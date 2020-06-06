<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\ResourceModel\Log;

/**
 * Class ProductView
 * @package Aheadworks\AdvancedReports\Model\ResourceModel\Log
 */
class ProductView extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_arep_log_product_view', 'id');
    }

    /**
     * Check if a visitor has already viewed specified product
     *
     * @param int $productId
     * @param int $visitorId
     * @return bool
     */
    public function isExist($productId, $visitorId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where("{$this->getMainTable()}.product_id IN (?)", $productId)
            ->where("{$this->getMainTable()}.visitor_id IN (?)", $visitorId);

        if ($this->getConnection()->fetchRow($select)) {
            return true;
        }
        return false;
    }
}
