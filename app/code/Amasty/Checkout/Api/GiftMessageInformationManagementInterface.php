<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Api;

interface GiftMessageInformationManagementInterface
{
    /**
     * @param int $cartId
     * @param mixed $giftMessages
     *
     * @return bool
     */
    public function update($cartId, $giftMessages);
}
