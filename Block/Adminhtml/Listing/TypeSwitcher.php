<?php

namespace M2E\Kaufland\Block\Adminhtml\Listing;

abstract class TypeSwitcher extends \M2E\Kaufland\Block\Adminhtml\Switcher
{
    public const LISTING_TYPE_M2E_PRO = 'product';
    public const LISTING_TYPE_LISTING_OTHER = 'other';

    protected $paramName = 'listing_type';

    //########################################

    public function getLabel()
    {
        return (string)__('Listing Type');
    }

    public function hasDefaultOption(): bool
    {
        return false;
    }

    //---------------------------------------

    protected function loadItems()
    {
        $this->items = [
            'mode' => [
                'value' => [
                    [
                        'label' => __('M2E Kaufland'),
                        'value' => self::LISTING_TYPE_M2E_PRO,
                    ],
                    [
                        'label' => __('Unmanaged'),
                        'value' => self::LISTING_TYPE_LISTING_OTHER,
                    ],
                ],
            ],
        ];
    }

    //########################################
}
