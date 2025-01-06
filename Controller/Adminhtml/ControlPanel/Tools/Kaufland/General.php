<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Tools\Kaufland;

use M2E\Kaufland\Controller\Adminhtml\Context;
use M2E\Kaufland\Controller\Adminhtml\ControlPanel\AbstractCommand;

class General extends AbstractCommand
{
    private \M2E\Kaufland\Model\ControlPanel\Inspection\Repository $repository;
    private \M2E\Kaufland\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory;

    public function __construct(
        \M2E\Kaufland\Helper\View\ControlPanel $controlPanelHelper,
        Context $context,
        \M2E\Kaufland\Model\ControlPanel\Inspection\Repository $repository,
        \M2E\Kaufland\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory
    ) {
        parent::__construct($controlPanelHelper, $context);
        $this->repository = $repository;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @title "Repair Removed Store"
     * @hidden
     */
    public function repairRemovedMagentoStoreAction()
    {
        $replaceIdFrom = $this->getRequest()->getParam('replace_from');
        $replaceIdTo = $this->getRequest()->getParam('replace_to');

        if (!$replaceIdFrom || !$replaceIdTo) {
            $this->messageManager->addError('Required params are not presented.');
            $this->_redirect($this->redirect->getRefererUrl());
        }

        $definition = $this->repository->getDefinition('RemovedStores');

        /** @var \M2E\Kaufland\Model\ControlPanel\Inspection\Inspector\RemovedStores $inspector */
        $inspector = $this->handlerFactory->create($definition);

        $inspector->fix([$replaceIdFrom => $replaceIdTo]);

        $this->_redirect($this->redirect->getRefererUrl());
    }
}
