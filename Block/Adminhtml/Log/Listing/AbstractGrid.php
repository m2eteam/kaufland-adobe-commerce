<?php

namespace M2E\Kaufland\Block\Adminhtml\Log\Listing;

abstract class AbstractGrid extends \M2E\Kaufland\Block\Adminhtml\Log\AbstractGrid
{
    /** @var \M2E\Kaufland\Model\ResourceModel\Collection\WrapperFactory */
    protected $wrapperCollectionFactory;
    /** @var \M2E\Kaufland\Model\Config\Manager */
    private $config;
    /** @var \M2E\Core\Helper\Data */
    protected $dataHelper;

    public function __construct(
        \M2E\Kaufland\Model\Config\Manager $config,
        \M2E\Kaufland\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Kaufland\Helper\View $viewHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Core\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->config = $config;
        $this->wrapperCollectionFactory = $wrapperCollectionFactory;
        $this->dataHelper = $dataHelper;

        parent::__construct($resourceConnection, $viewHelper, $context, $backendHelper, $data);
    }

    abstract protected function getViewMode();

    abstract protected function getLogHash($type);

    protected function addMaxAllowedLogsCountExceededNotification($date)
    {
        $notification = \M2E\Core\Helper\Data::escapeJs(
            (string)__(
                'Using a Grouped View Mode, the logs records which are not older than %date are
            displayed here in order to prevent any possible Performance-related issues.',
                ['date' => $this->_localeDate->formatDate($date, \IntlDateFormatter::MEDIUM, true)],
            )
        );

        $this->js->add("Kaufland.formData.maxAllowedLogsCountExceededNotification = '{$notification}';");
    }

    protected function getMaxLastHandledRecordsCount()
    {
        return $this->config->getGroupValue(
            '/logs/grouped/',
            'max_records_count'
        );
    }

    public function getRowUrl($item)
    {
        return false;
    }
}
