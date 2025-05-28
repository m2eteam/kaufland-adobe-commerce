<?php

namespace M2E\Kaufland\Model;

class TagFactory
{
    public function create(string $errorCode, string $text): Tag
    {
        return new Tag($errorCode, $text);
    }

    public function createWithHasErrorCode(): Tag
    {
        return $this->create(Tag::HAS_ERROR_ERROR_CODE, 'Has error');
    }

    public function createByErrorCode(string $errorCode, string $text): \M2E\Kaufland\Model\Tag
    {
        $text = $this->getPreparedText($errorCode) ?? $this->trimText($text);

        return $this->create($errorCode, $text);
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
}
