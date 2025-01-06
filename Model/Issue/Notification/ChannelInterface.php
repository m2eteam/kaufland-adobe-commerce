<?php

namespace M2E\Kaufland\Model\Issue\Notification;

use M2E\Kaufland\Model\Issue\DataObject;

interface ChannelInterface
{
    /**
     * @param DataObject $message
     *
     * @return void
     */
    public function addMessage(DataObject $message): void;
}
