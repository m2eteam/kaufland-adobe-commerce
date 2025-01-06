<?php

namespace M2E\Kaufland\Block\Adminhtml\Magento\Grid\Column\Filter;

/**
 * Class \M2E\Kaufland\Block\Adminhtml\Magento\Grid\Column\Filter\AbstractFilter
 */
class AbstractFilter extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    use \M2E\Kaufland\Block\Adminhtml\Traits\BlockTrait;

    /** @var \M2E\Kaufland\Model\Factory */
    protected $modelFactory;

    public function __construct(
        \M2E\Kaufland\Model\Factory $modelFactory,
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        array $data = []
    ) {
        $this->modelFactory = $modelFactory;

        parent::__construct($context, $resourceHelper, $data);
    }
}
