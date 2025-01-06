<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Category;

class Manually extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    use \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\WizardTrait;

    private array $categoriesData;
    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        array $categoriesData,
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->categoriesData = $categoriesData;
        $this->wizardManagerFactory = $wizardManagerFactory;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingCategoryManually');

        $wizardId = $this->getWizardIdFromRequest();
        $wizardManager = $this->wizardManagerFactory->createById($wizardId);

        $this->_headerText = $this->__('Set Category (manually)');

        $this->prepareButtons(
            [
                'id' => 'listing_category_continue_btn',
                'class' => 'action-primary forward',
                'label' => __('Continue'),
                'onclick' => 'ListingWizardCategoryModeManuallyGridObj.completeCategoriesDataStep(1, 0);',
            ],
            $wizardManager,
        );
    }

    protected function _prepareLayout()
    {
        $gridBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Category\ModeManually\Grid::class,
                '',
                [
                    'wizardId' => $this->getWizardIdFromRequest(),
                    'categoriesData' => $this->categoriesData,
                ],
            );

        $this->setChild('grid', $gridBlock);

        return parent::_prepareLayout();
    }
}
