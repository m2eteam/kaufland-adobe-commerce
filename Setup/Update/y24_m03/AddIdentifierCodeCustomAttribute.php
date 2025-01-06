<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m03;

class AddIdentifierCodeCustomAttribute extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $connection = $this->getConnection();
        $configTable = $this->getFullTableName('m2e_kaufland_config');

        if (!$connection->isTableExists($configTable)) {
            return;
        }

        $dataToInsert = [
            [
                'group' => '/kaufland/configuration/',
                'key' => 'identifier_code_mode',
                'value' => '1',
            ],
            [
                'group' => '/kaufland/configuration/',
                'key' => 'identifier_code_custom_attribute',
                'value' => 'ean',
            ],
        ];

        foreach ($dataToInsert as $data) {
            $connection->insert($configTable, $data);
        }
    }
}
