<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\SellingFormat;

class ChangeProcessor extends \M2E\Kaufland\Model\Template\ChangeProcessor\ChangeProcessorAbstract
{
    public const INSTRUCTION_INITIATOR = 'template_selling_format_change_processor';

    protected function getInstructionInitiator(): string
    {
        return self::INSTRUCTION_INITIATOR;
    }

    /**
     * @param \M2E\Kaufland\Model\Template\SellingFormat\Diff $diff
     * @param int $status
     *
     * @return array
     */
    protected function getInstructionsData(
        \M2E\Kaufland\Model\ActiveRecord\Diff $diff,
        int $status
    ): array {
        $data = [];

        /** @var \M2E\Kaufland\Model\Template\SellingFormat\Diff $diff */
        if ($diff->isQtyDifferent()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
                'priority' => 80,
            ];
        }

        if ($diff->isPriceDifferent()) {
            $priority = 5;

            if ($status === \M2E\Kaufland\Model\Product::STATUS_LISTED) {
                $priority = 60;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        return $data;
    }
}
