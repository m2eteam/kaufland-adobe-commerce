<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Select;

use M2E\Kaufland\Model\Product;
use Magento\Framework\Data\OptionSourceInterface;

class ProductStatus implements OptionSourceInterface
{
    public const STATUS_INCOMPLETE = 'Incomplete';

    public function toOptionArray(): array
    {
        $options = [];

        $statuses = [
            Product::STATUS_NOT_LISTED => Product::getStatusTitle(Product::STATUS_NOT_LISTED),
            Product::STATUS_LISTED => Product::getStatusTitle(Product::STATUS_LISTED),
            Product::STATUS_INACTIVE => Product::getStatusTitle(Product::STATUS_INACTIVE),
            self::STATUS_INCOMPLETE => Product::getIncompleteStatusTitle(),

        ];

        foreach ($statuses as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $options;
    }
}
