<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Select;

use Magento\Framework\Data\OptionSourceInterface;
use M2E\Kaufland\Model\Tag\Repository;
use M2E\Kaufland\Model\Tag;

class Errors implements OptionSourceInterface
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->repository->getAllTags() as $tag) {
            if ($tag->getErrorCode() === Tag::HAS_ERROR_ERROR_CODE) {
                continue;
            }

            $options[] = [
                'label' => substr($tag->getText(), 0, 40) . '...',
                'value' => $tag->getErrorCode(),
            ];
        }

        return $options;
    }
}
