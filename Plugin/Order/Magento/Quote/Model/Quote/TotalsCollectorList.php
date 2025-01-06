<?php

namespace M2E\Kaufland\Plugin\Order\Magento\Quote\Model\Quote;

class TotalsCollectorList extends \M2E\Kaufland\Plugin\AbstractPlugin
{
    /**
     * @psalm-suppress UndefinedClass
     * @var \Magento\Quote\Model\Quote\Address\Total\CollectorFactory
     */
    protected $totalCollectorFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /** @var array */
    protected $collectorsByStores = [];

    /**
     * @psalm-suppress UndefinedClass
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\Address\Total\CollectorFactory $totalCollectorFactory
    ) {
        $this->totalCollectorFactory = $totalCollectorFactory;
        $this->storeManager = $storeManager;
    }

    //########################################

    public function aroundGetCollectors($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getCollectors', $interceptor, $callback, $arguments);
    }

    // ---------------------------------------

    /**
     * \Magento\Quote\Model\Quote\TotalsCollectorList may cache the totalCollector for the incorrect Store View
     *
     * @param \Magento\Quote\Model\Quote\TotalsCollectorList $interceptor
     * @param \Closure $callback
     * @param array $arguments
     *
     * @return mixed
     */
    protected function processGetCollectors($interceptor, \Closure $callback, array $arguments)
    {
        $storeId = isset($arguments[0]) ? $arguments[0] : null;

        if ($storeId === null) {
            return $callback(...$arguments);
        }

        if (empty($this->collectorsByStores[$storeId])) {
            /**
             * @psalm-suppress UndefinedClass
             * @var \Magento\Quote\Model\Quote\Address\Total\Collector $totalCollector
             */
            $totalCollector = $this->totalCollectorFactory->create(
                ['store' => $this->storeManager->getStore($storeId)]
            );

            $this->collectorsByStores[$storeId] = $totalCollector->getCollectors();
        }

        return $this->collectorsByStores[$storeId];
    }

    //########################################
}
