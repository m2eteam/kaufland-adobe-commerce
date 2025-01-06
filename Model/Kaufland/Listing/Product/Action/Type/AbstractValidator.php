<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type;

use M2E\Core\Model\Connector\Response\Message;
use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator;

abstract class AbstractValidator
{
    private array $params = [];
    private array $messages = [];
    private Configurator $configurator;
    private \M2E\Kaufland\Model\Product $listingProduct;
    private array $temporaryData = [];

    public function getTemporaryData(): array
    {
        return $this->temporaryData;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    // ---------------------------------------

    public function setConfigurator(Configurator $configurator): self
    {
        $this->configurator = $configurator;

        return $this;
    }

    protected function getConfigurator(): Configurator
    {
        return $this->configurator;
    }

    // ---------------------------------------

    public function setListingProduct(\M2E\Kaufland\Model\Product $listingProduct): self
    {
        $this->listingProduct = $listingProduct;

        return $this;
    }

    protected function getListingProduct(): \M2E\Kaufland\Model\Product
    {
        return $this->listingProduct;
    }

    // ----------------------------------------

    abstract public function validate(): bool;

    // ----------------------------------------

    protected function addMessage($message, $type = Message::TYPE_ERROR)
    {
        $this->messages[] = [
            'text' => $message,
            'type' => $type,
        ];
    }

    // ---------------------------------------

    public function getMessages(): array
    {
        return $this->messages;
    }

    // ----------------------------------------

    protected function getAccount(): \M2E\Kaufland\Model\Account
    {
        return $this->getListing()->getAccount();
    }

    // ---------------------------------------

    protected function getListing(): \M2E\Kaufland\Model\Listing
    {
        return $this->getListingProduct()->getListing();
    }

    // ---------------------------------------

    protected function getMagentoProduct(): \M2E\Kaufland\Model\Magento\Product
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    // ---------------------------------------

    protected function validatePrice(): bool
    {
        if (!$this->validateFixedPrice()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    protected function validateQty(): bool
    {
        if (!$this->getConfigurator()->isQtyAllowed()) {
            return true;
        }

        $qty = $this->getQty();
        $clearQty = $this->getClearQty();

        if ($clearQty > 0 && $qty <= 0) {
            $message = 'You’re submitting an item with QTY contradicting the QTY settings in your Selling Policy.
            Please check Minimum Quantity to Be Listed and Quantity Percentage options.';

            $this->addMessage($message);

            return false;
        }

        if ($qty <= 0) {
            if (
                isset($this->params['status_changer']) &&
                $this->params['status_changer'] == \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER
            ) {
                $message = 'You are submitting an Item with zero quantity. It contradicts Kaufland requirements.';

                if ($this->getListingProduct()->isStoppable()) {
                    $message .= ' Please apply the Stop Action instead.';
                }

                $this->addMessage($message);
            } else {
                $message = 'Cannot submit an Item with zero quantity. It contradicts Kaufland requirements.
                            This action has been generated automatically based on your Synchronization Rule settings. ';

                if ($this->getListingProduct()->isStoppable()) {
                    $message .= 'The error occurs when the Stop Rules are not properly configured or disabled. ';
                }

                $message .= 'Please review your settings.';

                $this->addMessage($message);
            }

            return false;
        }

        $this->temporaryData['qty'] = $qty;
        $this->temporaryData['clear_qty'] = $clearQty;

        return true;
    }

    // ---------------------------------------

    protected function validateFixedPrice(): bool
    {
        if (!$this->getConfigurator()->isPriceAllowed()) {
            return true;
        }

        $price = $this->getPrice();
        if ($price < 0.01) {
            $this->addMessage(
                'The Price must be greater than 0. Please, check the Selling Policy and Product Settings'
            );

            return false;
        }

        $this->temporaryData['price'] = $price;

        return true;
    }

    //########################################

    protected function getQty()
    {
        if (isset($this->temporaryData['qty'])) {
            return $this->temporaryData['qty'];
        }

        return $this->getListingProduct()->getQty();
    }

    protected function getClearQty()
    {
        if (isset($this->temporaryData['clear_qty'])) {
            return $this->temporaryData['clear_qty'];
        }

        return $this->getListingProduct()->getQty(true);
    }

    protected function getPrice()
    {
        if (isset($this->temporaryData['price'])) {
            return $this->temporaryData['price'];
        }

        return $this->getListingProduct()->getFixedPrice();
    }
}
