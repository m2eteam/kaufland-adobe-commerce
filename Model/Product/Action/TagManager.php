<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action;

class TagManager
{
    private \M2E\Kaufland\Model\TagFactory $tagFactory;
    private \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Kaufland\Model\Tag\ValidatorIssues $validatorIssues;

    public function __construct(
        \M2E\Kaufland\Model\TagFactory $tagFactory,
        \M2E\Kaufland\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Kaufland\Model\Tag\ValidatorIssues $validatorIssues
    ) {
        $this->tagFactory = $tagFactory;
        $this->tagBuffer = $tagBuffer;
        $this->validatorIssues = $validatorIssues;
    }

    /**
     * @param \M2E\Kaufland\Model\Product $product
     * @param \M2E\Kaufland\Model\Product\Action\Validator\ValidatorMessage[] $messages
     */
    public function addErrorTags(\M2E\Kaufland\Model\Product $product, array $messages): void
    {
        if (empty($messages)) {
            return;
        }

        $tags = [];

        $userErrors = array_filter($messages, function ($message) {
            return $message->getCode() !== \M2E\Kaufland\Model\Tag\ValidatorIssues::NOT_USER_ERROR;
        });

        if (!empty($userErrors)) {
            $tags[] = $this->tagFactory->createWithHasErrorCode();

            foreach ($userErrors as $userError) {
                $error = $this->validatorIssues->mapByCode($userError->getCode());
                if ($error === null) {
                    continue;
                }

                $tags[] = $this->tagFactory->createByErrorCode(
                    $error->getCode(),
                    $error->getText()
                );
            }

            $this->tagBuffer->addTags($product, $tags);
        }
    }

    public function removeTagByCode(\M2E\Kaufland\Model\Product $listingProduct, string $code): void
    {
        $this->tagBuffer->removeTagByCode($listingProduct, $code);
    }

    public function flush(): void
    {
        $this->tagBuffer->flush();
    }
}
