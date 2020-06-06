<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace MW\Onestepcheckout\Model\Data\Customer;

/**
 * Class Response
 * @package MW\Onestepcheckout\Model\Data\Customer
 */
class Response extends \Magento\Framework\Model\AbstractExtensibleModel implements \MW\Onestepcheckout\Model\Data\Customer\ResponseInterface
{
    /**
     * @param boolean $errors
     * @return mixed
     */
    public function setErrors($errors)
    {
        return $this->setData(self::ERRORS, $errors);
    }

    /**
     * @return boolean
     */
    public function getErrors()
    {
        return $this->getData(self::ERRORS);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }
}
