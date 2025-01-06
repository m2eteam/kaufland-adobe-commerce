<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Database;

use M2E\Kaufland\Model\Exception;

class TableModelFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\Kaufland\Helper\Module\Database\Structure $dbStructureHelper;
    private \M2E\Core\Model\ResourceModel\Setup\CollectionFactory $setupCollectionFactory;
    private \M2E\Core\Model\ResourceModel\Config\CollectionFactory $configCollectionFactory;
    private \M2E\Core\Model\ResourceModel\Registry\CollectionFactory $registryCollectionFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Kaufland\Helper\Module\Database\Structure $dbStructureHelper,
        \M2E\Core\Model\ResourceModel\Setup\CollectionFactory $setupCollectionFactory,
        \M2E\Core\Model\ResourceModel\Config\CollectionFactory $configCollectionFactory,
        \M2E\Core\Model\ResourceModel\Registry\CollectionFactory $registryCollectionFactory
    ) {
        $this->objectManager = $objectManager;
        $this->dbStructureHelper = $dbStructureHelper;
        $this->setupCollectionFactory = $setupCollectionFactory;
        $this->configCollectionFactory = $configCollectionFactory;
        $this->registryCollectionFactory = $registryCollectionFactory;
    }

    public function create(string $tableName): TableModel
    {
        return $this->objectManager->create(
            TableModel::class,
            [
                'tableName' => $tableName,
                'collection' => $this->findCollection($tableName),
            ]
        );
    }

    private function findCollection(
        string $tableName
    ): \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
        $declaredCollection = $this->findDeclaredCollection($tableName);
        if ($declaredCollection !== null) {
            return $declaredCollection;
        }

        $resourceModelName = $this->dbStructureHelper->getTableModel($tableName);
        if (!$resourceModelName) {
            throw new Exception("Specified table '$tableName' cannot be managed.");
        }

        $modelName = $this->resolveModelNameBySubClass($resourceModelName);

        return $this->objectManager->create($modelName)->getCollection();
    }

    private function resolveModelNameBySubClass(string $modelName): string
    {
        $modelClassName = str_replace('ResourceModel\\', '', $modelName);
        $reflection = new \ReflectionClass($modelClassName);

        if ($reflection->isSubclassOf(\M2E\Kaufland\Model\ActiveRecord\AbstractModel::class)) {
            return $modelClassName;
        }

        return sprintf('%s\Entity', $modelName);
    }

    private function findDeclaredCollection(
        string $tableName
    ): ?\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
        $list = [
            'setup' => $this->setupCollectionFactory->create()
                                                    ->addFieldToFilter(
                                                        \M2E\Core\Model\ResourceModel\Setup::COLUMN_EXTENSION_NAME,
                                                        ['eq' => \M2E\Kaufland\Helper\Module::IDENTIFIER]
                                                    ),

            \M2E\Core\Helper\Module\Database\Tables::TABLE_NAME_CONFIG => $this->configCollectionFactory->create()
                                                                                                        ->addFieldToFilter(
                                                                                                            \M2E\Core\Model\ResourceModel\Config::COLUMN_EXTENSION_NAME,
                                                                                                            ['eq' => \M2E\Kaufland\Helper\Module::IDENTIFIER]
                                                                                                        ),

            \M2E\Core\Helper\Module\Database\Tables::TABLE_NAME_REGISTRY => $this->registryCollectionFactory->create()
                                                                                                            ->addFieldToFilter(
                                                                                                                \M2E\Core\Model\ResourceModel\Registry::COLUMN_EXTENSION_NAME,
                                                                                                                ['eq' => \M2E\Kaufland\Helper\Module::IDENTIFIER]
                                                                                                            ),
        ];

        return $list[$tableName] ?? null;
    }
}
