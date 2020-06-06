<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Model\ResourceModel\Login\Grid;

/**
 * LoginAsCustomer collection
 */
class Collection extends \Magefan\LoginAsCustomer\Model\ResourceModel\Login\Collection
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_map['fields']['email'] = 'c.email';
        $this->_map['fields']['created_at'] = 'main_table.created_at';
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                ['c' => $this->getTable('customer_entity')],
                'c.entity_id = main_table.customer_id',
                ['email']
            )->joinLeft(
                ['a' => $this->getTable('admin_user')],
                'a.user_id = main_table.admin_id',
                ['username']
            );
        return $this;
    }
}
