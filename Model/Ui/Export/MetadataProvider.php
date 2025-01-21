<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Ui\Export;

class MetadataProvider extends \Magento\Ui\Model\Export\MetadataProvider
{
    /**
     * Retrieve Headers row array for Export
     *
     * @param \Magento\Framework\View\Element\UiComponentInterface $component
     *
     * @return string[]
     * @throws \Exception
     */
    public function getHeaders(\Magento\Framework\View\Element\UiComponentInterface $component): array
    {
        $row = [];
        foreach ($this->getColumns($component) as $column) {
            $row[] = $column->getData('config/exportLabel') ?? $column->getData('config/label');
        }

        return $row;
    }

    /**
     * Returns columns list
     *
     * @param \Magento\Framework\View\Element\UiComponentInterface $component
     *
     * @return \Magento\Framework\View\Element\UiComponentInterface[]
     * @throws \Exception
     */
    protected function getColumns(\Magento\Framework\View\Element\UiComponentInterface $component): array
    {
        if (!isset($this->columns[$component->getName()])) {
            $columns = $this->getColumnsComponent($component);
            foreach ($columns->getChildComponents() as $column) {
                if ($this->isExportableColumn($column)) {
                    $this->columns[$component->getName()][$column->getName()] = $column;
                }
            }
        }

        return $this->columns[$component->getName()];
    }

    private function isExportableColumn(\Magento\Framework\View\Element\UiComponentInterface $column): bool
    {
        return $column->getData('config/label')
            && $column->getData('config/dataType') !== 'actions'
            && $column->getData('config/exportable') !== false;
    }
}
