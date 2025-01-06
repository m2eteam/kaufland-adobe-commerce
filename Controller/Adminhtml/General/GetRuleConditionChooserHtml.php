<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\General;

class GetRuleConditionChooserHtml extends \M2E\Kaufland\Controller\Adminhtml\AbstractGeneral
{
    public function execute()
    {
        $request = $this->getRequest();

        switch ($request->getParam('attribute')) {
            case 'sku':
                $block = $this->getLayout()->createBlock(
                    \M2E\Kaufland\Block\Adminhtml\Magento\Product\Rule\Chooser\Sku::class,
                    'product_rule_chooser_sku',
                    [
                        'data' => [
                            'js_form_object' => $request->getParam('form'),
                            'store' => $request->getParam('store', 0),
                        ],
                    ]
                );
                break;

            case 'category_ids':
                $ids = $request->getParam('selected', []);
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int)$id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = [];
                }

                $block = $this->getLayout()->createBlock(
                    \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree::class,
                    'promo_widget_chooser_category_ids',
                    [
                        'data' => [
                            'js_form_object' => $request->getParam('form'),
                        ],
                    ]
                )->setCategoryIds($ids);
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->setAjaxContent($block->toHtml());
        } else {
            $this->setAjaxContent('', false);
        }

        return $this->getResult();
    }
}
