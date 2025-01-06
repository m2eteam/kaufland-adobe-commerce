<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Search;

class CheckSearchResults extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    private \M2E\Kaufland\Model\Listing\Wizard\Repository $wizardProductRepository;
    private \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory;
    private \M2E\Kaufland\Model\Listing\Wizard\SearchChannelProductManager $searchChannelProductManager;
    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\SearchChannelProductManager $searchChannelProductManager,
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Model\Listing\Wizard\Repository $wizardProductRepository,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct();
        $this->wizardProductRepository = $wizardProductRepository;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->searchChannelProductManager = $searchChannelProductManager;
        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute()
    {
        $wizardId = (int)$this->getRequest()->getParam('id');
        $manager = $this->wizardManagerFactory->createById($wizardId);

        $countProductsForCreate = $this->wizardProductRepository->getCountProductsWithoutKauflandId($wizardId);

        $newProductPopup = $this->getLayout()
                                ->createBlock(
                                    \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Product\NewProductPopup::class,
                                );

        return $this->jsonResultFactory
            ->create()
            ->setData(
                [
                    'is_search_completed' => $this->searchChannelProductManager->isAllFound($manager),
                    'count_products_for_create' => $countProductsForCreate,
                    'popupHtml' => $newProductPopup->toHtml(),
                ]
            );
    }
}
