<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Wizard;

use M2E\Kaufland\Model\Wizard;

class InstallationKaufland extends Wizard
{
    /** @var string[] */
    protected $steps = [
        'registration',
        'account',
        'settings',

        'listingTutorial',
        'listingGeneral',
    ];

    /**
     * @return string
     */
    public function getNick()
    {
        return \M2E\Kaufland\Helper\View\Kaufland::WIZARD_INSTALLATION_NICK;
    }
}
