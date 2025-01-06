<?php

namespace M2E\Kaufland\Block\Adminhtml;

class Support extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock
{
    private \M2E\Kaufland\Helper\Module\Support $moduleSupportHelper;

    public function __construct(
        \M2E\Kaufland\Helper\Module\Support $moduleSupportHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->moduleSupportHelper = $moduleSupportHelper;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $this->appendHelpBlock([
            'no_collapse' => true,
            'no_hide' => true,
            'content' => __(
                '<p>Have any questions regarding the use of M2E Kaufland, its functionality, technical aspects, or billing?
                You can always find answers in our
                <a href="%1" target="_blank" class="external-link">documentation</a> or
                <a href="%2" target="_blank" class="external-link">Knowledge Base</a>
                created specifically for M2E Kaufland clients. There is also a
                <a href="%3" target="_blank" class="external-link">YouTube channel</a>
                with helpful video guides.</p>
                <p>In case you cannot find a solution to your problem within the available resources,
                feel free to reach out to M2E Kaufland Support Team by clicking Contact Us. If your subscription plan
                does not include a ticket system, you will receive an email with the plan\'s terms
                in response to your request.</p>',
                'https://docs-m2.m2epro.com/m2e-kaufland-user-guide',
                'https://help.m2epro.com/en/support/solutions/9000117126',
                \M2E\Kaufland\Helper\Module\Support::YOUTUBE_CHANNEL_URL
            ),
        ]);

        parent::_prepareLayout();
    }

    public function toHtml()
    {
        $summaryInfo = \M2E\Core\Helper\Json::encode(
            $this->moduleSupportHelper->getSummaryInfo()
        );

        $this->js->add(
            <<<JS
window.showContactUsWidget = function () {
    $('contact_us_button').hide();
    FreshworksWidget('open');
};

// Initialize FreshworksWidget
window.fwSettings = {
    widget_id: 9000000228
};
(function () {
    // code below used to save widget commands in queue if widget still not loaded
    // `q` means `queue`
    // widget will read this queue and run commands
    if (typeof window.FreshworksWidget != "function") {
        var handler = function () {
            handler.q.push(arguments)
        };
        handler.q = [];
        window.FreshworksWidget = handler;
    }
})();

FreshworksWidget('prefill', 'ticketForm', {
    custom_fields: {
        cf_summary_info: {$summaryInfo}
    }
});

FreshworksWidget('hide', 'ticketForm', ['custom_fields.cf_summary_info', 'custom_fields.cf_version']);
FreshworksWidget('hide');

JS
        );

        $this->js->addRequireJs(
            ['freshworks_widget' => '//widget.freshworks.com/widgets/9000000228.js'],
            ''
        );

        $button = <<<HTML
<div class="a-center">
    <input id="contact_us_button"
        value="Contact Us"
        class="action-primary Kaufland-field-without-tooltip"
        type="button"
        onclick="showContactUsWidget()">
</div>
HTML;

        return parent::toHtml() . $button;
    }
}
