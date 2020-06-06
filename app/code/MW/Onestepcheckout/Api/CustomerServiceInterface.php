<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace MW\Onestepcheckout\Api;

/**
 * Interface CustomerServiceInterface
 * @package MW\Onestepcheckout\Api
 */
interface CustomerServiceInterface
{
    /**
     * @param string $username
     * @param string $password
     * @return \MW\Onestepcheckout\Model\Data\Customer\ResponseInterface
     */
    public function login($username, $password);

    /**
     * @return \MW\Onestepcheckout\Api\CustomerServiceInterface
     */
    public function logout();

    /**
     * @param \MW\Onestepcheckout\Model\Data\Customer\RegisterInterface $registerData
     * @return mixed
     */
    public function register($registerData);
}
