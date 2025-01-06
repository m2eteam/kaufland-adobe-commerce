<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Settings\AttributeMapping;

class SetGpsrToCategory extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractSettings
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
        try {
            $this->gpsrService->setToCategories();

            $this->setJsonContent(['success' => true]);
        } catch (\Throwable $e) {
            $this->setJsonContent(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->getResult();
    }
}
