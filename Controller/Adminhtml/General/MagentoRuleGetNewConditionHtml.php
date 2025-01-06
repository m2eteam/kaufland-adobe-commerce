<?php

namespace M2E\Kaufland\Controller\Adminhtml\General;

class MagentoRuleGetNewConditionHtml extends \M2E\Kaufland\Controller\Adminhtml\AbstractGeneral
{
    private \M2E\Kaufland\Model\Factory $factory;

    private \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordModelFactory;

    public function __construct(
        \M2E\Kaufland\Model\Factory $factory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory
    ) {
        parent::__construct();

        $this->factory = $factory;
        $this->activeRecordModelFactory = $activeRecordFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $prefix = $this->getRequest()->getParam('prefix');
        $storeId = $this->getRequest()->getParam('store', 0);

        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $ruleModelPrefix = '';
        $attributeCode = !empty($typeArr[1]) ? $typeArr[1] : '';
        if (count($typeArr) == 3) {
            $ruleModelPrefix = 'Kaufland\\';
            $attributeCode = !empty($typeArr[2]) ? $typeArr[2] : '';
        }

        $model = $this->factory->getObject($type)
                               ->setId($id)
                               ->setType($type)
                               ->setRule(
                                   $this->activeRecordModelFactory->getObject($ruleModelPrefix . 'Magento\Product\Rule')
                               )
                               ->setPrefix($prefix);

        if ($type == $ruleModelPrefix . 'Magento\Product\Rule\Condition\Combine') {
            $model->setData($prefix, []);
        }

        if (!empty($attributeCode)) {
            $model->setAttribute($attributeCode);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\ConditionInterface) {
            /** @var \M2E\Kaufland\Model\Magento\Product\Rule\Condition\Combine $model */
            $model->setJsFormObject($prefix);
            $model->setStoreId($storeId);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->setAjaxContent($html);

        return $this->getResult();
    }
}
