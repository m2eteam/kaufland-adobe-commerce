<?php

declare(strict_types=1);

namespace M2E\Kaufland\Observer\Product\AddUpdate;

use Magento\Catalog\Model\Product\Attribute\Source\Status;

class After extends AbstractAddUpdate
{
    private \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\Factory $listingAutoActionsModeFactory;
    private \Magento\Eav\Model\Config $eavConfig;
    private \Magento\Store\Model\StoreManager $storeManager;
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;
    private \M2E\Kaufland\Model\Listing\Log\Repository $listingLogRepository;
    private array $listingsProductsChangedAttributes = [];
    private array $attributeAffectOnStoreIdCache = [];

    public function __construct(
        \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\Factory $listingAutoActionsModeFactory,
        \M2E\Kaufland\Model\Product\Repository $listingProductRepository,
        \M2E\Kaufland\Model\Listing\Log\Repository $listingLogRepository,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory
    ) {
        parent::__construct(
            $listingProductRepository,
            $productFactory,
            $activeRecordFactory,
            $modelFactory
        );

        $this->listingAutoActionsModeFactory = $listingAutoActionsModeFactory;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->changeAttributeTrackerFactory = $changeAttributeTrackerFactory;
        $this->listingLogService = $listingLogService;
        $this->listingLogRepository = $listingLogRepository;
    }

    public function beforeProcess(): void
    {
        parent::beforeProcess();

        if (!$this->isProxyExist()) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                'Before proxy should be defined earlier than after Action is performed.'
            );
        }

        if ($this->getProductId() <= 0) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Product ID should be defined for "after save" event.');
        }

        $this->reloadProduct();
    }

    protected function process(): void
    {
        if (!$this->isAddingProductProcess()) {
            $this->updateProductsNamesInLogs();

            if ($this->areThereAffectedItems()) {
                $this->performStatusChanges();
                $this->performPriceChanges();
                $this->performSpecialPriceChanges();
                $this->performSpecialPriceFromDateChanges();
                $this->performSpecialPriceToDateChanges();

                $this->addListingProductInstructions();
            }
        } else {
            $this->performGlobalAutoActions();
        }

        $this->performWebsiteAutoActions();
        $this->performCategoryAutoActions();
    }

    private function updateProductsNamesInLogs()
    {
        if (!$this->isAdminDefaultStoreId()) {
            return;
        }

        $name = $this->getProduct()->getName();

        if ($this->getProxy()->getData('name') === $name) {
            return;
        }

        $this->listingLogRepository->updateProductTitle($this->getProductId(), $name);
    }

    private function performStatusChanges()
    {
        $oldValue = (int)$this->getProxy()->getData('status');
        $newValue = (int)$this->getProduct()->getStatus();

        if ($oldValue == $newValue) {
            return;
        }

        $oldValue = ($oldValue == Status::STATUS_ENABLED) ? 'Enabled' : 'Disabled';
        $newValue = ($newValue == Status::STATUS_ENABLED) ? 'Enabled' : 'Disabled';

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            /** @var \M2E\Kaufland\Model\Product $listingProduct */

            $listingProductStoreId = $listingProduct->getListing()->getStoreId();

            if (!$this->isAttributeAffectOnStoreId('status', $listingProductStoreId)) {
                continue;
            }

            $this->listingsProductsChangedAttributes[$listingProduct->getId()][] = 'status';

            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_STATUS,
                $oldValue,
                $newValue
            );
        }
    }

    private function performPriceChanges()
    {
        $oldValue = round((float)$this->getProxy()->getData('price'), 2);
        $newValue = round((float)$this->getProduct()->getPrice(), 2);

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $this->listingsProductsChangedAttributes[$listingProduct->getId()][] = 'price';

            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_PRICE,
                strval($oldValue),
                strval($newValue)
            );
        }
    }

    private function performSpecialPriceChanges()
    {
        $oldValue = round((float)$this->getProxy()->getData('special_price'), 2);
        $newValue = round((float)$this->getProduct()->getSpecialPrice(), 2);

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $this->listingsProductsChangedAttributes[$listingProduct->getId()][] = 'special_price';

            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE,
                (string)$oldValue,
                (string)$newValue
            );
        }
    }

    private function performSpecialPriceFromDateChanges()
    {
        $oldValue = $this->getProxy()->getData('special_price_from_date');
        $newValue = $this->getProduct()->getSpecialFromDate();

        if ($oldValue == $newValue) {
            return;
        }

        ($oldValue === null || $oldValue === false || $oldValue == '') && $oldValue = 'None';
        ($newValue === null || $newValue === false || $newValue == '') && $newValue = 'None';

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $this->listingsProductsChangedAttributes[$listingProduct->getId()][] = 'special_price_from_date';

            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE,
                $oldValue,
                $newValue
            );
        }
    }

    private function performSpecialPriceToDateChanges()
    {
        $oldValue = $this->getProxy()->getData('special_price_to_date');
        $newValue = $this->getProduct()->getSpecialToDate();

        if ($oldValue == $newValue) {
            return;
        }

        ($oldValue === null || $oldValue === false || $oldValue == '') && $oldValue = 'None';
        ($newValue === null || $newValue === false || $newValue == '') && $newValue = 'None';

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $this->listingsProductsChangedAttributes[$listingProduct->getId()][] = 'special_price_to_date';

            $this->logListingProductMessage(
                $listingProduct,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE,
                $oldValue,
                $newValue
            );
        }
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function addListingProductInstructions()
    {
        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $listingProduct,
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }

    protected function isAddingProductProcess()
    {
        return $this->getProxy()->getProductId() <= 0 && $this->getProductId() > 0;
    }

    private function isProxyExist()
    {
        $key = $this->getProductId() . '_' . $this->getStoreId();
        if (isset(\M2E\Kaufland\Observer\Product\AddUpdate\Before::$proxyStorage[$key])) {
            return true;
        }

        $key = $this
            ->getEvent()
            ->getProduct()
            ->getData(\M2E\Kaufland\Observer\Product\AddUpdate\Before::BEFORE_EVENT_KEY);

        return isset(\M2E\Kaufland\Observer\Product\AddUpdate\Before::$proxyStorage[$key]);
    }

    /**
     * @return \M2E\Kaufland\Observer\Product\AddUpdate\Before\Proxy
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function getProxy()
    {
        if (!$this->isProxyExist()) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                'Before proxy should be defined earlier than after Action is performed.'
            );
        }

        $key = $this->getProductId() . '_' . $this->getStoreId();
        if (isset(\M2E\Kaufland\Observer\Product\AddUpdate\Before::$proxyStorage[$key])) {
            return \M2E\Kaufland\Observer\Product\AddUpdate\Before::$proxyStorage[$key];
        }

        $key = $this
            ->getEvent()
            ->getProduct()
            ->getData(\M2E\Kaufland\Observer\Product\AddUpdate\Before::BEFORE_EVENT_KEY);

        return \M2E\Kaufland\Observer\Product\AddUpdate\Before::$proxyStorage[$key];
    }

    private function isAttributeAffectOnStoreId($attributeCode, $onStoreId)
    {
        $cacheKey = $attributeCode . '_' . $onStoreId;

        if (isset($this->attributeAffectOnStoreIdCache[$cacheKey])) {
            return $this->attributeAffectOnStoreIdCache[$cacheKey];
        }

        $attributeInstance = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

        if (!($attributeInstance instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute)) {
            return $this->attributeAffectOnStoreIdCache[$cacheKey] = false;
        }

        $attributeScope = (int)$attributeInstance->getData('is_global');

        if (
            $attributeScope == \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL ||
            $this->getStoreId() == $onStoreId
        ) {
            return $this->attributeAffectOnStoreIdCache[$cacheKey] = true;
        }

        if ($this->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productFactory->create();
            $product->setStoreId($onStoreId);
            $product->load($this->getProductId());

            $scopeOverridden = $this->objectManager
                ->create(\Magento\Catalog\Model\Attribute\ScopeOverriddenValue::class);
            $isExistsValueForStore = $scopeOverridden->containsValue(
                \Magento\Catalog\Api\Data\ProductInterface::class,
                $product,
                $attributeCode,
                $onStoreId
            );

            return $this->attributeAffectOnStoreIdCache[$cacheKey] = !$isExistsValueForStore;
        }

        if ($attributeScope == \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE) {
            return $this->attributeAffectOnStoreIdCache[$cacheKey] = false;
        }

        $affectedStoreIds = $this->storeManager->getStore($this->getStoreId())->getWebsite()->getStoreIds();
        $affectedStoreIds = array_map('intval', array_values(array_unique($affectedStoreIds)));

        return $this->attributeAffectOnStoreIdCache[$cacheKey] = in_array($onStoreId, $affectedStoreIds);
    }

    private function logListingProductMessage(
        \M2E\Kaufland\Model\Product $listingProduct,
        int $action,
        string $oldValue,
        string $newValue,
        $messagePostfix = ''
    ): void {
        $oldValue = strlen($oldValue) > 150 ? substr($oldValue, 0, 150) . ' ...' : $oldValue;
        $newValue = strlen($newValue) > 150 ? substr($newValue, 0, 150) . ' ...' : $newValue;

        $messagePostfix = trim(trim($messagePostfix), '.');
        if (!empty($messagePostfix)) {
            $messagePostfix = ' ' . $messagePostfix;
        }

        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            $action,
            null,
            \M2E\Kaufland\Helper\Module\Log::encodeDescription(
                'From [%from%] to [%to%]' . $messagePostfix . '.',
                ['!from' => $oldValue, '!to' => $newValue]
            ),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO,
        );
    }

    protected function performGlobalAutoActions()
    {
        $object = $this->listingAutoActionsModeFactory->createGlobalMode($this->getProduct());
        $object->synch();
    }

    protected function performWebsiteAutoActions()
    {
        $object = $this->listingAutoActionsModeFactory->createWebsiteMode($this->getProduct());

        $websiteIdsOld = $this->getProxy()->getWebsiteIds();
        $websiteIdsNew = $this->getProduct()->getWebsiteIds();

        // website for admin values
        if ($this->isAddingProductProcess()) {
            $websiteIdsNew[] = 0;
        }

        $addedWebsiteIds = array_diff($websiteIdsNew, $websiteIdsOld);
        foreach ($addedWebsiteIds as $websiteId) {
            $object->synchWithAddedWebsiteId($websiteId);
        }

        $deletedWebsiteIds = array_diff($websiteIdsOld, $websiteIdsNew);
        foreach ($deletedWebsiteIds as $websiteId) {
            $object->synchWithDeletedWebsiteId($websiteId);
        }
    }

    protected function performCategoryAutoActions()
    {
        $categoryIdsOld = $this->getProxy()->getCategoriesIds();
        $categoryIdsNew = $this->getProduct()->getCategoryIds();
        $addedCategories = array_diff($categoryIdsNew, $categoryIdsOld);
        $deletedCategories = array_diff($categoryIdsOld, $categoryIdsNew);

        $websiteIdsOld = $this->getProxy()->getWebsiteIds();
        $websiteIdsNew = $this->getProduct()->getWebsiteIds();
        $addedWebsites = array_diff($websiteIdsNew, $websiteIdsOld);
        $deletedWebsites = array_diff($websiteIdsOld, $websiteIdsNew);

        $websitesChanges = [
            // website for default store view
            0 => [
                'added' => $addedCategories,
                'deleted' => $deletedCategories,
            ],
        ];

        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteId = (int)$website->getId();

            $websiteChanges = [
                'added' => [],
                'deleted' => [],
            ];

            // website has been enabled
            if (in_array($websiteId, $addedWebsites)) {
                $websiteChanges['added'] = $categoryIdsNew;
                // website is enabled
            } elseif (in_array($websiteId, $websiteIdsNew)) {
                $websiteChanges['added'] = $addedCategories;
            }

            // website has been disabled
            if (in_array($websiteId, $deletedWebsites)) {
                $websiteChanges['deleted'] = $categoryIdsOld;
                // website is enabled
            } elseif (in_array($websiteId, $websiteIdsNew)) {
                $websiteChanges['deleted'] = $deletedCategories;
            }

            $websitesChanges[$websiteId] = $websiteChanges;
        }

        $categoryAutoAction = $this->listingAutoActionsModeFactory->createCategoryMode($this->getProduct());
        foreach ($websitesChanges as $websiteId => $changes) {
            $categoryAutoAction->synchWithAddedCategoryId($websiteId, $changes['added']);
            $categoryAutoAction->synchWithDeletedCategoryId($websiteId, $changes['deleted']);
        }
    }
}
