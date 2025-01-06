<?php

declare(strict_types=1);

namespace M2E\Kaufland\Helper\View;

class Kaufland
{
    public const NICK = 'kaufland';

    public const WIZARD_INSTALLATION_NICK = 'installationKaufland';
    public const MENU_ROOT_NODE_NICK = 'M2E_Kaufland::main';

    /** @var \M2E\Kaufland\Helper\Module\Wizard */
    private $wizard;

    public function __construct(
        \M2E\Kaufland\Helper\Module\Wizard $wizard
    ) {
        $this->wizard = $wizard;
    }

    public static function getTitle(): string
    {
        return (string)__('Kaufland Integration');
    }

    public static function getMenuRootNodeLabel(): string
    {
        return self::getTitle();
    }

    /**
     * @return string
     */
    public function getWizardInstallationNick(): string
    {
        return self::WIZARD_INSTALLATION_NICK;
    }

    /**
     * @return bool
     */
    public function isInstallationWizardFinished(): bool
    {
        return $this->wizard->isFinished(
            $this->getWizardInstallationNick()
        );
    }
}
