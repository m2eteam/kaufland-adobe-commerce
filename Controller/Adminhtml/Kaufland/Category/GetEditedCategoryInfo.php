<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Category;

class GetEditedCategoryInfo extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractCategory
{
    private \M2E\Kaufland\Model\Category\Dictionary\Manager $dictionaryManager;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary\Manager $dictionaryManager
    ) {
        parent::__construct();
        $this->dictionaryManager = $dictionaryManager;
    }

    public function execute()
    {
        $categoryId = (int)$this->getRequest()->getParam('category_id');
        $storefrontId = (int)$this->getRequest()->getParam('storefront_id');

        if (
            empty($categoryId)
            || empty($storefrontId)
        ) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Invalid input');
        }

        try {
            $dictionary = $this->dictionaryManager->getOrCreateDictionary($storefrontId, $categoryId);
        } catch (\Throwable $e) {
            $this->setJsonContent([
                'success' => false,
                'message' => $e->getMessage()
            ]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'success' => true,
            'dictionary_id' => $dictionary->getId(),
            'is_all_required_attributes_filled' => $dictionary->isAllRequiredAttributesFilled(),
            'path' => $dictionary->getPath(),
            'value' => $dictionary->getCategoryId(),
        ]);

        return $this->getResult();
    }
}
