<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Search;

class SearchChannelId extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractListing implements
    \Magento\Framework\App\Action\HttpGetActionInterface
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Listing\Wizard\SearchChannelProductManager $searchKauflandProductId;
    private \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory;
    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\SearchChannelProductManager $searchKauflandProductId,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory
    ) {
        parent::__construct();
        $this->searchKauflandProductId = $searchKauflandProductId;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute(): \Magento\Framework\Controller\Result\Json
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);

        $findResult = $this->searchKauflandProductId->find($manager);
        if ($findResult === null) {
            return $this->createJsonResponse(true, 0, 0);
        }

        return $this->createJsonResponse(
            $findResult->isCompleted(),
            $findResult->getTotalProductCount(),
            $findResult->getProcessedProductCount(),
        );
    }

    private function createJsonResponse(
        bool $isCompleted,
        int $totalItems,
        int $processedItems
    ): \Magento\Framework\Controller\Result\Json {
        return $this->jsonResultFactory
            ->create()
            ->setData(
                [
                    'is_complete' => $isCompleted,
                    'total_items' => $totalItems,
                    'processed_items' => $processedItems,
                ],
            );
    }
}
