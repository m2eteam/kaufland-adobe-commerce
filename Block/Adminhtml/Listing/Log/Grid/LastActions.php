<?php

namespace M2E\Kaufland\Block\Adminhtml\Listing\Log\Grid;

class LastActions extends \M2E\Kaufland\Block\Adminhtml\Log\Grid\LastActions
{
    /** @var \M2E\Kaufland\Helper\View */
    protected $viewHelper;

    public function __construct(
        \M2E\Kaufland\Helper\View $viewHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\Core\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $dataHelper, $data);
        $this->viewHelper = $viewHelper;
    }

    protected function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('lastProductActions');
        // ---------------------------------------
    }

    //########################################

    protected function getActions(array $logs)
    {
        $actions = $this->getGroupedActions($logs);

        $this->sortActions($actions);
        $this->sortActionLogs($actions);

        return array_slice($actions, 0, self::ACTIONS_COUNT);
    }

    protected function getGroupedActions(array $logs)
    {
        $groupedLogsByAction = [];

        foreach ($logs as $log) {
            $log['description'] = $this->viewHelper->getModifiedLogMessage($log['description']);
            $groupedLogsByAction[$log['action_id']][] = $log;
        }

        $actions = [];

        foreach ($groupedLogsByAction as $actionLogs) {
            $actions[] = [
                'type' => $this->getMainType($actionLogs),
                'date' => $date = $this->getMainDate($actionLogs),
                'localized_date' => $this->_localeDate->formatDate($date, \IntlDateFormatter::MEDIUM, true),
                'action' => $this->getActionTitle($actionLogs),
                'initiator' => $this->getInitiator($actionLogs),
                'items' => $actionLogs,
            ];
        }

        return $actions;
    }

    //########################################
}
