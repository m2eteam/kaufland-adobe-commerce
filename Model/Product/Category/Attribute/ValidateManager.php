<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Category\Attribute;

class ValidateManager
{
    private \M2E\Kaufland\Model\Product\Repository $productRepository;
    private \M2E\Kaufland\Model\Product\Action\TagManager $tagManager;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Product\Action\TagManager $tagManager
    ) {
        $this->productRepository = $productRepository;
        $this->tagManager = $tagManager;
    }

    /**
     * @param \M2E\Kaufland\Model\Product $product
     * @param string[] $errors
     *
     * @return void
     */
    public function markProductAsNotValid(\M2E\Kaufland\Model\Product $product, array $errors): void
    {
        $product->markCategoryAttributesAsInvalid($errors);
        $this->productRepository->save($product);

        $messages[] = new \M2E\Kaufland\Model\Product\Action\Validator\ValidatorMessage(
            'The Item either is Listed, or not Listed yet or not available',
            \M2E\Kaufland\Model\Tag\ValidatorIssues::ERROR_CATEGORY_ATTRIBUTE_MISSING
        );
        $this->tagManager->addErrorTags($product, $messages);
        $this->tagManager->flush();
    }

    public function markProductAsValid(\M2E\Kaufland\Model\Product $product): void
    {
        $product->markCategoryAttributesAsValid();
        $this->productRepository->save($product);

        $this->tagManager->removeTagByCode(
            $product,
            \M2E\Kaufland\Model\Tag\ValidatorIssues::ERROR_CATEGORY_ATTRIBUTE_MISSING
        );
        $this->tagManager->flush();
    }
}
