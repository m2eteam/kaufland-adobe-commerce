<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Tag;

use M2E\Kaufland\Model\Product\Action\Validator\ValidatorMessage;

class ValidatorIssues
{
    public const NOT_USER_ERROR = 'not-user-error';

    public const ERROR_QUANTITY_POLICY_CONTRADICTION = '0001-m2e';
    public const ERROR_ZERO_QUANTITY = '0002-m2e';
    public const ERROR_ZERO_PRICE = '0003-m2e';
    public const ERROR_NO_DESCRIPTION_POLICY = '0004-m2e';
    public const ERROR_EAN_MISSING = '0005-m2e';
    public const ERROR_NO_SHIPPING_POLICY = '0006-m2e';
    public const ERROR_NO_CONDITION_SET = '0007-m2e';
    public const ERROR_SKU_MISSING = '0008-m2e';
    public const ERROR_REQUIRED_ATTRIBUTE_MISSING = '0009-m2e';
    public const ERROR_DUPLICATE_SKU_IN_UNMANAGED = '0010-m2e';
    public const ERROR_DUPLICATE_SKU_IN_LISTING = '0011-m2e';
    public const ERROR_HANDLING_TIME_INVALID = '0012-m2e';
    public const ERROR_HANDLING_TIME_OUT_OF_RANGE = '0013-m2e';

    public function mapByCode(string $code): ?ValidatorMessage
    {
        $map = [
            self::ERROR_QUANTITY_POLICY_CONTRADICTION => (string)__('You’re submitting an item with QTY contradicting the QTY settings in your Selling Policy.'),
            self::ERROR_ZERO_QUANTITY => (string)__('Cannot submit an Item with zero quantity.'),
            self::ERROR_ZERO_PRICE => (string)__('The Price must be greater than 0.'),
            self::ERROR_NO_DESCRIPTION_POLICY => (string)__('No Description policy is set for this Listing.'),
            self::ERROR_EAN_MISSING => (string)__('EAN is missing a value.'),
            self::ERROR_NO_SHIPPING_POLICY => (string)__('No Shipping policy is set for this Listing.'),
            self::ERROR_NO_CONDITION_SET => (string)__('No Condition is set for this Listing.'),
            self::ERROR_SKU_MISSING => (string)__('Product was not Listed. The SKU value is missing.'),
            self::ERROR_REQUIRED_ATTRIBUTE_MISSING => (string)__(
                'The required %channel_title attribute is missing a value.',
                ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
            ),
            self::ERROR_DUPLICATE_SKU_IN_UNMANAGED => (string)__('Product with the same SKU already exists in Unmanaged Items.'),
            self::ERROR_DUPLICATE_SKU_IN_LISTING => (string)__('Product with the same SKU already exists in another Listing.'),
            self::ERROR_HANDLING_TIME_INVALID => (string)__('Handling Time is missing or invalid.'),
            self::ERROR_HANDLING_TIME_OUT_OF_RANGE => (string)__('Handling Time must be a positive whole number less than 100.'),
        ];

        if (!isset($map[$code])) {
            return null;
        }

        return new ValidatorMessage(
            $map[$code],
            $code
        );
    }
}
