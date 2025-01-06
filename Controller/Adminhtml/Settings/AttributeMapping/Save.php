<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Settings\AttributeMapping;

class Save extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractSettings
{
    private \M2E\Kaufland\Model\AttributeMapping\GpsrService $gpsrService;

    public function __construct(
        \M2E\Kaufland\Model\AttributeMapping\GpsrService $gpsrService
    ) {
        parent::__construct();

        $this->gpsrService = $gpsrService;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $wasChangedGpsr = false;
        if (!empty($post['gpsr_attributes'])) {
            $wasChangedGpsr = $this->processGpsrAttributes($post['gpsr_attributes']);
        }

        $this->setJsonContent(
            [
                'success' => true,
                'was_changed_gpsr' => $wasChangedGpsr,
            ]
        );

        return $this->getResult();
    }

    private function processGpsrAttributes(array $gpsrAttributes): bool
    {
        $attributes = [];
        foreach ($gpsrAttributes as $channelCode => $magentoCode) {
            if (empty($magentoCode)) {
                continue;
            }

            $attributes[] = new \M2E\Kaufland\Model\AttributeMapping\Gpsr\Pair(
                null,
                \M2E\Kaufland\Model\AttributeMapping\GpsrService::MAPPING_TYPE,
                \M2E\Kaufland\Model\AttributeMapping\Gpsr\Provider::getAttributeTitle($channelCode) ?? $channelCode,
                $channelCode,
                $magentoCode
            );
        }

        return $this->gpsrService->save($attributes) > 0;
    }
}
