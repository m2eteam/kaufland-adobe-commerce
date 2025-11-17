<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Magento\Product;

class Rule extends \M2E\Kaufland\Model\Magento\Product\Rule
{
    private \M2E\Kaufland\Model\Channel\Magento\Product\Rule\Condition\CombineFactory $ruleCombineFactory;

    public const NICK = 'kaufland_product_rule';

    /** @var string */
    protected string $nick = self::NICK;

    /**
     * @psalm-suppress UndefinedClass
     */
    public function __construct(
        \M2E\Kaufland\Model\Channel\Magento\Product\Rule\Condition\CombineFactory $ruleCombineFactory,
        \Magento\Framework\Data\Form $form,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \M2E\Kaufland\Model\Magento\Product\Rule\Condition\CombineFactory $ruleConditionCombineFactory,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $form,
            $productFactory,
            $resourceIterator,
            $ruleConditionCombineFactory,
            $modelFactory,
            $activeRecordFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->ruleCombineFactory = $ruleCombineFactory;
    }

    public function getConditionObj(): \M2E\Kaufland\Model\Channel\Magento\Product\Rule\Condition\Combine
    {
        return $this->ruleCombineFactory->create();
    }
}
