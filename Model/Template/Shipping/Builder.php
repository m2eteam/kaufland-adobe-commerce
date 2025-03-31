<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Shipping;

use M2E\Kaufland\Model\Template\Shipping as Shipping;

class Builder extends \M2E\Kaufland\Model\Kaufland\Template\AbstractBuilder
{
    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function prepareData(): array
    {
        $data = parent::prepareData();

        $data = array_merge($this->getDefaultData(), $data);

        if (isset($this->rawData['account_id'])) {
            $data['account_id'] = (int)$this->rawData['account_id'];
        }

        if (isset($this->rawData['listing_type'])) {
            $data['listing_type'] = (int)$this->rawData['listing_type'];
        }

        if (isset($this->rawData['handling_time'])) {
            $data['handling_time'] = (int)$this->rawData['handling_time'];
        }

        if (isset($this->rawData['handling_time_mode'])) {
            $data['handling_time_mode'] = (int)$this->rawData['handling_time_mode'];
        }

        if (isset($this->rawData['handling_time_attribute'])) {
            $data['handling_time_attribute'] = $this->rawData['handling_time_attribute'];
        }

        if (isset($this->rawData['storefront_id'])) {
            $data['storefront_id'] = (int)$this->rawData['storefront_id'];
        }

        if (isset($this->rawData['warehouse_id'])) {
            $data['warehouse_id'] = (int)$this->rawData['warehouse_id'];
        }

        if (isset($this->rawData['shipping_group_id'])) {
            $data['shipping_group_id'] = (int)$this->rawData['shipping_group_id'];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getDefaultData(): array
    {
        return [
            'handling_time_mode' => Shipping::HANDLING_TIME_MODE_VALUE,
            'handling_time' => '',
            'handling_time_attribute' => '',
        ];
    }
}
