<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs\Database\Table\Column\Filter;

class Select extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    protected function _getOptions(): array
    {
        $options = [];

        /** @var \M2E\Kaufland\Model\ActiveRecord\AbstractModel $model */
        $model = $this->getColumn()->getGrid()->getTableModel()->createModel();
        $htmlName = $this->_getHtmlName();

        $colOptions = $model->getCollection()
                            ->getSelect()
                            ->group($htmlName)
                            ->query();

        if (!empty($colOptions)) {
            $options = [['value' => null, 'label' => '']];
            foreach ($colOptions as $colOption) {
                $options[] = [
                    'value' => $colOption[$htmlName],
                    'label' => $colOption[$htmlName],
                ];
            }
        }

        return $options;
    }
}
