<?php

namespace M2E\Kaufland\Model\Magento;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class \M2E\Kaufland\Model\Magento\Shipping
 */
class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements CarrierInterface
{
    protected $_code = 'kauflandshipping';

    protected $resultFactory;
    protected $rateRequestFactory;
    protected $rateResultMethodFactory;
    private \M2E\Kaufland\Helper\Data\GlobalData $globalData;

    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateResultMethodFactory,
        \M2E\Kaufland\Helper\Data\GlobalData $globalData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $resultFactory,
        array $data = []
    ) {
        $this->resultFactory = $resultFactory;
        $this->rateRequestFactory = $rateRequestFactory;
        $this->rateResultMethodFactory = $rateResultMethodFactory;
        $this->globalData = $globalData;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    //########################################

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return bool|\Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $shippingData = $this->globalData->getValue('shipping_data');

        if (!$shippingData) {
            return false;
        }

        $result = $this->resultFactory->create();
        $method = $this->rateResultMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setMethod($this->_code);

        $method->setCarrierTitle($shippingData['carrier_title']);
        $method->setMethodTitle($shippingData['shipping_method']);

        $method->setCost($shippingData['shipping_price']);
        $method->setPrice($shippingData['shipping_price']);

        $result->append($method);

        return $result;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     *
     * @return bool
     */
    public function checkAvailableShipCountries(\Magento\Framework\DataObject $request)
    {
        if (!$this->globalData->getValue('shipping_data')) {
            return false;
        }

        return true;
    }

    /**
     * Get allowed shipping methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * Check if carrier has shipping tracking option available
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return false;
    }

    //########################################
}
