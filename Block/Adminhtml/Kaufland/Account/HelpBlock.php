<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Account;

class HelpBlock extends \M2E\Kaufland\Block\Adminhtml\HelpBlock
{
    public function getContent(): string
    {
        return (string)__(
            '<p>On this Page you can find information about %channel_title Accounts which can be managed via %extension_title.</p><br>
<p>Settings for such configurations as %channel_title Orders along with Magento Order creation conditions,
Unmanaged Listings import including options of Linking them to Magento Products and Moving them
to %extension_title Listings,
etc. can be specified for each Account separately.</p><br>
<p><strong>Note:</strong> %channel_title Account can be deleted only if it is not being used for any of M2E %channel_title Listings.</p>',
            [
                'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
            ]
        );
    }
}
