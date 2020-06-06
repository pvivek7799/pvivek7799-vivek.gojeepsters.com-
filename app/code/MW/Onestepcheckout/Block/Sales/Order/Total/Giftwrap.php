<?php
/**
 * Mage-World
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Onestepcheckout\Block\Sales\Order\Total;

class Giftwrap extends \Magento\Tax\Block\Sales\Order\Tax
{
    /**
     * Initialize all order totals relates with tax
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        parent::initTotals();
        $parent = $this->getParentBlock();

        $title = "Giftwrap Fee";
        if ($this->getOrder()->getGiftwrapAmount() > 0) {
            $giftwrapAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'giftwrap',
                    'field' => 'giftwrap_amount',
                    'value' => $this->getOrder()->getGiftwrapAmount(),
                    'label' => __($title)
                ]
            );
            $parent->addTotal($giftwrapAmount, 'shipping');
        }

        return $this;
    }
}
