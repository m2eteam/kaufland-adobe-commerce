<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Template\ChangeProcessor;

abstract class ChangeProcessorAbstract extends \M2E\Kaufland\Model\Template\ChangeProcessorAbstract
{
    public const INSTRUCTION_TYPE_QTY_DATA_CHANGED = 'template_qty_data_changed';
    public const INSTRUCTION_TYPE_PRICE_DATA_CHANGED = 'template_price_data_changed';
    public const INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED = 'template_shipping_data_changed';
}
