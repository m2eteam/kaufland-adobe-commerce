<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Tag;

use M2E\Kaufland\Model\ResourceModel\Tag as TagResource;

class Entity extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\Kaufland\Model\ResourceModel\Tag::class);
    }

    public function getErrorCode(): string
    {
        return $this->getDataByKey(TagResource::COLUMN_ERROR_CODE);
    }

    public function setErrorCode(string $errorCode): void
    {
        $this->setData(TagResource::COLUMN_ERROR_CODE, $errorCode);
    }

    public function getText(): string
    {
        return $this->getDataByKey(TagResource::COLUMN_TEXT);
    }

    public function setText(string $text): void
    {
        $this->setData(TagResource::COLUMN_TEXT, $text);
    }

    public function getCreateDate(): \DateTime
    {
        if (empty($this->getData(TagResource::COLUMN_CREATE_DATE))) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                sprintf("Field '%s' must be set", TagResource::COLUMN_CREATE_DATE)
            );
        }

        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(TagResource::COLUMN_CREATE_DATE)
        );
    }

    public function setCreateDate(\DateTime $createDate): void
    {
        $timeZone = new \DateTimeZone(\M2E\Core\Helper\Date::getTimezone()->getDefaultTimezone());
        $createDate->setTimezone($timeZone);
        $this->setData(TagResource::COLUMN_CREATE_DATE, $createDate->format('Y-m-d H:i:s'));
    }
}
