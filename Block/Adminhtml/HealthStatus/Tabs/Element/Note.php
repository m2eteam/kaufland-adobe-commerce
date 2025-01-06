<?php

namespace M2E\Kaufland\Block\Adminhtml\HealthStatus\Tabs\Element;

use Magento\Framework\Data\Form\Element\Note as OriginalNote;
use M2E\Kaufland\Model\HealthStatus\Task\Result;

/**
 * Class \M2E\Kaufland\Block\Adminhtml\HealthStatus\Tabs\Element\Note
 */
class Note extends OriginalNote
{
    /** @var \M2E\Kaufland\Model\HealthStatus\Task\Result */
    private $taskResult;

    //########################################

    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        if (isset($data['task_result']) && ($data['task_result'] instanceof Result)) {
            $this->taskResult = $data['task_result'];
        }
    }

    //########################################

    public function getLabelHtml($idSuffix = '', $scopeLabel = '')
    {
        $parentHtml = parent::getLabelHtml($idSuffix, $scopeLabel);

        if ($this->taskResult !== null) {
            $labelClass = $this->getLabelClass($this->taskResult);
            $parentHtml = preg_replace('/class="(.+)"/', 'class="' . $labelClass . ' $1"', $parentHtml);
        }

        return $parentHtml;
    }

    //########################################

    private function getLabelClass(Result $result)
    {
        switch (true) {
            case $result->isCritical():
                return 'health-status-message-critical-mark';

            case $result->isWaring():
                return 'health-status-message-warning-mark';

            case $result->isNotice():
                return 'health-status-message-notice-mark';

            case $result->isSuccess():
                return 'health-status-message-success-mark';

            default:
                return '';
        }
    }

    //########################################
}
