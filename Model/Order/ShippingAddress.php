<?php

namespace M2E\Kaufland\Model\Order;

class ShippingAddress extends \Magento\Framework\DataObject
{
    /** @var \Magento\Directory\Model\CountryFactory */
    protected $countryFactory;
    /** @var \M2E\Kaufland\Model\Order */
    protected $order;
    /** @var \Magento\Directory\Model\Country */
    protected $country;
    /** @var \Magento\Directory\Model\Region */
    protected $region;
    /** @var \Magento\Directory\Helper\Data */
    protected $directoryHelper;

    public function __construct(
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        \M2E\Kaufland\Model\Order $order,
        array $data = []
    ) {
        $this->countryFactory = $countryFactory;
        $this->directoryHelper = $directoryHelper;
        $this->order = $order;
        parent::__construct($data);
    }

    /**
     * @return array
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getRawData()
    {
        $buyerName = $this->order->getBuyerName();
        $recipientName = $this->getData('recipient_name');

        return [
            'buyer_name' => $buyerName,
            'recipient_name' => $recipientName ?: $buyerName,
            'email' => $this->getBuyerEmail(),
            'country_id' => $this->getData('country_code'),
            'region' => $this->getData('state'),
            'city' => $this->getData('city') ? $this->getData('city') : $this->getCountryName(),
            'postcode' => $this->getPostalCode(),
            'telephone' => $this->getPhone(),
            'company' => $this->getData('company_name'),
            'street' => $this->getStreet(),
            'house_number' => $this->getData('house_number'),
        ];
    }

    public function getCountry()
    {
        if ($this->country === null) {
            $this->country = $this->countryFactory->create();

            try {
                $this->country->loadByCode($this->getData('country_code'));
            } catch (\Exception $e) {
            }
        }

        return $this->country;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception
     */
    public function getRegion()
    {
        if (!$this->getCountry()->getId()) {
            return null;
        }

        if ($this->region === null) {
            $countryRegions = $this->getCountry()->getRegionCollection();
            $countryRegions->getSelect()->where('code = ? OR default_name = ?', $this->getState());
            $this->region = $countryRegions->getFirstItem();
        }

        $isRegionRequired = $this->directoryHelper->isRegionRequired($this->getCountry()->getId());
        if ($isRegionRequired && !$this->region->getId()) {
            if (!$this->isRegionOverrideRequired()) {
                throw new \M2E\Kaufland\Model\Exception(
                    sprintf('Invalid Region/State value "%s" in the Shipping Address.', $this->getState())
                );
            }

            $countryRegions = $this->getCountry()->getRegionCollection();
            $this->region = $countryRegions->getFirstItem();
            $msg = ' Invalid Region/State value: "%s" in the Shipping Address is overridden by "%s".';
            $this->order->addInfoLog(sprintf($msg, $this->getState(), $this->region->getDefaultName()), [], [], true);
        }

        return $this->region;
    }

    public function getCountryName()
    {
        if (!$this->getCountry()->getId()) {
            return $this->getData('country_code');
        }

        return $this->getCountry()->getName();
    }

    public function getRegionId()
    {
        $region = $this->getRegion();

        if ($region === null || $region->getId() === null) {
            return null;
        }

        return $region->getId();
    }

    public function getRegionCode()
    {
        $region = $this->getRegion();

        if ($region === null || $region->getId() === null) {
            return '';
        }

        return $region->getCode();
    }

    protected function getState()
    {
        return $this->getData('state');
    }

    public function getBuyerName(): string
    {
        return $this->getData('buyer_name');
    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        if (empty(array_filter($this->_data))) {
            return true;
        }

        return false;
    }

    protected function getBuyerEmail()
    {
        $email = $this->order->getBuyerEmail();

        if (stripos($email, 'Invalid Request') !== false || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = str_replace(' ', '-', strtolower($this->order->getBuyerUserId()));
            $email .= \M2E\Core\Model\Magento\Customer::FAKE_EMAIL_POSTFIX;
        }

        return $email;
    }

    protected function getPostalCode()
    {
        $postalCode = $this->getData('postal_code');

        if (stripos($postalCode, 'Invalid Request') !== false || $postalCode == '') {
            $postalCode = '0000';
        }

        return $postalCode;
    }

    protected function getPhone()
    {
        $phone = $this->getData('phone');

        if (stripos($phone, 'Invalid Request') !== false || $phone == '') {
            $phone = '0000000000';
        }

        return $phone;
    }

    protected function getStreet()
    {
        return $this->getData('street') . ' ' . $this->getData('house_number');
    }

    protected function isRegionOverrideRequired(): bool
    {
        return $this->order->getAccount()->getOrdersSettings()->isRegionOverrideRequired();
    }
}
