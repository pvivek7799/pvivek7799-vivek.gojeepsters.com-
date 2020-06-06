<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionImportExport\Block\Adminhtml;

use MageWorx\OptionBase\Helper\System as SystemHelper;

class ImportExport extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'import_export.phtml';

    /**
     * @var SystemHelper
     */
    protected $systemHelper;

    /**
     * @param SystemHelper $systemHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        SystemHelper $systemHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->systemHelper = $systemHelper;
        parent::__construct($context, $data);
        $this->setUseContainer(true);
        $this->setMageOneStoreIds($this->_backendSession->getStoreIds());
        $this->setMageOneCustomerGroupIds($this->_backendSession->getCustomerGroupIds());
        $this->_backendSession->setStoreIds([]);
        $this->_backendSession->setCustomerGroupIds([]);
    }

    /**
     * @return array
     */
    public function getCustomerGroups()
    {
        return $this->systemHelper->getCustomerGroups();
    }

    /**
     * @return array
     */
    public function getStores()
    {
        return $this->systemHelper->getStores();
    }
}
