<?php
/**
 * Created by chandra..
 * User: maiuoc
 * Date: 2019-01-19
 * Time: 1:16 PM
 */
namespace Amasty\Finder\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class DellPost
 * @package Magebay\Hello\Controller\Index
 */
class AddToCart extends \Magento\Framework\App\Action\Action
{
    /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
    protected $_resultJsonFactory;
    protected $formKey;
    protected $cart;
    protected $product;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product
    )
    {
        parent::__construct($context);
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
    }
    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $response = array(
            'status'=>'error',
            'message'=>'Error, Please try again'
        );
        $params = $this->getRequest()->getParams();
        $productId = isset($params['product']) ? (int)$params['product'] : 0;
        if($productId > 0)
        {
            try {
                $params['form_key'] = $this->formKey->getFormKey();
                $_product = $this->product->load($productId);
				$productName = $_product->getName();
				$productUrl = $_product->getProductUrl();
                $this->cart->addProduct($_product, $params);
                $this->cart->save();
				$response['status'] = 'success';
				$response['productname'] = $productName;
				$response['producturl'] = $productUrl;
			}
            catch(\Exception $e) {
                $response['message'] = $e->getMessage();
            }
        }

        return $resultJson->setData($response);
    }
}