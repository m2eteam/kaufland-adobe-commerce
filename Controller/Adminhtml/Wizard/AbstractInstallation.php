<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Wizard;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractWizard;

abstract class AbstractInstallation extends AbstractWizard
{
    protected function getNick(): string
    {
        return \M2E\Kaufland\Helper\View\Kaufland::WIZARD_INSTALLATION_NICK;
    }

    protected function init(): void
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Configuration of %channel Integration', ['channel' => (string)__('Kaufland')]));
    }
}
