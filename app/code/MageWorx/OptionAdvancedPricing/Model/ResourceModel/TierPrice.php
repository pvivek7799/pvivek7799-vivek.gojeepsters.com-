<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionAdvancedPricing\Model\ResourceModel;

use MageWorx\OptionAdvancedPricing\Model\TierPrice as TierPriceModel;

class TierPrice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            TierPriceModel::TABLE_NAME,
            TierPriceModel::COLUMN_OPTION_TYPE_TIER_PRICE_ID
        );
    }
}
