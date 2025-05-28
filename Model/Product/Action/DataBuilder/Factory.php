<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\DataBuilder;

class Factory
{
    private const ALLOWED_BUILDERS = [
        Price::NICK => Price::class,
        Qty::NICK => Qty::class,
        Title::NICK => Title::class,
        Description::NICK => Description::class,
        Images::NICK => Images::class,
        Attributes::NICK => Attributes::class,
    ];

    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $nick): AbstractDataBuilder
    {
        if (!isset(self::ALLOWED_BUILDERS[$nick])) {
            throw new \M2E\Kaufland\Model\Exception\Logic(sprintf('Unknown builder - %s', $nick));
        }

        /** @var AbstractDataBuilder */
        return $this->objectManager->create(self::ALLOWED_BUILDERS[$nick]);
    }
}
