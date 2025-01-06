<?php

namespace M2E\Kaufland\Model\Template\Synchronization;

class ChangeProcessor extends \M2E\Kaufland\Model\Template\Synchronization\ChangeProcessorAbstract
{
    public const INSTRUCTION_TYPE_REVISE_QTY_ENABLED = 'template_synchronization_revise_qty_enabled';
    public const INSTRUCTION_TYPE_REVISE_QTY_DISABLED = 'template_synchronization_revise_qty_disabled';
    public const INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED = 'template_synchronization_revise_qty_settings_changed';

    public const INSTRUCTION_TYPE_REVISE_PRICE_ENABLED = 'template_synchronization_revise_price_enabled';
    public const INSTRUCTION_TYPE_REVISE_PRICE_DISABLED = 'template_synchronization_revise_price_disabled';
    public const INSTRUCTION_TYPE_REVISE_TITLE_ENABLED = 'template_synchronization_revise_title_enabled';
    public const INSTRUCTION_TYPE_REVISE_TITLE_DISABLED = 'template_synchronization_revise_title_disabled';

    public const INSTRUCTION_TYPE_REVISE_DESCRIPTION_ENABLED = 'template_synchronization_revise_description_enabled';
    public const INSTRUCTION_TYPE_REVISE_DESCRIPTION_DISABLED = 'template_synchronization_revise_description_disabled';

    public const INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED = 'template_synchronization_revise_images_enabled';
    public const INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED = 'template_synchronization_revise_images_disabled';

    public const INSTRUCTION_TYPE_REVISE_CATEGORIES_ENABLED = 'template_synchronization_revise_categories_enabled';
    public const INSTRUCTION_TYPE_REVISE_CATEGORIES_DISABLED = 'template_synchronization_revise_categories_disabled';

    protected function getInstructionsData(
        \M2E\Kaufland\Model\ActiveRecord\Diff $diff,
        int $status
    ): array {
        /** @var \M2E\Kaufland\Model\Template\Synchronization\Diff $diff */
        $data = parent::getInstructionsData($diff, $status);

        if ($diff->isReviseQtyEnabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_QTY_ENABLED,
                'priority' => $status === \M2E\Kaufland\Model\Product::STATUS_LISTED ? 80 : 5,
            ];
        } elseif ($diff->isReviseQtyDisabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_QTY_DISABLED,
                'priority' => 5,
            ];
        } elseif ($diff->isReviseQtySettingsChanged()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED,
                'priority' => $status === \M2E\Kaufland\Model\Product::STATUS_LISTED ? 80 : 5,
            ];
        }

        if ($diff->isRevisePriceEnabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_PRICE_ENABLED,
                'priority' => $status === \M2E\Kaufland\Model\Product::STATUS_LISTED ? 60 : 5,
            ];
        } elseif ($diff->isRevisePriceDisabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_PRICE_DISABLED,
                'priority' => 5,
            ];
        }

        if ($diff->isReviseTitleEnabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_TITLE_ENABLED,
                'priority' => $status === \M2E\Kaufland\Model\Product::STATUS_LISTED ? 30 : 5,
            ];
        } elseif ($diff->isReviseTitleDisabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_TITLE_DISABLED,
                'priority' => 5,
            ];
        }

        if ($diff->isReviseDescriptionEnabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_DESCRIPTION_ENABLED,
                'priority' => $status === \M2E\Kaufland\Model\Product::STATUS_LISTED ? 30 : 5,
            ];
        } elseif ($diff->isReviseDescriptionDisabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_DESCRIPTION_DISABLED,
                'priority' => 5,
            ];
        }

        //----------------------------------------

        if ($diff->isReviseImagesEnabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED,
                'priority' => $status === \M2E\Kaufland\Model\Product::STATUS_LISTED ? 30 : 5,
            ];
        } elseif ($diff->isReviseImagesDisabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED,
                'priority' => 5,
            ];
        }

        //----------------------------------------

        if ($diff->isReviseCategoriesEnabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_CATEGORIES_ENABLED,
                'priority' => $status === \M2E\Kaufland\Model\Product::STATUS_LISTED ? 30 : 5,
            ];
        } elseif ($diff->isReviseCategoriesDisabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_REVISE_CATEGORIES_DISABLED,
                'priority' => 5,
            ];
        }

        return $data;
    }
}
