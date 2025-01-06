<?php

namespace M2E\Kaufland\Controller\Adminhtml\General;

class ValidationCheckRepetitionValue extends \M2E\Kaufland\Controller\Adminhtml\AbstractGeneral
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

        $dataField = $this->getRequest()->getParam('data_field', '');
        $dataValue = $this->getRequest()->getParam('data_value', '');

        if ($model == '' || $dataField == '' || $dataValue == '') {
            $this->setJsonContent(['result' => false]);

            return $this->getResult();
        }

        $collection = $this->activeRecordModelFactory->getObject($model)->getCollection();

        if ($dataField != '' && $dataValue != '') {
            $collection->addFieldToFilter($dataField, ['in' => [$dataValue]]);
        }

        $idField = $this->getRequest()->getParam('id_field', 'id');
        $idValue = $this->getRequest()->getParam('id_value', '');

        if ($idField != '' && $idValue != '') {
            $collection->addFieldToFilter($idField, ['nin' => [$idValue]]);
        }

        $filterField = $this->getRequest()->getParam('filter_field');
        $filterValue = $this->getRequest()->getParam('filter_value');

        if ($filterField && $filterValue) {
            $collection->addFieldToFilter($filterField, $filterValue);
        }

        $this->setJsonContent(['result' => !(bool)$collection->getSize()]);

        return $this->getResult();
    }
}
