<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace MW\Onestepcheckout\Model\Data\Customer;

/**
 * Class Register
 * @package MW\Onestepcheckout\Model\Data\Customer
 */
class Register extends \Magento\Framework\Model\AbstractExtensibleModel implements \MW\Onestepcheckout\Model\Data\Customer\RegisterInterface
{
    /**
     * @param string $email
     * @return mixed
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @param string $firstname
     * @return mixed
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->getData(self::FIRSTNAME);
    }

    /**
     * @param string $lastname
     * @return mixed
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->getData(self::LASTNAME);
    }

    /**
     * @param string $password
     * @return mixed
     */
    public function setPassword($password)
    {
        return $this->setData(self::PASSWORD, $password);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getData(self::PASSWORD);
    }

    /**
     * @param string $confirmation
     * @return mixed
     */
    public function setConfirmation($confirmation)
    {
        return $this->setData(self::CONFIRMATION, $confirmation);
    }

    /**
     * @return string
     */
    public function getConfirmation()
    {
        return $this->getData(self::CONFIRMATION);
    }

    /**
     * @param boolean $wantSubscribe
     * @return mixed
     */
    public function setWantSubscribe($wantSubscribe)
    {
        return $this->setData(self::WANT_SUBSCRIBE, $wantSubscribe);
    }

    /**
     * @return boolean
     */
    public function getWantSubscribe()
    {
        return $this->getData(self::WANT_SUBSCRIBE);
    }
}
