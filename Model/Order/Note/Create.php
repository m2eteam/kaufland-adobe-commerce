<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order\Note;

use M2E\Kaufland\Model\Order\Note\MagentoOrderUpdateTrait;

class Create
{
    use MagentoOrderUpdateTrait;

    private \M2E\Kaufland\Model\Order\Note\Repository $repository;
    private \M2E\Kaufland\Model\Order\NoteFactory $noteFactory;

    public function __construct(
        \M2E\Kaufland\Model\Order\Note\Repository $repository,
        \M2E\Kaufland\Model\Order\NoteFactory $noteFactory,
        \M2E\Kaufland\Model\Magento\Order\Updater $magentoOrderUpdater
    ) {
        $this->repository = $repository;
        $this->noteFactory = $noteFactory;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
    }

    public function process(\M2E\Kaufland\Model\Order $order, string $note): \M2E\Kaufland\Model\Order\Note
    {
        $obj = $this->noteFactory->create();
        $obj->init($order->getId(), $note);

        $this->repository->create($obj);

        $comment = (string)__(
            'Custom Note was added to the corresponding %channel_title order: %note.',
            [
                'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                'note' => $obj->getNote()
            ],
        );
        $this->updateMagentoOrderComment($order, $comment);

        return $obj;
    }
}
