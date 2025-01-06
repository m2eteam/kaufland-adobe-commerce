<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

class Save extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    private bool $isShippingSaveAllowed  = true;
    private \M2E\Kaufland\Helper\Module\Wizard $wizardHelper;
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Kaufland\Model\Template\Synchronization\SaveService $synchronizationSaveService;
    private \M2E\Kaufland\Model\Template\SellingFormat\SaveService $sellingFormatSaveService;
    private \M2E\Kaufland\Model\Template\Shipping\SaveService $shippingSaveService;
    private \M2E\Kaufland\Model\Template\Description\SaveService $descriptionSaveService;
    private \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository;

    public function __construct(
        \M2E\Kaufland\Model\Template\SellingFormat\SaveService $sellingFormatSaveService,
        \M2E\Kaufland\Model\Template\Synchronization\SaveService $synchronizationSaveService,
        \M2E\Kaufland\Model\Template\Shipping\SaveService $shippingSaveService,
        \M2E\Kaufland\Model\Template\Description\SaveService $descriptionSaveService,
        \M2E\Kaufland\Helper\Module\Wizard $wizardHelper,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository,
        \M2E\Kaufland\Model\Kaufland\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->wizardHelper = $wizardHelper;
        $this->urlHelper = $urlHelper;
        $this->synchronizationSaveService = $synchronizationSaveService;
        $this->sellingFormatSaveService = $sellingFormatSaveService;
        $this->shippingSaveService = $shippingSaveService;
        $this->descriptionSaveService = $descriptionSaveService;
        $this->shippingGroupRepository = $shippingGroupRepository;
    }

    public function execute()
    {
        $templates = [];
        $templateNicks = $this->templateManager->getAllTemplates();

        // ---------------------------------------
        foreach ($templateNicks as $nick) {
            if ($this->isSaveAllowed($nick)) {
                $template = $this->saveTemplate($nick);

                if ($template) {
                    $templates[] = [
                        'nick' => $nick,
                        'id' => (int)$template->getId(),
                        'title' => \M2E\Core\Helper\Data::escapeJs(
                            \M2E\Core\Helper\Data::escapeHtml($template->getTitle())
                        ),
                    ];
                }
            }
        }
        // ---------------------------------------

        // ---------------------------------------
        if ($this->isAjax()) {
            $this->setJsonContent($templates);

            return $this->getResult();
        }
        // ---------------------------------------

        if (count($templates) == 0) {
            if (!$this->isShippingSaveAllowed) {
                return $this->_redirect($this->redirect->getRefererUrl());
            }

            $this->messageManager->addError(__('Policy was not saved.'));

            return $this->_redirect('*/*/index');
        }

        $template = array_shift($templates);

        $this->messageManager->addSuccess(__('Policy was saved.'));

        $extendedRoutersParams = [
            'edit' => [
                'id' => $template['id'],
                'nick' => $template['nick'],
                'close_on_save' => $this->getRequest()->getParam('close_on_save'),
            ],
        ];

        if ($this->wizardHelper->isActive(\M2E\Kaufland\Helper\View\Kaufland::WIZARD_INSTALLATION_NICK)) {
            $extendedRoutersParams['edit']['wizard'] = true;
        }

        return $this->_redirect(
            $this->urlHelper->getBackUrl(
                'list',
                [],
                $extendedRoutersParams
            )
        );
    }

    protected function isSaveAllowed($templateNick)
    {
        if (!$this->getRequest()->isPost()) {
            return false;
        }

        if ($templateNick === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SHIPPING) {
            return $this->isShippingSaveAllowed();
        }

        $requestedTemplateNick = $this->getRequest()->getPost('nick');

        if ($requestedTemplateNick === null) {
            return true;
        }

        if ($requestedTemplateNick == $templateNick) {
            return true;
        }

        return false;
    }

    protected function saveTemplate($nick)
    {
        $data = $this->getRequest()->getPost($nick);

        if ($data === null) {
            return null;
        }

        if ($nick === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SYNCHRONIZATION) {
            return $this->synchronizationSaveService->save($data);
        }

        if ($nick === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            return $this->sellingFormatSaveService->save($data);
        }

        if ($nick === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SHIPPING) {
            return $this->shippingSaveService->save($data);
        }

        if ($nick === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_DESCRIPTION) {
            return $this->descriptionSaveService->save($data);
        }

        throw new \M2E\Kaufland\Model\Exception\Logic('Unknown nick ' . $nick);
    }

    private function isShippingSaveAllowed(): bool
    {
        $data = $this->getRequest()->getPost(\M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SHIPPING);

        if ($data === null) {
            return false;
        }

        $storefrontId = (int)$data['storefront_id'];
        $shippingGroupId = (int)$data['shipping_group_id'];

        if ($this->shippingGroupRepository->isShippingGroupExistsByStorefront($shippingGroupId, $storefrontId)) {
            return true;
        }

        $this->isShippingSaveAllowed = false;
        $this->messageManager->addError((string)__('The selected Shipping Group is not available for the Storefront.'));

        return false;
    }
}
