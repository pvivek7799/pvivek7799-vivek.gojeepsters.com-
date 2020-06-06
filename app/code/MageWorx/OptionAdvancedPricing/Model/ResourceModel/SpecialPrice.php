<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Model\ResourceModel;

use MageWorx\OptionAdvancedPricing\Model\SpecialPrice as SpecialPriceModel;

class SpecialPrice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            SpecialPriceModel::TABLE_NAME,
            SpecialPriceModel::COLUMN_OPTION_TYPE_SPECIAL_PRICE_ID
        );
    }
}
