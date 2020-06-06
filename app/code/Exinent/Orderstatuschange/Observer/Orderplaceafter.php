<?php
namespace Exinent\Orderstatuschange\Observer;

use Magento\Framework\Event\ObserverInterface;

class Orderplaceafter implements ObserverInterface
{

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $_invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $_invoiceRepository;

    /**
    * @var \Magento\Sales\Model\Service\InvoiceService
    */
    protected $_invoiceService;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    /**
    * @var \Magento\Sales\Api\OrderRepositoryInterface
    */
    protected $_orderRepository;

    /**
    * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
    * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
    * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
    * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
    * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
        ) {
          $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
          $this->_invoiceService = $invoiceService;
          $this->_transactionFactory = $transactionFactory;
          $this->_invoiceRepository = $invoiceRepository;
          $this->_orderRepository = $orderRepository;
    }




    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $orderId = $observer->getEvent()->getOrder()->getId();
        $this->createInvoice($orderId);      

    }

    protected function createInvoice($orderId)
    {
        try 
        {
			$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/invoice.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info('observer Executed');
            $order = $this->_orderRepository->get($orderId);
            if ($order)
            {
                $invoices = $this->_invoiceCollectionFactory->create()
                  ->addAttributeToFilter('order_id', array('eq' => $order->getId()));

                $invoices->getSelect()->limit(1);

                if ((int)$invoices->count() !== 0) {
                  $invoices = $invoices->getFirstItem();
                  $invoice = $this->_invoiceRepository->get($invoices->getId());
                  return $invoice;
                }

                if(!$order->canInvoice()) {
                    return null;
                }

                $invoice = $this->_invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $order->addStatusHistoryComment(__('Automatically INVOICED'), false);
                $transactionSave = $this->_transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();

                return $invoice;
            }
        } catch (\Exception $e) {
			$logger->info('observer Executed Error:'.$e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/invoice.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info('orderObserver:'.print_r($order->debug(),true));
    }
}