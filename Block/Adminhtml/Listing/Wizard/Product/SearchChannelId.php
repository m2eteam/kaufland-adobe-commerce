<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Product;

class SearchChannelId extends \Magento\Framework\View\Element\Template
{
    public const PROGRESS_BAR_ELEMENT_ID = 'listing_wizard_product_search_channel_id_progress_bar';

    protected $_template = 'M2E_Kaufland::listing/wizard/product_search_channel_id.phtml';

    private \M2E\Kaufland\Model\Listing\Wizard\SearchChannelProductManager $searchChannelProductManager;
    private \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;
    private \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\SearchChannelProductManager $searchChannelProductManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration,
        array $data = []
    ) {
        $this->searchChannelProductManager = $searchChannelProductManager;
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;
        $this->configuration = $configuration;
        parent::__construct($context, $data);
    }

    public function getLinkForSearch(): string
    {
        return $this->getUrl(
            '*/listing_wizard_search/searchChannelId',
            ['id' => $this->uiWizardRuntimeStorage->getManager()->getWizardId()],
        );
    }

    public function getProgressBarElementId(): string
    {
        return self::PROGRESS_BAR_ELEMENT_ID;
    }

    public function isNeedSearch(): bool
    {
        if (!$this->configuration->getIdentifierCodeCustomAttribute()) {
            return false;
        }

        $manager = $this->uiWizardRuntimeStorage->getManager();

        return !$this->searchChannelProductManager->isAllFound($manager);
    }
}
