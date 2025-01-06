<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Inspection\Inspector;

use M2E\Kaufland\Helper\Module\Database\Structure as DatabaseStructureHelper;
use M2E\Kaufland\Model\ControlPanel\Inspection\FixerInterface;
use M2E\Kaufland\Model\ControlPanel\Inspection\InspectorInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManager;
use M2E\Kaufland\Model\ControlPanel\Inspection\Issue\Factory as IssueFactory;

class RemovedStores implements InspectorInterface, FixerInterface
{
    /** @var array */
    private $removedStoresId = [];

    /** @var StoreManager */
    private $storeManager;

    /** @var UrlInterface */
    private $urlBuilder;

    /** @var ResourceConnection */
    private $resourceConnection;

    /** @var IssueFactory */
    private $issueFactory;

    /** @var DatabaseStructureHelper */
    private DatabaseStructureHelper $databaseStructureHelper;

    public function __construct(
        UrlInterface $urlBuilder,
        ResourceConnection $resourceConnection,
        StoreManager $storeManager,
        IssueFactory $issueFactory,
        DatabaseStructureHelper $databaseStructureHelper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->issueFactory = $issueFactory;
        $this->databaseStructureHelper = $databaseStructureHelper;
    }

    private function getRemovedStores()
    {
        $existsStoreIds = array_keys($this->storeManager->getStores(true));
        $storeRelatedColumns = $this->databaseStructureHelper->getStoreRelatedColumns();

        $usedStoresIds = [];

        foreach ($storeRelatedColumns as $tableName => $columnsInfo) {
            foreach ($columnsInfo as $columnInfo) {
                $tempResult = $this->resourceConnection->getConnection()->select()
                                                       ->distinct()
                                                       ->from(
                                                           $this->databaseStructureHelper
                                                               ->getTableNameWithPrefix($tableName),
                                                           [$columnInfo['name']]
                                                       )
                                                       ->where("{$columnInfo['name']} IS NOT NULL")
                                                       ->query()
                                                       ->fetchAll(\Zend_Db::FETCH_COLUMN);

                if ($columnInfo['type'] === 'int') {
                    $usedStoresIds = array_merge($usedStoresIds, $tempResult);
                    continue;
                }

                // json
                foreach ($tempResult as $itemRow) {
                    preg_match_all('/"(store|related_store)_id":"?([\d]+)"?/', $itemRow, $matches);
                    !empty($matches[2]) && $usedStoresIds = array_merge($usedStoresIds, $matches[2]);
                }
            }
        }

        $usedStoresIds = array_values(array_unique(array_map('intval', $usedStoresIds)));
        $this->removedStoresId = array_diff($usedStoresIds, $existsStoreIds);
    }

    public function process()
    {
        $issues = [];
        $this->getRemovedStores();

        if (!empty($this->removedStoresId)) {
            $issues[] = $this->issueFactory->create(
                'Some data have nonexistent magento stores',
                $this->renderMetadata($this->removedStoresId)
            );
        }

        return $issues;
    }

    private function renderMetadata($data)
    {
        $removedStoreIds = implode(', ', $data);
        $repairStoresAction = $this->urlBuilder
            ->getUrl('kaufland/controlPanel_tools_Kaufland/general', ['action' => 'repairRemovedMagentoStore']);

        $html = <<<HTML
<div style="margin:0 0 10px">Removed Store IDs: {$removedStoreIds}</div>
<form action="{$repairStoresAction}" method="get">
    <input name="replace_from" value="" type="text" placeholder="replace from id" required/>
    <input name="replace_to" value="" type="text" placeholder="replace to id" required />
    <button type="submit">Repair</button>
</form>
HTML;

        return $html;
    }

    public function fix($data)
    {
        foreach ($data as $replaceIdFrom => $replaceIdTo) {
            $this->replaceId($replaceIdFrom, $replaceIdTo);
        }
    }

    private function replaceId($replaceIdFrom, $replaceIdTo)
    {
        $storeRelatedColumns = $this->databaseStructureHelper->getStoreRelatedColumns();
        foreach ($storeRelatedColumns as $tableName => $columnsInfo) {
            foreach ($columnsInfo as $columnInfo) {
                if ($columnInfo['type'] === 'int') {
                    $this->resourceConnection->getConnection()->update(
                        $this->databaseStructureHelper
                            ->getTableNameWithPrefix($tableName),
                        [$columnInfo['name'] => $replaceIdTo],
                        "`{$columnInfo['name']}` = {$replaceIdFrom}"
                    );

                    continue;
                }

                // json ("store_id":"10" | "store_id":10, | "store_id":10})
                $bind = [
                    $columnInfo['name'] => new \Zend_Db_Expr(
                        "REPLACE(
                        REPLACE(
                            REPLACE(
                                `{$columnInfo['name']}`,
                                'store_id\":{$replaceIdFrom},',
                                'store_id\":{$replaceIdTo},'
                            ),
                            'store_id\":\"{$replaceIdFrom}\"',
                            'store_id\":\"{$replaceIdTo}\"'
                        ),
                        'store_id\":{$replaceIdFrom}}',
                        'store_id\":{$replaceIdTo}}'
                    )"
                    ),
                ];

                $this->resourceConnection->getConnection()->update(
                    $this->databaseStructureHelper->getTableNameWithPrefix($tableName),
                    $bind,
                    "`{$columnInfo['name']}` LIKE '%store_id\":\"{$replaceIdFrom}\"%' OR
                     `{$columnInfo['name']}` LIKE '%store_id\":{$replaceIdFrom},%' OR
                     `{$columnInfo['name']}` LIKE '%store_id\":{$replaceIdFrom}}%'"
                );
            }
        }
    }
}
