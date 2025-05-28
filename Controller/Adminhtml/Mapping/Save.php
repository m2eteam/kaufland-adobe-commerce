<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Mapping;

class Save extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractMapping
{
    private \M2E\Kaufland\Model\AttributeMapping\GeneralService $generalService;

    public function __construct(
        \M2E\Kaufland\Model\AttributeMapping\GeneralService $generalService
    ) {
        parent::__construct();

        $this->generalService = $generalService;
    }

    public function execute()
    {
        $attributes = [];
        $post = $this->getRequest()->getPostValue();
        if (!empty($post['general_attributes'])) {
            foreach ($post['general_attributes'] as $channelCode => $generalAttribute) {
                $attributes[] = new \M2E\Kaufland\Model\AttributeMapping\General\Pair(
                    \M2E\Kaufland\Model\AttributeMapping\GeneralService::MAPPING_TYPE,
                    (string)$generalAttribute['title'],
                    (string)$channelCode,
                    (string)$generalAttribute['magento_code']
                );
            }
            $this->generalService->update($attributes);
        }

        $this->setJsonContent(
            [
                'success' => true,
            ]
        );

        return $this->getResult();
    }
}
