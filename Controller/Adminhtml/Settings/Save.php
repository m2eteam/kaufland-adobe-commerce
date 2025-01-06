<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Settings;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractSettings;

class Save extends AbstractSettings
{
    private \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration;

    public function __construct(
        \M2E\Kaufland\Helper\Component\Kaufland\Configuration $componentConfiguration
    ) {
        parent::__construct();

        $this->configuration = $componentConfiguration;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->setJsonContent(['success' => false]);

            return $this->getResult();
        }

        $this->configuration->setConfigValues($this->getRequest()->getParams());
        $this->setJsonContent(['success' => true]);

        return $this->getResult();
    }
}
