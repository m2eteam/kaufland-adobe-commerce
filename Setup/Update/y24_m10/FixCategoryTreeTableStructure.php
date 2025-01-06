<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m10;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Category\Tree as CategoryTreeResource;

class FixCategoryTreeTableStructure extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_CATEGORY_TREE);

        $modifier->changeColumn(
            CategoryTreeResource::COLUMN_STOREFRONT_ID,
            'INT UNSIGNED NOT NULL',
            null,
            CategoryTreeResource::ID_FIELD,
            false
        );

        $modifier->changeColumn(
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            'INT UNSIGNED NOT NULL',
            null,
            CategoryTreeResource::COLUMN_STOREFRONT_ID,
            false
        );

        $modifier->changeColumn(
            CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID,
            'INT UNSIGNED NOT NULL',
            null,
            CategoryTreeResource::COLUMN_CATEGORY_ID,
            false
        );

        $modifier->commit();
    }
}
