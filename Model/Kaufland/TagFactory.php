<?php

namespace M2E\Kaufland\Model\Kaufland;

class TagFactory
{
    /** @var \M2E\Kaufland\Model\TagFactory */
    private $tagFactory;

    public function __construct(\M2E\Kaufland\Model\TagFactory $tagFactory)
    {
        $this->tagFactory = $tagFactory;
    }

    public function createByErrorCode(string $errorCode, string $text): \M2E\Kaufland\Model\Tag
    {
        $text = $this->getPreparedText($errorCode) ?? $this->trimText($text);

        return $this->tagFactory->create($errorCode, $text);
    }

    private function getPreparedText(string $errorCode): ?string
    {
        return null;
    }

    private function trimText(string $text): string
    {
        if (strlen($text) <= 255) {
            return $text;
        }

        return substr($text, 0, 252) . '...';
    }

    public function createWithHasErrorCode(): \M2E\Kaufland\Model\Tag
    {
        return $this->tagFactory->create(\M2E\Kaufland\Model\Tag::HAS_ERROR_ERROR_CODE, 'Has error');
    }
}
