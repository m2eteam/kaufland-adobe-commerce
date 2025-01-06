<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\Description;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\AbstractDescription;
use M2E\Kaufland\Model\ResourceModel\Listing as ListingResource;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;
use M2E\Kaufland\Model\Template\Description as TemplateDescription;

class Preview extends AbstractDescription
{
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Description\RendererFactory $rendererFactory;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Description\TemplateParser $templateParser;
    private array $description = [];
    private \M2E\Kaufland\Model\ResourceModel\Listing $listingResource;
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Kaufland\Model\Magento\ProductFactory $ourMagentoProductFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing $listingResource,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Description\RendererFactory $rendererFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Description\TemplateParser $templateParser,
        \Magento\Framework\HTTP\PhpEnvironment\Request $phpEnvironmentRequest,
        \Magento\Catalog\Model\Product $productModel,
        \M2E\Kaufland\Model\Kaufland\Template\Manager $templateManager,
        \M2E\Kaufland\Model\Magento\ProductFactory $ourMagentoProductFactory
    ) {
        parent::__construct($phpEnvironmentRequest, $productModel, $templateManager);

        $this->rendererFactory = $rendererFactory;
        $this->templateParser = $templateParser;
        $this->listingResource = $listingResource;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->ourMagentoProductFactory = $ourMagentoProductFactory;
    }

    protected function getLayoutType()
    {
        return self::LAYOUT_BLANK;
    }

    public function execute()
    {
        $this->description = $this->getRequest()->getPost('description_preview', []);

        if (empty($this->description)) {
            $this->messageManager->addError((string)__('Description Policy data is not specified.'));

            return $this->getResult();
        }

        $productsEntities = $this->getProductsEntities();

        if ($productsEntities['magento_product'] === null) {
            $this->messageManager->addError((string)__('Magento Product does not exist.'));

            return $this->getResult();
        }

        $description = $this->getDescription(
            $productsEntities['magento_product'],
            $productsEntities['listing_product'],
        );

        if (!$description) {
            $this->messageManager->addWarning(
                (string)__(
                    'The Product Description attribute is selected as a source of the Kaufland Item Description,
                    but this Product has empty description.',
                ),
            );
        } elseif ($productsEntities['listing_product'] === null) {
            $this->messageManager->addWarning(
                (string)__(
                    'The Product you selected is not presented in any M2E Kaufland Listing.
                    Thus, the values of the M2E Kaufland Attribute(s), which are used in the Item Description,
                    will be ignored and displayed like #attribute label#.
                    Please, change the Product ID to preview the data.',
                ),
            );
        }

        $previewBlock = $this->getLayout()
                             ->createBlock(
                                 \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Description\Preview::class,
                             )
                             ->setData([
                                 'title' => $productsEntities['magento_product']->getProduct()->getData('name'),
                                 'magento_product_id' => $productsEntities['magento_product']->getProductId(),
                                 'description' => $description,
                             ]);

        $this->getResultPage()->getConfig()->getTitle()->prepend((string)__('Preview Description'));
        $this->addContent($previewBlock);

        return $this->getResult();
    }

    private function getDescription(
        \M2E\Kaufland\Model\Magento\Product $magentoProduct,
        \M2E\Kaufland\Model\Product $listingProduct = null
    ): string {
        $descriptionModeProduct = TemplateDescription::DESCRIPTION_MODE_PRODUCT;
        $descriptionModeShort = TemplateDescription::DESCRIPTION_MODE_SHORT;
        $descriptionModeCustom = TemplateDescription::DESCRIPTION_MODE_CUSTOM;

        if ($this->description['description_mode'] == $descriptionModeProduct) {
            $description = $magentoProduct->getProduct()->getDescription();
        } elseif ($this->description['description_mode'] == $descriptionModeShort) {
            $description = $magentoProduct->getProduct()->getShortDescription();
        } elseif ($this->description['description_mode'] == $descriptionModeCustom) {
            $description = $this->description['description_template'];
        } else {
            $description = '';
        }

        if (empty($description)) {
            return '';
        }

        $description = $this->templateParser->parseTemplate($description, $magentoProduct);

        if ($listingProduct !== null) {
            $renderer = $this->rendererFactory->create($listingProduct);
            $description = $renderer->parseTemplate($description);
        }

        return $description;
    }

    private function getProductsEntities(): array
    {
        $productId = $this->description['magento_product_id'] ?? -1;
        $storeId = $this->description['store_id'] ?? \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        $magentoProduct = $this->getMagentoProductById($productId, $storeId);
        $listingProduct = $this->getListingProductByMagentoProductId($productId, $storeId);

        return [
            'magento_product' => $magentoProduct,
            'listing_product' => $listingProduct,
        ];
    }

    private function getMagentoProductById($productId, $storeId): ?\M2E\Kaufland\Model\Magento\Product
    {
        if (!$this->isMagentoProductExists($productId)) {
            return null;
        }

        $magentoProduct = $this->ourMagentoProductFactory->create();

        $magentoProduct->loadProduct($productId, $storeId);

        return $magentoProduct;
    }

    private function getListingProductByMagentoProductId($productId, $storeId): ?\M2E\Kaufland\Model\Product
    {
        $listingProductCollection = $this->listingProductCollectionFactory
            ->create()
            ->addFieldToFilter(ProductResource::COLUMN_MAGENTO_PRODUCT_ID, $productId);

        $listingProductCollection->getSelect()->joinLeft(
            ['ml' => $this->listingResource->getMainTable()],
            sprintf('`ml`.`%s` = `main_table`.`%s`', ListingResource::COLUMN_ID, ProductResource::COLUMN_LISTING_ID),
            [ListingResource::COLUMN_STORE_ID]
        );

        $listingProductCollection->addFieldToFilter(ListingResource::COLUMN_STORE_ID, $storeId);
        /** @var \M2E\Kaufland\Model\Product $listingProduct */
        $listingProduct = $listingProductCollection->getFirstItem();

        if ($listingProduct->getId() === null) {
            return null;
        }

        return $listingProduct;
    }
}
