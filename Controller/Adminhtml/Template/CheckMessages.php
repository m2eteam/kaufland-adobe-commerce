<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Template;

class CheckMessages extends \M2E\Kaufland\Controller\Adminhtml\AbstractBase
{
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \M2E\Kaufland\Model\Template\SellingFormat\Repository $sellingRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        \M2E\Kaufland\Model\Template\SellingFormat\Repository $sellingRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->sellingRepository = $sellingRepository;
        $this->storeManager = $storeManager;
        $this->storefrontRepository = $storefrontRepository;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        $data = $this->getRequest()->getParam($nick);

        $template = null;
        $templateData = $data ?? [];

        if ($nick == \M2E\Kaufland\Model\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            $template = $this->sellingRepository->get($id);
        }

        if ($template !== null && $template->getId()) {
            $templateData = $template->getData();
        }

        if ($template === null || empty($templateData)) {
            $this->setJsonContent(['messages' => '']);

            return $this->getResult();
        }

        $storefront = $this->storefrontRepository->get((int)$this->getRequest()->getParam('storefront_id'));
        $store = $this->storeManager->getStore((int)$this->getRequest()->getParam('store_id'));

        /** @var \M2E\Kaufland\Block\Adminhtml\Template\SellingFormat\Messages $messagesBlock */
        $messagesBlock = $this->getLayout()
                              ->createBlock(
                                  \M2E\Kaufland\Block\Adminhtml\Template\SellingFormat\Messages::class,
                                  '',
                                  [
                                      'storefront'  => $storefront,
                                      'store' => $store
                                  ]
                              );

        $this->setJsonContent(['messages' => $messagesBlock->getMessagesHtml()]);

        return $this->getResult();
    }
}
