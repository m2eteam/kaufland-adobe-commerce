<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Tools\Kaufland;

use M2E\Core\Model\ControlPanel\Inspection\FixerInterface;
use M2E\Core\Model\ControlPanel\Inspection\InspectorInterface;

class General extends \M2E\Kaufland\Controller\Adminhtml\ControlPanel\AbstractCommand
{
    private \M2E\Core\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory;
    private \M2E\Core\Model\ControlPanel\InspectionTaskCollection $taskCollection;
    private \M2E\Core\Model\ControlPanel\CurrentExtensionResolver $currentExtensionResolver;

    public function __construct(
        \M2E\Kaufland\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\Kaufland\Controller\Adminhtml\Context $context,
        \M2E\Core\Model\ControlPanel\InspectionTaskCollection $taskCollection,
        \M2E\Core\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory,
        \M2E\Core\Model\ControlPanel\CurrentExtensionResolver $currentExtensionResolver
    ) {
        parent::__construct($controlPanelHelper, $context);
        $this->handlerFactory = $handlerFactory;
        $this->taskCollection = $taskCollection;
        $this->currentExtensionResolver = $currentExtensionResolver;
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

            return $this->_redirect($this->redirect->getRefererUrl());
        }

        $currentExtension = $this->currentExtensionResolver->get();
        $definition = $this->taskCollection->findTaskForExtension(
            $currentExtension->getModuleName(),
            'RemovedStores'
        );
        if ($definition === null) {
            $this->messageManager->addError('Inspection task for removed stores not found');

            return $this->_redirect($this->redirect->getRefererUrl());
        }

        /** @var FixerInterface&InspectorInterface $inspector */
        $inspector = $this->handlerFactory->create($definition);

        $inspector->fix([$replaceIdFrom => $replaceIdTo]);

        $this->_redirect($this->redirect->getRefererUrl());
    }
}
