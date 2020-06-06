<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.Landofcoder.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_AdvancedReports
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.Landofcoder.com/)
 * @license    http://www.Landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\AdvancedReports\Controller\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Lof\AdvancedReports\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Lof\AdvancedReports\Cron\ScheduledSendExports
     */
    protected $_crontab;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Enable/disable configuration
     */
    const XML_PATH_EMAIL_COPY_FOLDER = 'scheduled_email_settings/copy_folder';

    /**
     * @param \Magento\Framework\App\Action\Context      $context           
     * @param \Magento\Framework\App\ResourceConnection  $resource          
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager      
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory 
     * @param \Lof\AdvancedReports\Helper\Data                $dataHelper            
     * @param \Magento\Framework\Registry                $registry 
     * @param \Lof\AdvancedReports\Cron\ScheduledSendExports                $crontab              
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Lof\AdvancedReports\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Lof\AdvancedReports\Cron\ScheduledSendExports  $crontab   
    ) {
      parent::__construct($context);
        $this->resultPageFactory  = $resultPageFactory;
        $this->_coreRegistry      = $registry;
        $this->_resource          = $resource;
        $this->_dataHelper             = $dataHelper;
        $this->_storeManager      = $storeManager;
        $this->_crontab = $crontab;
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * index action
     */
    public function execute()
    {
       $filename = $this->getRequest()->getParam('f');
       $filename = base64_decode($filename);
       $copy_folder = $this->_dataHelper->getConfig(self::XML_PATH_EMAIL_COPY_FOLDER);
       $copy_folder = str_replace("/", DIRECTORY_SEPARATOR, $copy_folder);
       $copy_folder = DirectoryList::ROOT . DIRECTORY_SEPARATOR. $copy_folder;
       $filepath = $copy_folder.$filename;

        if ($filename) {
            try {
                $this->_prepareDownloadResponse($filename, array('type' => 'filename', 'value' => $filepath));
                //Track number download file at here. Store: filename, number, ip
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError($filepath . __(' not found'));
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
    }

}