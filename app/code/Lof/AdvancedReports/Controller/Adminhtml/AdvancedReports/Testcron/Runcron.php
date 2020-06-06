<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_AdvancedReports
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\AdvancedReports\Controller\Adminhtml\AdvancedReports\Testcron;

// use Magento\Reports\Model\Flag;

class Runcron extends \Lof\AdvancedReports\Controller\Adminhtml\AdvancedReports\Testcron
{
     /**
     * @var \Lof\AdvancedReports\Cron\ScheduledSendExports
     */
    protected $_crontab;

    /**
     * @param \Magento\Backend\App\Action\Context     $context           
     * @param \Lof\AdvancedReports\Cron\ScheduledSendExports                $crontab              
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lof\AdvancedReports\Cron\ScheduledSendExports  $crontab   
    ) {
        parent::__construct($context);
        $this->_crontab = $crontab;
    }
 
    /**
     * Shipping report action
     *
     * @return void
     */
    public function execute()
    {    
        $this->_initAction()->_setActiveMenu(
            'Lof_AdvancedReports::testcronrun'
        )->_addBreadcrumb(
            __('Force Run Cron Job'),
            __('Force Run Cron Job')
        );
        
        //Mage_Cron_Model_Schedule $schedule
        $resultRedirect = $this->resultRedirectFactory->create();
        $observer = $this->_crontab->execute();

        // display error message
        $this->messageManager->addSuccess(__('The advanced report cron job was processed.'));
        // go to grid
        
        return $resultRedirect->setPath('*/*/');
    }
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        $this->initVerification();
        if (!$this->getData('is_valid') && !$this->getData('local_valid')) {
            return false;
        }
        return $this->_authorization->isAllowed('Lof_AdvancedReports::testcronrun');
    }
    

}
