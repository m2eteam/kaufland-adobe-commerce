<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Dashboard\Shipments;

class InfoProvider implements \M2E\Core\Model\Dashboard\Shipments\InfoProviderInterface
{
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory;

    public function __construct(
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory
    ) {
        $this->urlHelper = $urlHelper;
        $this->dateRangeFactory = $dateRangeFactory;
    }

    public function getUrlForLateShipments(): string
    {
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();

        return $this->getUrl([
            'status' => \M2E\Kaufland\Model\Order::STATUS_UNSHIPPED,
            'delivery_time_expires_date[to]' => \M2E\Core\Helper\Date::convertToLocalFormat($currentDate),
            'delivery_time_expires_date[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
        ]);
    }

    public function getUrlForShipByToday(): string
    {
        $dateRange = $this->dateRangeFactory->createForToday();
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();

        return $this->getUrl([
            'status' => \M2E\Kaufland\Model\Order::STATUS_UNSHIPPED,
            'delivery_time_expires_date[from]' => \M2E\Core\Helper\Date::convertToLocalFormat($currentDate),
            'delivery_time_expires_date[to]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateEnd),
            'delivery_time_expires_date[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
        ]);
    }

    public function getUrlForShipByTomorrow(): string
    {
        $dateRange = $this->dateRangeFactory->createForTomorrow();

        return $this->getUrl([
            'status' => \M2E\Kaufland\Model\Order::STATUS_UNSHIPPED,
            'delivery_time_expires_date[from]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateStart),
            'delivery_time_expires_date[to]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateEnd),
            'delivery_time_expires_date[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
        ]);
    }

    public function getUrlForTwoAndMoreDays(): string
    {
        $dateRange = $this->dateRangeFactory->createForTwoAndMoreDays();

        return $this->getUrl([
            'status' => \M2E\Kaufland\Model\Order::STATUS_UNSHIPPED,
            'delivery_time_expires_date[from]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateStart),
            'delivery_time_expires_date[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
        ]);
    }

    private function getUrl(array $filterParams): string
    {
        return $this->urlHelper->getUrlWithFilter('m2e_kaufland/kaufland_order/index', $filterParams);
    }
}
