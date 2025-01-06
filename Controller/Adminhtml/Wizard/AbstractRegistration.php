<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Wizard;

abstract class AbstractRegistration extends \M2E\Kaufland\Controller\Adminhtml\AbstractWizard
{
    protected function getCustomViewNick(): string
    {
        return '';
    }

    protected function getNick()
    {
        return null;
    }

    protected function getMenuRootNodeNick()
    {
        return null;
    }

    protected function getMenuRootNodeLabel()
    {
        return null;
    }
}
