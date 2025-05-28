<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

use M2E\Kaufland\Model\Template\Manager;

class Delete extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    private \M2E\Kaufland\Model\Template\Shipping\DeleteService $shippingDeleteService;
    private \M2E\Kaufland\Model\Template\Synchronization\DeleteService $synchronizationDeleteService;
    private \M2E\Kaufland\Model\Template\SellingFormat\DeleteService $sellingFormatDeleteService;
    private \M2E\Kaufland\Model\Template\Description\DeleteService $descriptionDeleteService;

    public function __construct(
        \M2E\Kaufland\Model\Template\Shipping\DeleteService $shippingDeleteService,
        \M2E\Kaufland\Model\Template\Synchronization\DeleteService $synchronizationDeleteService,
        \M2E\Kaufland\Model\Template\SellingFormat\DeleteService $sellingFormatDeleteService,
        \M2E\Kaufland\Model\Template\Description\DeleteService $descriptionDeleteService,
        Manager $templateManager
    ) {
        parent::__construct($templateManager);
        $this->shippingDeleteService = $shippingDeleteService;
        $this->synchronizationDeleteService = $synchronizationDeleteService;
        $this->sellingFormatDeleteService = $sellingFormatDeleteService;
        $this->descriptionDeleteService = $descriptionDeleteService;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');

        $this->isValidNick($nick);

        try {
            if ($nick === Manager::TEMPLATE_SYNCHRONIZATION) {
                $this->synchronizationDeleteService->process($id);
            } elseif ($nick === Manager::TEMPLATE_SELLING_FORMAT) {
                $this->sellingFormatDeleteService->process($id);
            } elseif ($nick === Manager::TEMPLATE_SHIPPING) {
                $this->shippingDeleteService->process($id);
            } elseif ($nick === Manager::TEMPLATE_DESCRIPTION) {
                $this->descriptionDeleteService->process($id);
            }

            $this->messageManager->addSuccess((string)__('Policy was deleted.'));
        } catch (\M2E\Kaufland\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));
        }

        return $this->_redirect('*/*/index');
    }

    private function isValidNick($nick): void
    {
        $allowed = [
            Manager::TEMPLATE_SYNCHRONIZATION,
            Manager::TEMPLATE_SELLING_FORMAT,
            Manager::TEMPLATE_SHIPPING,
            Manager::TEMPLATE_DESCRIPTION,
        ];

        if (!in_array($nick, $allowed)) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Unknown Policy nick ' . $nick);
        }
    }
}
