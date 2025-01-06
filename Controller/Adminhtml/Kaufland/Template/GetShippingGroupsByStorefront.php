<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

class GetShippingGroupsByStorefront extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    private \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Template\Manager $templateManager,
        \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository
    ) {
        parent::__construct($templateManager);
        $this->shippingGroupRepository = $shippingGroupRepository;
    }

    public function execute()
    {
        $storefrontId = (int)$this->getRequest()->getParam('storefront_id');
        $shippingGroups = $this->shippingGroupRepository->findByStorefrontId($storefrontId);

        $arrayShippingGroups = [];
          /** @var \M2E\Kaufland\Model\ShippingGroup $shippingGroup */
        foreach ($shippingGroups as $shippingGroup) {
            $arrayShippingGroups[] = [
                'shipping_group_id' => $shippingGroup->getId(),
                'name' => $shippingGroup->getName(),
            ];
        }

        $this->setJsonContent($arrayShippingGroups);

        return $this->getResult();
    }
}
