<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Description;

class Diff extends \M2E\Kaufland\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return $this->isTitleDifferent()
            || $this->isDescriptionDifferent()
            || $this->isImagesDifferent();
    }

    public function isTitleDifferent(): bool
    {
        $keys = [
            \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_TITLE_MODE,
            \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_TITLE_TEMPLATE,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isDescriptionDifferent(): bool
    {
        $keys = [
            \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_DESCRIPTION_MODE,
            \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_DESCRIPTION_TEMPLATE,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isImagesDifferent(): bool
    {
        $keys = [
            \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_IMAGE_MAIN_MODE,
            \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_IMAGE_MAIN_ATTRIBUTE,

            \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_GALLERY_TYPE,
            \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_GALLERY_IMAGES_MODE,
            \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_GALLERY_IMAGES_ATTRIBUTE,
            \M2E\Kaufland\Model\ResourceModel\Template\Description::COLUMN_GALLERY_IMAGES_LIMIT,
        ];

        return $this->isSettingsDifferent($keys);
    }
}
