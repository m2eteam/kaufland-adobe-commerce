<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Category;

class Diff extends \M2E\Kaufland\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return $this->isCategoriesDifferent();
    }

    private function isCategoriesDifferent(): bool
    {
        $keys = [
            'attributes',
        ];

        return $this->isSettingsDifferent($keys);
    }
}
