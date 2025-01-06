<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Settings;

class Sku
{
    public const SKU_MODE_PRODUCT_ID = 3;
    public const SKU_MODE_DEFAULT = 1;
    public const SKU_MODE_CUSTOM_ATTRIBUTE = 2;

    public const SKU_MODIFICATION_MODE_NONE = 0;
    public const SKU_MODIFICATION_MODE_PREFIX = 1;
    public const SKU_MODIFICATION_MODE_POSTFIX = 2;

    public const GENERATE_SKU_MODE_NO = 0;
    public const GENERATE_SKU_MODE_YES = 1;

    private array $settings = [
        'sku_mode' => self::SKU_MODE_DEFAULT,
        'sku_custom_attribute' => '',
        'sku_modification_mode' => self::SKU_MODIFICATION_MODE_NONE,
        'sku_modification_custom_value' => '',
        'generate_sku_mode' => self::GENERATE_SKU_MODE_NO,
    ];

    public function createWithSkuMode(int $skuMode): self
    {
        $new = clone $this;
        $new->settings['sku_mode'] = $skuMode;

        return $new;
    }

    public function createWithSkuCustomAttribute(string $customAttribute): self
    {
        $new = clone $this;
        $new->settings['sku_custom_attribute'] = $customAttribute;

        return $new;
    }

    public function createWithSkuModificationMode(int $modificationMode): self
    {
        $new = clone $this;
        $new->settings['sku_modification_mode'] = $modificationMode;

        return $new;
    }

    public function createWithSkuModificationCustomValue(string $skuModificationCustomValue): self
    {
        $new = clone $this;
        $new->settings['sku_modification_custom_value'] = $skuModificationCustomValue;

        return $new;
    }

    public function createWithGenerateSkuMode(int $generateSkuMode): self
    {
        $new = clone $this;
        $new->settings['generate_sku_mode'] = $generateSkuMode;

        return $new;
    }

    // ---------------------------------------

    public function getGenerateSkuMode(): int
    {
        return (int)$this->settings['generate_sku_mode'];
    }

    public function isGenerateSkuModeYes(): bool
    {
        return $this->getGenerateSkuMode() == self::GENERATE_SKU_MODE_YES;
    }

    public function getSkuCustomAttribute(): string
    {
        return $this->settings['sku_custom_attribute'];
    }

    public function getSkuMode(): int
    {
        return (int)$this->settings['sku_mode'];
    }

    public function isSkuProductIdMode(): bool
    {
        return $this->getSkuMode() == self::SKU_MODE_PRODUCT_ID;
    }

    public function isSkuDefaultMode(): bool
    {
        return $this->getSkuMode() == self::SKU_MODE_DEFAULT;
    }

    public function isSkuAttributeMode(): bool
    {
        return $this->getSkuMode() == self::SKU_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getSkuModificationMode(): int
    {
        return (int)$this->settings['sku_modification_mode'];
    }

    public function getSkuModificationCustomValue(): string
    {
        return $this->settings['sku_modification_custom_value'];
    }

    public function isSkuModificationModeNone(): bool
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_NONE;
    }

    public function isSkuModificationModePrefix(): bool
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_PREFIX;
    }

    public function isSkuModificationModePostfix(): bool
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_POSTFIX;
    }

    // ---------------------------------------

    public static function isEqual(Sku $firstSkuSettings, Sku $secondSkuSettings): bool
    {

        if ($firstSkuSettings->getGenerateSkuMode() != $secondSkuSettings->getGenerateSkuMode()) {
            return false;
        }

        if ($firstSkuSettings->getSkuMode() != $secondSkuSettings->getSkuMode()) {
            return false;
        }

        if ($firstSkuSettings->getSkuCustomAttribute() != $secondSkuSettings->getSkuCustomAttribute()) {
            return false;
        }

        if ($firstSkuSettings->getSkuModificationCustomValue() != $secondSkuSettings->getSkuModificationCustomValue()) {
            return false;
        }

        if ($firstSkuSettings->getSkuModificationMode() != $secondSkuSettings->getSkuModificationMode()) {
            return false;
        }

        return true;
    }
}
