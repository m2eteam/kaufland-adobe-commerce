<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing;

class Tabs extends \M2E\Kaufland\Block\Adminhtml\Magento\Tabs\AbstractHorizontalStaticTabs
{
    private const ALL_ITEMS_TAB_ID = 'all_items';
    private const ITEMS_BY_ISSUE_TAB_ID = 'items_by_issue';
    private const ITEMS_BY_LISTING_TAB_ID = 'items_by_listing';
    private const UNMANAGED_ITEMS_TAB_ID = 'unmanaged_items';

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->accountRepository = $accountRepository;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function init(): void
    {
        $cssMb20 = 'margin-bottom: 20px;';
        $cssMb10 = 'margin-bottom: 10px;';

        // ---------------------------------------

        $this->addTab(
            self::ITEMS_BY_LISTING_TAB_ID,
            (string)__('Items By Listing'),
            $this->getUrl('*/kaufland_listing/index')
        );
        $this->registerCssForTab(self::ITEMS_BY_LISTING_TAB_ID, $cssMb20);

        // ---------------------------------------

        $this->addTab(
            self::ITEMS_BY_ISSUE_TAB_ID,
            (string)__('Items By Issue'),
            $this->getUrl('*/product_grid/issues')
        );
        $this->registerCssForTab(self::ITEMS_BY_ISSUE_TAB_ID, $cssMb20);

        // ---------------------------------------

        $firstAccount = $this->accountRepository->findFirst();
        if ($firstAccount !== null) {
            $this->addTab(
                self::UNMANAGED_ITEMS_TAB_ID,
                (string)__('Unmanaged Items'),
                $this->getUrl(
                    '*/product_grid/unmanaged',
                    ['account' => $firstAccount->getId()]
                )
            );
            $this->registerCssForTab(self::UNMANAGED_ITEMS_TAB_ID, $cssMb20);
        }

        // ---------------------------------------

        $this->addTab(
            self::ALL_ITEMS_TAB_ID,
            (string)__('All Items'),
            $this->getUrl('*/product_grid/allItems')
        );
        $this->registerCssForTab(self::ALL_ITEMS_TAB_ID, $cssMb10);

        // ---------------------------------------
    }

    /**
     * @return void
     */
    public function activateItemsByListingTab(): void
    {
        $this->setActiveTabId(self::ITEMS_BY_LISTING_TAB_ID);
    }

    /**
     * @return void
     */
    public function activateItemsByIssueTab(): void
    {
        $this->setActiveTabId(self::ITEMS_BY_ISSUE_TAB_ID);
    }

    /**
     * @return void
     */
    public function activateUnmanagedItemsTab(): void
    {
        $this->setActiveTabId(self::UNMANAGED_ITEMS_TAB_ID);
    }

    /**
     * @return void
     */
    public function activateAllItemsTab(): void
    {
        $this->setActiveTabId(self::ALL_ITEMS_TAB_ID);
    }
}
