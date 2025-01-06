<?php

namespace M2E\Kaufland\Controller\Adminhtml\General;

class ModelGetAll extends \M2E\Kaufland\Controller\Adminhtml\AbstractGeneral
{
    private \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordModelFactory;

    public function __construct(\M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory)
    {
        parent::__construct();
        $this->activeRecordModelFactory = $activeRecordFactory;
    }

    public function execute()
    {
        $model = $this->getRequest()->getParam('model', '');
        $storefrontId = $this->getRequest()->getParam('storefront_id', '');
        $isCustomTemplate = $this->getRequest()->getParam('is_custom_template', null);

        $idField = $this->getRequest()->getParam('id_field', 'id');
        $dataField = $this->getRequest()->getParam('data_field', '');

        if ($model == '' || $idField == '' || $dataField == '') {
            $this->setJsonContent([]);

            return $this->getResult();
        }

        $model = str_replace('_', '\\', $model);

        $collection = $this->activeRecordModelFactory->getObject($model)->getCollection();

        $storefrontId != '' && $collection->addFieldToFilter('storefront_id', $storefrontId);
        $isCustomTemplate != null && $collection->addFieldToFilter('is_custom_template', $isCustomTemplate);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)
                   ->columns([$idField, $dataField]);

        $sortField = $this->getRequest()->getParam('sort_field', '');
        $sortDir = $this->getRequest()->getParam('sort_dir', 'ASC');

        if ($sortField != '' && $sortDir != '') {
            $collection->setOrder('main_table.' . $sortField, $sortDir);
        }

        $limit = $this->getRequest()->getParam('limit', null);
        $limit !== null && $collection->setPageSize((int)$limit);

        $data = $collection->toArray();

        $this->setJsonContent($data['items']);

        return $this->getResult();
    }
}
