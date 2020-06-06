<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace MW\Onestepcheckout\Model;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;

/**
 * Class CustomerService
 * @package MW\Onestepcheckout\Model
 */
class CustomerService implements \MW\Onestepcheckout\Api\CustomerServiceInterface
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * CustomerService constructor.
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param Session $customerSession
     * @param \Magento\Framework\Escaper $escaper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Escaper $escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->customerAccountManagement = $accountManagement;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->customerFactory = $customerFactory;
        $this->customerUrl = $customerUrl;
        $this->eventManager = $eventManager;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @param $username
     * @param $password
     * @return \MW\Onestepcheckout\Model\Data\Customer\ResponseInterface
     */
    public function login($username, $password)
    {
        $response = [
            'errors'  => false,
            'message' => __('Login successful.'),
        ];
        try {
            $customer = $this->customerAccountManagement->authenticate($username, $password);
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();
        } catch (EmailNotConfirmedException $e) {
            $response = [
                'errors'  => true,
                'message' => $e->getMessage(),
            ];
        } catch (InvalidEmailOrPasswordException $e) {
            $response = [
                'errors'  => true,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $response = [
                'errors'  => true,
                'message' => __('Invalid login or password.'),
            ];
        }
        return $response;
    }

    /**
     * @return \MW\Onestepcheckout\Api\CustomerServiceInterface
     */
    public function logout()
    {
        $this->customerSession->logout();
        return $this;
    }

    /**
     * @param $email
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    public function forgotPassword($email)
    {
        $result = [];
        $result['success'] = '';
        $result['errorMessage'] = '';
        $result['successMessage'] = '';

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        if ($email) {
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                $this->customerSession->setForgottenEmail($email);
                $result['success'] = 'false';
                $result['errorMessage'] = __('Please correct the email address.');
                return $result;
            } else {
                $customer = $this->customerFactory->create()
                    ->setWebsiteId($websiteId)
                    ->loadByEmail($email);
                if ($customer->getId()) {
                    try {
                        $this->customerAccountManagement->initiatePasswordReset(
                            $email,
                            AccountManagement::EMAIL_RESET
                        );
                    } catch (\Exception $exception) {
                        $result['success'] = 'false';
                        $result['errorMessage'] = __('We\'re unable to send the password reset email.');

                        return $result;
                    }
                    $result['success'] = 'true';
                    $result['successMessage'] = $this->getSuccessMessage($email);

                    return $result;
                } else {
                    $result = ['success' => false, 'errorMessage' => 'The account does not exist.'];

                    return $result;
                }
            }
        } else {
            $result['success'] = 'false';
            $result['errorMessage'] = __('Please enter your email');

            return $result;
        }
    }

    /**
     * @param $email
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($email)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($email)
        );
    }

    /**
     * @param \MW\Onestepcheckout\Model\Data\Customer\RegisterInterface $registerData
     * @return mixed
     */
    public function register($registerData)
    {
        try {
            $customer = $this->customerFactory->create();
            $this->checkPasswordConfirmation($registerData->getPassword(), $registerData->getConfirmation());
            $customer->setFirstname($registerData->getFirstname());
            $customer->setLastname($registerData->getLastname());
            $customer->setEmail($registerData->getEmail());

            $customer = $this->customerAccountManagement->createAccount($customer, $registerData->getPassword(), '');
            if ($registerData->getWantSubscribe()) {
                $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
            }
            $this->eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $confirmationStatus = $this->customerAccountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                $message = __(
                    'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                    $email
                );
                $result = ['success' => false, 'error' => $message];
            } else {
                $this->customerSession->setCustomerDataAsLoggedIn($customer);
                $result = ['success' => true];
            }
        } catch (\Exception $e) {
            $result = ['success' => false, 'error' => $e->getMessage()];
        }
        return $result;
    }

    /**
     * @param $password
     * @param $confirmation
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
            throw new \Magento\Framework\Exception\InputException(__('Please make sure your passwords match.'));
        }
    }
}
