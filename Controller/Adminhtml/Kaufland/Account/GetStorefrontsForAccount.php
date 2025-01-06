<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Account;

class GetStorefrontsForAccount extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractAccount
{
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;

    public function __construct(
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository
    ) {
        parent::__construct();

        $this->storefrontRepository = $storefrontRepository;
    }

    public function execute()
    {
        $accountId = $this->getRequest()->getParam('account_id');

        if (empty($accountId)) {
            $this->setJsonContent([
                'result' => false,
                'message' => 'Account Id is required',
            ]);

            return $this->getResult();
        }

        $storefronts = $this->storefrontRepository->findForAccount((int)$accountId);
        $storefronts = array_map(static function (\M2E\Kaufland\Model\Storefront $entity) {
            return [
                'id' => $entity->getId(),
                'storefront_name' => $entity->getTitle(),
            ];
        }, $storefronts);

        $this->setJsonContent([
            'result' => true,
            'storefronts' => $storefronts,
        ]);

        return $this->getResult();
    }
}
