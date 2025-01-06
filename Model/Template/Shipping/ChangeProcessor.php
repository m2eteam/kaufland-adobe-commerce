<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Shipping;

class ChangeProcessor extends \M2E\Kaufland\Model\Kaufland\Template\ChangeProcessor\ChangeProcessorAbstract
{
    public const INSTRUCTION_INITIATOR = 'template_shipping_change_processor';

    protected function getInstructionInitiator(): string
    {
        return self::INSTRUCTION_INITIATOR;
    }

    /**
     * @param \M2E\Kaufland\Model\Template\Shipping\Diff $diff
     * @param int $status
     *
     * @return array
     */
    protected function getInstructionsData(
        \M2E\Kaufland\Model\ActiveRecord\Diff $diff,
        int $status
    ): array {
        $data = [];

        /** @var \M2E\Kaufland\Model\Template\Shipping\Diff $diff */
        if ($diff->isShippingDifferent()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED,
                'priority' => 80,
            ];
        }

        return $data;
    }
}
