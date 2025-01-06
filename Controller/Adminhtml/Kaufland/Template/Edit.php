<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

class Edit extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    private \M2E\Kaufland\Helper\Component\Kaufland\Template\Switcher\DataLoader $dataLoader;
    private \M2E\Kaufland\Model\Template\Synchronization\Repository $synchronizationRepository;
    private \M2E\Kaufland\Model\Template\SynchronizationFactory $synchronizationFactory;
    private \M2E\Kaufland\Model\Template\SellingFormatFactory $sellingFormatFactory;
    private \M2E\Kaufland\Model\Template\SellingFormat\Repository $sellingFormatRepository;
    private \M2E\Kaufland\Model\Template\Shipping\Repository $shippingRepository;
    private \M2E\Kaufland\Model\Template\ShippingFactory $shippingFactory;
    private \M2E\Kaufland\Model\Template\Description\Repository $descriptionRepository;
    private \M2E\Kaufland\Model\Template\DescriptionFactory $descriptionFactory;

    public function __construct(
        \M2E\Kaufland\Model\Template\SellingFormatFactory $sellingFormatFactory,
        \M2E\Kaufland\Model\Template\SellingFormat\Repository $sellingFormatRepository,
        \M2E\Kaufland\Model\Template\Synchronization\Repository $synchronizationRepository,
        \M2E\Kaufland\Model\Template\SynchronizationFactory $synchronizationFactory,
        \M2E\Kaufland\Model\Template\Shipping\Repository $shippingRepository,
        \M2E\Kaufland\Model\Template\ShippingFactory $shippingFactory,
        \M2E\Kaufland\Model\Template\Description\Repository $descriptionRepository,
        \M2E\Kaufland\Model\Template\DescriptionFactory $descriptionFactory,
        \M2E\Kaufland\Helper\Component\Kaufland\Template\Switcher\DataLoader $dataLoader,
        \M2E\Kaufland\Model\Kaufland\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->dataLoader = $dataLoader;
        $this->synchronizationRepository = $synchronizationRepository;
        $this->synchronizationFactory = $synchronizationFactory;
        $this->sellingFormatFactory = $sellingFormatFactory;
        $this->sellingFormatRepository = $sellingFormatRepository;
        $this->shippingRepository = $shippingRepository;
        $this->shippingFactory = $shippingFactory;
        $this->descriptionRepository = $descriptionRepository;
        $this->descriptionFactory = $descriptionFactory;
    }

    public function execute()
    {
        // ---------------------------------------
        $id = $this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        // ---------------------------------------

        if ($nick === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SYNCHRONIZATION) {
            return $this->executeSynchronizationTemplate($id);
        }

        if ($nick === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            return $this->executeSellingFormatTemplate($id);
        }

        if ($nick === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SHIPPING) {
            return $this->executeShippingTemplate($id);
        }

        if ($nick === \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_DESCRIPTION) {
            return $this->executeDescriptionTemplate($id);
        }

        throw new \M2E\Kaufland\Model\Exception\Logic('Unknown nick ' . $nick);
    }

    private function executeSynchronizationTemplate($id)
    {
        $template = $this->synchronizationRepository->find((int)$id);
        if ($template === null) {
            $template = $this->synchronizationFactory->create();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/synchronization-policy-for-kaufland');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Synchronization Policy',
                    [
                        'template_title' => \M2E\Core\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Synchronization Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SYNCHRONIZATION,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }

    private function executeSellingFormatTemplate($id)
    {
        $template = $this->sellingFormatRepository->find((int)$id);
        if ($template === null) {
            $template = $this->sellingFormatFactory->create();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/synchronization-policy-for-kaufland');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Selling Policy',
                    [
                        'template_title' => \M2E\Core\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Selling Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SELLING_FORMAT,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }

    private function executeShippingTemplate($id)
    {
        $template = $this->shippingRepository->find((int)$id);
        if ($template === null) {
            $template = $this->shippingFactory->create();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        $this->setPageHelpLink('https://docs-m2.m2epro.com/kaufland-magento-integration');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Shipping Policy',
                    [
                        'template_title' => \M2E\Core\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Shipping Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SHIPPING,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }

    private function executeDescriptionTemplate($id)
    {
        $template = $this->descriptionRepository->find((int)$id);
        if ($template === null) {
            $template = $this->descriptionFactory->create();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/m2e-kaufland-policies');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Description Policy',
                    [
                        'template_title' => \M2E\Core\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Description Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_DESCRIPTION,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }
}
