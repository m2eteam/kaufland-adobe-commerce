<?php

namespace M2E\Kaufland\Block\Adminhtml\Listing\View;

abstract class Switcher extends \M2E\Kaufland\Block\Adminhtml\Switcher
{
    protected $paramName = 'view_mode';
    protected $viewMode = null;

    protected \M2E\Kaufland\Helper\Data\Session $sessionDataHelper;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Helper\Data\Session $sessionDataHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->sessionDataHelper = $sessionDataHelper;
        $this->listingRepository = $listingRepository;
        parent::__construct($context, $data);
    }

    abstract protected function getComponentMode();

    abstract protected function getDefaultViewMode();

    public function getLabel()
    {
        return (string)__('View Mode');
    }

    public function hasDefaultOption(): bool
    {
        return false;
    }

    public function getStyle()
    {
        return self::ADVANCED_STYLE;
    }

    public function getDefaultParam()
    {
        $listing = $this->listingRepository->get($this->getRequest()->getParam('id'));

        $sessionViewMode = $this->sessionDataHelper->getValue(
            "{$this->getComponentMode()}_listing_{$listing->getId()}_view_mode"
        );

        if ($sessionViewMode === null) {
            return $this->getDefaultViewMode();
        }

        return $sessionViewMode;
    }

    public function getSelectedParam()
    {
        if ($this->viewMode !== null) {
            return $this->viewMode;
        }

        $selectedViewMode = parent::getSelectedParam();

        $listing = $this->listingRepository->get($this->getRequest()->getParam('id'));

        $this->sessionDataHelper->setValue(
            "{$this->getComponentMode()}_listing_{$listing->getId()}_view_mode",
            $selectedViewMode
        );

        $this->viewMode = $selectedViewMode;

        return $this->viewMode;
    }
}
