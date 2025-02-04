<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y25_m01;

class AddTrackDirectDatabaseChanges extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $config = $this->getConfigModifier(\M2E\Kaufland\Helper\Module::IDENTIFIER);

        $config->delete('/listing/product/inspector/', 'max_allowed_instructions_count');

        $config->insert(
            \M2E\Kaufland\Helper\Module\Configuration::CONFIG_GROUP,
            'listing_product_inspector_mode',
            '0'
        );
        $config->insert(
            \M2E\Kaufland\Model\Product\InspectDirectChanges\Config::GROUP,
            \M2E\Kaufland\Model\Product\InspectDirectChanges\Config::KEY_MAX_ALLOWED_PRODUCT_COUNT,
            '2000'
        );
    }
}
