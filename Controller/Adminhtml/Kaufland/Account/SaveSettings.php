<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Account;

class SaveSettings extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractAccount
{
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Kaufland\Model\Account\Update $accountUpdate;
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Kaufland\Model\Account\Update $accountUpdate,
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Core\Helper\Url $urlHelper
    ) {
        parent::__construct();

        $this->urlHelper = $urlHelper;
        $this->accountUpdate = $accountUpdate;
        $this->accountRepository = $accountRepository;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost();

        if (!$post->count()) {
            $this->_forward('index');
        }

        $accountId = (int)$this->getRequest()->getParam('id', 0);

        if ($accountId === 0) {
            $this->_forward('index');
        }

        $account = $this->accountRepository->get($accountId);

        $data = $post->toArray();

        $unmanagedListingSettings = $account->getUnmanagedListingSettings()
                                            ->createWithSync((bool)(int)$data['other_listings_synchronization'])
                                            ->createWithMapping((bool)(int)$data['other_listings_mapping_mode'])
                                            ->createWithMappingSettings(
                                                $data['other_listings_mapping']['sku'],
                                                $data['other_listings_mapping']['ean'],
                                                $data['other_listings_mapping']['item_id'],
                                            )->createWithRelatedStoreId((int)$data['related_store_id']);

        $orderSettings = $account->getOrdersSettings()
                                 ->createWith($data['magento_orders_settings']);

        $invoicesAndShipmentSettings = $account->getInvoiceAndShipmentSettings()
                                               ->createWithMagentoShipment((bool)(int)$data['create_magento_shipment'])
                                               ->createWithMagentoInvoice((bool)(int)$data['create_magento_invoice'])
                                               ->uploadMagentoInvoice((bool)(int)$data['upload_magento_invoice']);

        $this->accountUpdate->updateSettings(
            $account,
            $data['title'],
            $unmanagedListingSettings,
            $orderSettings,
            $invoicesAndShipmentSettings,
        );

        if ($this->isAjax()) {
            $this->setJsonContent(['success' => true]);

            return $this->getResult();
        }

        $this->messageManager->addSuccess(__('Account was saved'));

        return $this->_redirect(
            $this->urlHelper->getBackUrl(
                'list',
                [],
                [
                    'edit' => [
                        'id' => $account->getId(),
                        '_current' => true,
                    ],
                ],
            ),
        );
    }
}
