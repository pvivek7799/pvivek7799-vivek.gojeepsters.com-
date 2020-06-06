<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace MW\Onestepcheckout\Model\Data\Customer;

/**
 * Interface RegisterInterface
 * @package MW\Onestepcheckout\Model\Data\Customer
 */
interface RegisterInterface
{

    const EMAIL = "email";
    const FIRSTNAME = "firstname";
    const LASTNAME = "lastname";
    const PASSWORD = "password";
    const CONFIRMATION = "confirmation";
    const WANT_SUBSCRIBE = "want_subscribe";

    /**
     * @param string $email
     * @return mixed
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $firstname
     * @return mixed
     */
    public function setFirstname($firstname);

    /**
     * @return string
     */
    public function getFirstname();

    /**
     * @param string $lastname
     * @return mixed
     */
    public function setLastname($lastname);

    /**
     * @return string
     */
    public function getLastname();

    /**
     * @param string $password
     * @return mixed
     */
    public function setPassword($password);

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @param string $confirmation
     * @return mixed
     */
    public function setConfirmation($confirmation);

    /**
     * @return string
     */
    public function getConfirmation();

    /**
     * @param boolean $wantSubscribe
     * @return mixed
     */
    public function setWantSubscribe($wantSubscribe);

    /**
     * @return boolean
     */
    public function getWantSubscribe();
}
