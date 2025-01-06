<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Product\Action;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AbstractAction;
use M2E\Kaufland\Model\Listing\LogService;
use M2E\Kaufland\Model\Product\Repository;
use M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory;
use M2E\Kaufland\Model\ResourceModel\Product\Grid\AllItems\ActionFilter;

class RunRelist extends AbstractAction
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
            ['result' => $result] = $this->actionService->runRelist($products);
            if ($result === 'success') {
                $this->getMessageManager()->addSuccessMessage(
                    __('"Relisting Selected Items On Kaufland" task has completed.'),
                );
            } else {
                $this->getMessageManager()->addErrorMessage(
                    __('"Relisting Selected Items On Kaufland" task has completed with errors.'),
                );
            }

            return $this->redirectToGrid();
        }

        $this->actionService->scheduleRelist($products);

        $this->getMessageManager()->addSuccessMessage(
            __('"Relisting Selected Items On Kaufland" task has completed.'),
        );

        return $this->redirectToGrid();
    }
}
