<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Plugin\Checkout\Controller\Cart;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Image
 * @package MW\Onestepcheckout\Plugin
 */
class Add extends \Magento\Checkout\Controller\Cart\Add
{

    /**
     * @param \Magento\Checkout\Controller\Cart\Add $subject
     * @param \Closure $proceed
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(\Magento\Checkout\Controller\Cart\Add $subject, \Closure $proceed)
    {
        if ($this->_canRedirectCheckoutAfterAddProduct()) {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }

            $params = $this->getRequest()->getParams();
            try {
                if (isset($params['qty'])) {
                    $filter = new \Zend_Filter_LocalizedToNormalized(
                        ['locale' => $this->_objectManager->get(\Magento\Framework\Locale\ResolverInterface::class)->getLocale()]
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initProduct();
                $related = $this->getRequest()->getParam('related_product');

                /**
                 * Check product availability
                 */
                if (!$product) {
                    return $this->goBack();
                }

                $this->cart->addProduct($product, $params);
                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }

                $this->cart->save();

                $this->_eventManager->dispatch(
                    'checkout_cart_add_product_complete',
                    ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );

                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if ($this->_canRedirectCheckoutAfterAddProduct()) {
                        if ($this->getRequest()->isAjax()) {
                            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                                'backUrl' => $this->_url->getUrl('checkout', array('_secure' => true)),
                            ]);
                        }

                        return $this->resultRedirectFactory->create()->setPath('checkout');
                    }
                    return $this->goBack(null, $product);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_checkoutSession->getUseNotice(true)) {
                    $this->messageManager->addNoticeMessage(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                    );
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $this->messageManager->addErrorMessage(
                            $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                        );
                    }
                }

                $url = $this->_checkoutSession->getRedirectUrl(true);

                if (!$url) {
                    $cartUrl = $this->_objectManager->get(\Magento\Checkout\Helper\Cart::class)->getCartUrl();
                    $url = $this->_redirect->getRedirectUrl($cartUrl);
                }

                return $this->goBack($url);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t add this item to your shopping cart right now.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                return $this->goBack();
            }
        } else {
            $result = $proceed();
            return $result;
        }
    }

    /**
     * @return bool
     */
    protected function _canRedirectCheckoutAfterAddProduct()
    {
        return $this->_objectManager->get(\MW\Onestepcheckout\Helper\Config::class)->isEnabledOneStep() &&
            $this->_objectManager->get(\MW\Onestepcheckout\Helper\Config::class)->allowRedirectCheckoutAfterAddProduct();
    }
}
