<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace MW\Onestepcheckout\Model\Klarna\Checkout\Orderline;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Tax\Model\Calculation;

if (class_exists('Klarna\Core\Model\Checkout\Orderline\AbstractLine')) {
    /**
     * Generate Giftwrap order line details
     */
    class Giftwrap extends \Klarna\Core\Model\Checkout\Orderline\AbstractLine
    {
        /**
         * @var \Magento\Checkout\Model\Session
         */
        protected $_checkoutSession;

        /**
         * Giftwrap constructor.
         * @param \Klarna\Core\Helper\DataConverter $helper
         * @param Calculation $calculator
         * @param ScopeConfigInterface $config
         * @param DataObjectFactory $dataObjectFactory
         * @param \Magento\Checkout\Model\Session $checkoutSession
         * @param \Klarna\Core\Helper\KlarnaConfig $klarnaConfig
         */
        public function __construct(
            \Klarna\Core\Helper\DataConverter $helper,
            Calculation $calculator,
            ScopeConfigInterface $config,
            DataObjectFactory $dataObjectFactory,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Klarna\Core\Helper\KlarnaConfig $klarnaConfig
        ) {
            $this->_checkoutSession = $checkoutSession;
            parent::__construct($helper, $calculator, $config, $dataObjectFactory, $klarnaConfig);
        }

        /**
         * Checkout item types
         */
        const ITEM_TYPE_GIFTWRAP = 'physical';

        /**
         * Collect totals process.
         *
         * @param \Klarna\Core\Api\BuilderInterface  $checkout
         *
         * @return $this
         * @throws \Klarna\Core\Exception
         */
        public function collect(\Klarna\Core\Api\BuilderInterface $checkout)
        {
            return $this;
        }

        /**
         * Add order details to checkout request
         *
         * @param \Klarna\Core\Api\BuilderInterface  $checkout
         *
         * @return $this
         */
        public function fetch(\Klarna\Core\Api\BuilderInterface  $checkout)
        {
            $giftwrapAmount = $this->_checkoutSession->getData('onestepcheckout_giftwrap_amount');
            if ($giftwrapAmount) {
                $checkout->addOrderLine(
                    [
                        'type'             => self::ITEM_TYPE_GIFTWRAP,
                        'reference'        => "OSC Giftwrap",
                        'name'             => 'Giftwrap Fee',
                        'unit_price'       => round($giftwrapAmount*100),
                        'quantity'         => 1,
                        'total_amount'     => round($giftwrapAmount*100),
                        'tax_rate'         => 0,
                        'total_tax_amount' => 0
                    ]
                );
            }

            return $this;
        }
    }
} else {
    require_once(__DIR__ . '/giftwrapclass.php');
}
