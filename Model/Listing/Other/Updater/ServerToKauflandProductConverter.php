<?php

namespace M2E\Kaufland\Model\Listing\Other\Updater;

class ServerToKauflandProductConverter
{
    private \M2E\Kaufland\Model\Account $account;
    private \M2E\Kaufland\Model\Storefront $storefront;

    public function __construct(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront
    ) {
        $this->account = $account;
        $this->storefront = $storefront;
    }

    public function convert(array $response): \M2E\Kaufland\Model\Listing\Other\KauflandProductCollection
    {
        $result = new \M2E\Kaufland\Model\Listing\Other\KauflandProductCollection();
        foreach ($response as $unmanagedItem) {
            if (!$this->isItemValid($unmanagedItem)) {
                continue;
            }
            $kauflandProduct = new \M2E\Kaufland\Model\Listing\Other\KauflandProduct(
                $this->account->getId(),
                $this->storefront->getId(),
                (int)$unmanagedItem['unit_id'],
                $unmanagedItem['offer_id'],
                $unmanagedItem['product_id'],
                \M2E\Kaufland\Model\Listing\Other\KauflandProduct::convertChannelStatusToExtension(
                    $unmanagedItem['status'],
                ),
                $unmanagedItem['title'],
                $unmanagedItem['eans'],
                $unmanagedItem['currency_code'],
                (float)$unmanagedItem['price'],
                (int)$unmanagedItem['qty'],
                $unmanagedItem['main_picture_url'],
                (int)$unmanagedItem['category_id'],
                $unmanagedItem['category_title'] ?? null,
                (bool)$unmanagedItem['fulfilled_by_merchant'],
                (int)$unmanagedItem['warehouse_id'],
                (int)$unmanagedItem['shipping_group_id'],
                (string)$unmanagedItem['condition'],
                (int)$unmanagedItem['handling_time'],
                $unmanagedItem['is_valid_product'] ?? true,
                $unmanagedItem['product_empty_attributes'] ?? [],
            );

            $result->add($kauflandProduct);
        }

        return $result;
    }

    public function isItemValid($unmanagedItem): bool
    {
        return $unmanagedItem['offer_id'] !== null;
    }
}
