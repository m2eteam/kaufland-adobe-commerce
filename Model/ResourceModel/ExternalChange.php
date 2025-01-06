<?php

namespace M2E\Kaufland\Model\ResourceModel;

class ExternalChange extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_STOREFRONT_ID = 'storefront_id';
    public const COLUMN_OFFER_ID = 'offer_id';
    public const COLUMN_UNIT_ID = 'unit_id';

    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct()
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_EXTERNAL_CHANGE, self::COLUMN_ID);
    }
}
