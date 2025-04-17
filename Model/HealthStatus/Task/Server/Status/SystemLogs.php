<?php

namespace M2E\Kaufland\Model\HealthStatus\Task\Server\Status;

use M2E\Kaufland\Model\HealthStatus\Task\IssueType;
use M2E\Kaufland\Model\HealthStatus\Task\Result as TaskResult;

class SystemLogs extends IssueType
{
    private const COUNT_CRITICAL_LEVEL = 1500;
    private const COUNT_WARNING_LEVEL = 500;
    private const SEE_TO_BACK_INTERVAL = 3600;

    private TaskResult\Factory $resultFactory;
    private \Magento\Framework\UrlInterface $urlBuilder;
    private \M2E\Kaufland\Model\Log\System\Repository $systemLogsRepository;

    public function __construct(
        \M2E\Kaufland\Model\HealthStatus\Task\Result\Factory $resultFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \M2E\Kaufland\Model\Log\System\Repository $systemLogsRepository
    ) {
        parent::__construct();
        $this->resultFactory = $resultFactory;
        $this->urlBuilder = $urlBuilder;
        $this->systemLogsRepository = $systemLogsRepository;
    }

    public function process(): TaskResult
    {
        $exceptionsCount = $this->getExceptionsCountByBackInterval(self::SEE_TO_BACK_INTERVAL);

        $result = $this->resultFactory->create($this);
        $result->setTaskResult(TaskResult::STATE_SUCCESS);
        $result->setTaskData($exceptionsCount);

        if ($exceptionsCount >= self::COUNT_WARNING_LEVEL) {
            $result->setTaskResult(TaskResult::STATE_WARNING);
            $result->setTaskMessage(
                __(
                    '%extension_title has recorded <b>%exception_count</b> messages to the System Log during the ' .
                    'last hour. <a target="_blank" href="%url">Click here</a> for the details.',
                    [
                        'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                        'exception_count' => $exceptionsCount,
                        'url' => $this->urlBuilder->getUrl('m2e_kaufland/synchronization_log/index')
                    ]
                )
            );
        }

        if ($exceptionsCount >= self::COUNT_CRITICAL_LEVEL) {
            $result->setTaskResult(TaskResult::STATE_CRITICAL);
            $result->setTaskMessage(
                __(
                    '%extension_title has recorded <b>%exception_count</b> messages to the System Log ' .
                    'during the last hour. <a href="%url">Click here</a> for the details.',
                    [
                        'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                        'exception_count' => $exceptionsCount,
                        'url' => $this->urlBuilder->getUrl('m2e_kaufland/synchronization_log/index')
                    ]
                )
            );
        }

        return $result;
    }

    private function getExceptionsCountByBackInterval(int $inSeconds): int
    {
        $date = \M2E\Core\Helper\Date::createCurrentGmt();
        $date->modify("- $inSeconds seconds");

        return $this->systemLogsRepository->getCountExceptionAfterDate($date);
    }
}
