<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Settings\License;

class RefreshStatus extends \M2E\Kaufland\Controller\Adminhtml\AbstractBase
{
    private \M2E\Kaufland\Model\Servicing\Dispatcher $servicing;

    public function __construct(
        \M2E\Kaufland\Model\Servicing\Dispatcher $servicing,
        \M2E\Kaufland\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->servicing = $servicing;
    }

    public function execute()
    {
        try {
            $this->servicing->processTask(
                \M2E\Kaufland\Model\Servicing\Task\License::NAME,
            );
        } catch (\Throwable $e) {
            $this->messageManager->addError(
                (string)__($e->getMessage()),
            );

            $this->setJsonContent([
                'success' => false,
                'message' => __($e->getMessage()),
            ]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'success' => true,
            'message' => __('The License has been refreshed.'),
        ]);

        return $this->getResult();
    }
}
