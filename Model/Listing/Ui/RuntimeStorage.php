<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Ui;

class RuntimeStorage
{
    private \M2E\Kaufland\Model\Listing $listing;

    public function hasListing(): bool
    {
        return isset($this->listing);
    }

    public function setListing(\M2E\Kaufland\Model\Listing $listing): void
    {
        $this->listing = $listing;
    }

    public function getListing(): \M2E\Kaufland\Model\Listing
    {
        return $this->listing;
    }
}
