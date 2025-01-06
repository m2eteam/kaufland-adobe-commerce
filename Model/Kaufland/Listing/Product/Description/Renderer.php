<?php

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Description;

class Renderer
{
    private \M2E\Kaufland\Model\Product $listingProduct;

    public function __construct(
        \M2E\Kaufland\Model\Product $listingProduct
    ) {
        $this->listingProduct = $listingProduct;
    }

    public function parseTemplate(string $text): string
    {
        return $this->insertValues($text);
    }

    private function insertValues(string $text): string
    {
        preg_match_all("/#value\[(.+?)\]#/", $text, $matches);

        if (empty($matches[0])) {
            return $text;
        }

        $replaces = [];
        foreach ($matches[1] as $i => $attributeCode) {
            $method = 'get' . implode(array_map('ucfirst', explode('_', $attributeCode)));

            $arg = null;
            if (preg_match('/(?<=\[)(\d+?)(?=\])/', $method, $tempMatch)) {
                $arg = $tempMatch[0];
                $method = str_replace('[' . $arg . ']', '', $method);
            }

            $value = '';
            if (method_exists($this, $method)) {
                $value = $this->$method($arg);
            }

            if ($attributeCode === 'fixed_price') {
                $value = round((float)$value, 2);
            }

            if ($value !== '') {
                $replaces[$matches[0][$i]] = $value;
            }
        }

        return str_replace(array_keys($replaces), array_values($replaces), $text);
    }

    private function getQty(): int
    {
        return $this->listingProduct->getQty();
    }

    private function getFixedPrice(): string
    {
        $price = $this->listingProduct->getFixedPrice();
        if (empty($price)) {
            return 'N/A';
        }

        return sprintf('%01.2f', $price);
    }

    private function getTitle(): string
    {
        return $this->listingProduct->getDescriptionTemplateSource()->getTitle();
    }
}
