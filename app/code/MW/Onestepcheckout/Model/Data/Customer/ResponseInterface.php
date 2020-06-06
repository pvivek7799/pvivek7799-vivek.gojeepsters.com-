<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace MW\Onestepcheckout\Model\Data\Customer;

/**
 * Interface ResponseInterface
 * @package MW\Onestepcheckout\Model\Data\Customer
 */
interface ResponseInterface
{
    const ERRORS = "errors";
    const MESSAGE = "message";

    /**
     * @param boolean $errors
     * @return mixed
     */
    public function setErrors($errors);

    /**
     * @return boolean
     */
    public function getErrors();

    /**
     * @param string $message
     * @return mixed
     */
    public function setMessage($message);

    /**
     * @return string
     */
    public function getMessage();
}
