<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product;

class Cache extends \M2E\Kaufland\Model\Magento\Product
{
    private $isCacheEnabled = false;
    private \M2E\Kaufland\Helper\Data\Cache\Runtime $runtimeCache;
    private \M2E\Kaufland\Model\Factory $modelFactory;

    public function __construct(
        \M2E\Kaufland\Helper\Data\Cache\Runtime $runtimeCache,
        \M2E\Kaufland\Model\Magento\Product\Inventory\Factory $inventoryFactory,
        \Magento\Framework\Filesystem\DriverPool $driverPool,
        \Magento\Framework\App\ResourceConnection $resourceModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\Product\Type $productType,
        \M2E\Kaufland\Model\Magento\Product\Type\ConfigurableFactory $configurableFactory,
        \M2E\Kaufland\Model\Magento\Product\Status $productStatus,
        \Magento\CatalogInventory\Model\Configuration $catalogInventoryConfiguration,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \M2E\Core\Helper\Data $helperData,
        \M2E\Kaufland\Helper\Module\Configuration $moduleConfiguration,
        \M2E\Kaufland\Helper\Module\Database\Structure $dbStructureHelper,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Kaufland\Helper\Data\Cache\Permanent $cache,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Helper\Magento\Product $magentoProductHelper
    ) {
        parent::__construct(
            $inventoryFactory,
            $driverPool,
            $resourceModel,
            $productFactory,
            $websiteFactory,
            $productType,
            $configurableFactory,
            $productStatus,
            $catalogInventoryConfiguration,
            $storeFactory,
            $filesystem,
            $objectManager,
            $activeRecordFactory,
            $magentoProductCollectionFactory,
            $resourceProduct,
            $helperData,
            $moduleConfiguration,
            $dbStructureHelper,
            $globalDataHelper,
            $cache,
            $modelFactory,
            $magentoProductHelper,
        );

        $this->runtimeCache = $runtimeCache;
        $this->modelFactory = $modelFactory;
    }

    public function getCacheValue($key)
    {
        $key = sha1(
            'magento_product_'
            . $this->getProductId()
            . '_'
            . $this->getStoreId()
            . '_'
            . \M2E\Core\Helper\Json::encode($key),
        );

        return $this->runtimeCache->getValue($key);
    }

    public function setCacheValue($key, $value)
    {
        $key = sha1(
            'magento_product_'
            . $this->getProductId()
            . '_'
            . $this->getStoreId()
            . '_'
            . \M2E\Core\Helper\Json::encode($key),
        );

        $tags = [
            'magento_product',
            'magento_product_' . $this->getProductId() . '_' . $this->getStoreId(),
        ];

        $this->runtimeCache->setValue($key, $value, $tags);

        return $value;
    }

    public function clearCache()
    {
        $this->runtimeCache->removeTagValues(
            'magento_product_' . $this->getProductId() . '_' . $this->getStoreId(),
        );
    }

    /**
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->isCacheEnabled;
    }

    /**
     * @return $this
     */
    public function enableCache()
    {
        $this->isCacheEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableCache()
    {
        $this->isCacheEnabled = false;

        return $this;
    }

    public function exists()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeInstance()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function getStockItem()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getTypeId()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function isSimpleTypeWithCustomOptions()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getSku()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getName()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function isStatusEnabled()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function isStockAvailability()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getPrice()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getSpecialPrice()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getQty($lifeMode = false)
    {
        $args = func_get_args();
        $args[] = $this->isGroupedProductMode;

        return $this->getMethodData(__FUNCTION__, $args);
    }

    public function getAttributeValue($attributeCode, $convertBoolean = true): string
    {
        $args = func_get_args();

        return $this->getMethodData(__FUNCTION__, $args);
    }

    public function getThumbnailImage()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getImage($attribute = 'image')
    {
        $args = func_get_args();

        return $this->getMethodData(__FUNCTION__, $args);
    }

    public function getGalleryImages($limitImages = 0)
    {
        $args = func_get_args();

        return $this->getMethodData(__FUNCTION__, $args);
    }

    public function getVariationInstance()
    {
        if ($this->_variationInstance !== null) {
            return $this->_variationInstance;
        }

        $this->_variationInstance = $this->modelFactory
            ->getObject('Magento_Product_Variation_Cache')->setMagentoProduct($this);

        return $this->_variationInstance;
    }

    protected function getMethodData($methodName, $params = null)
    {
        $cacheKey = [
            __CLASS__,
            $methodName,
        ];

        if ($params !== null) {
            $cacheKey[] = $params;
        }

        $cacheResult = $this->getCacheValue($cacheKey);

        if ($this->isCacheEnabled() && $cacheResult !== null) {
            return $cacheResult;
        }

        if ($params !== null) {
            $data = parent::$methodName(...$params);
        } else {
            $data = parent::$methodName();
        }

        if (!$this->isCacheEnabled()) {
            return $data;
        }

        return $this->setCacheValue($cacheKey, $data);
    }
}
