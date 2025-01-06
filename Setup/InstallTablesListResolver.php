<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup;

use M2E\Core\Model\Setup\InstallTablesListResolverInterface;

class InstallTablesListResolver implements InstallTablesListResolverInterface
{
    private \M2E\Core\Helper\Magento $magentoHelper;

    public function __construct(\M2E\Core\Helper\Magento $magentoHelper)
    {
        $this->magentoHelper = $magentoHelper;
    }

    public function list(\Magento\Framework\DB\Adapter\AdapterInterface $connection): array
    {
        $likeCondition = $this->magentoHelper->getDatabaseTablesPrefix()
            . \M2E\Kaufland\Helper\Module\Database\Tables::PREFIX
            . '%';

        return $connection->getTables($likeCondition);
    }
}
