<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Category;

class EditCategory extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractCategory
{
    /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\ViewFactory */
    private $viewFactory;
    /** @var \M2E\Kaufland\Model\Category\Dictionary\Manager */
    private $dictionaryManager;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\ViewFactory $viewFactory,
        \M2E\Kaufland\Model\Category\Dictionary\Manager $dictionaryManager
    ) {
        parent::__construct();

        $this->viewFactory = $viewFactory;
        $this->dictionaryManager = $dictionaryManager;
    }

    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        $storefrontId = $this->getRequest()->getParam('storefront_id');
        $dictionary = $this->dictionaryManager->getOrCreateDictionary((int)$storefrontId, $categoryId);

        $block = $this->viewFactory->create($this->getLayout(), $dictionary);
        $this->addContent($block);
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Edit Category'));

        return $this->getResult();
    }
}
