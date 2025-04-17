<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Product\Action;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AbstractAction;
use M2E\Kaufland\Model\Product\Repository;
use M2E\Kaufland\Model\ResourceModel\Product\Grid\AllItems\ActionFilter;
use M2E\Kaufland\Model\Listing\LogService;
use M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory;

class RunRevise extends AbstractAction
{
    use ActionTrait;

    private Repository $productRepository;

    private ActionFilter $massActionFilter;

    private ActionService $actionService;

    public function __construct(
        ActionService $actionService,
        ActionFilter $massActionFilter,
        Repository $productRepository,
        LogService $logService,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($collectionFactory, $logService);

        $this->productRepository = $productRepository;
        $this->massActionFilter = $massActionFilter;
        $this->actionService = $actionService;
    }

    public function execute()
    {
        $products = $this->productRepository->massActionSelectedProducts($this->massActionFilter);

        if ($this->isRealtimeAction($products)) {
            ['result' => $result] = $this->actionService->runRevise($products);
            if ($result === 'success') {
                $this->getMessageManager()->addSuccessMessage(
                    __('"Revising Selected Items On %channel_title" task has completed.', [
                        'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                    ])
                );
            } else {
                $this->getMessageManager()->addErrorMessage(
                    __('"Revising Selected Items On %channel_title" task has completed with errors.', [
                        'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                    ])
                );
            }

            return $this->redirectToGrid();
        }

        $this->actionService->scheduleRevise($products);

        $this->getMessageManager()->addSuccessMessage(
            __('"Revising Selected Items On %channel_title" task has completed.', [
                'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
            ])
        );

        return $this->redirectToGrid();
    }
}
