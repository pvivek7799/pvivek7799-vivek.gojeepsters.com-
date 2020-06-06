<?php
namespace Rootways\Chase\Block;

class Info extends \Magento\Payment\Block\Info
{
    protected $_isCheckoutProgressBlockFlag = true;
    protected $customHelper;
    protected $_paymentConfig;
    protected $paymentModel;
    protected $storeManager;
    
    protected $_avsCode = array( '1' => 'No address supplied', '2' => 'Bill-to address did not pass Auth Host edit checks',
                                '3' => 'AVS not performed', '4' => 'Issuer does not participate in AVS',
                                '5' => 'Edit-error - AVS data is invalid', '6' => 'System unavailable or time-out', '7' => 'Address information unavailable',
                                '8' => 'Transaction Ineligible for AVS', '9' => 'Zip Match/Zip4 Match/Locale match',
                                'A' => 'Zip Match/Zip 4 Match/Locale no match', 'B' => 'Zip Match/Zip 4 no Match/Locale match',
                                'C' => 'Zip Match/Zip 4 no Match/Locale no match', 'D' => 'Zip No Match/Zip 4 Match/Locale match',
                                'E' => 'Zip No Match/Zip 4 Match/Locale no match', 'F' => 'Zip No Match/Zip 4 No Match/Locale match',
                                'G' => 'No match at all', 'H' => 'Zip Match/Locale match', 'J' => 'Issuer does not participate in Global AVS',
                                'JA' => 'International street address and postal match',
                                'JB' => 'International street address match. Postal code not verified',
                                'JC' => 'International street address and postal code not verified.',
                                'JD' => 'International postal code match. Street address not verified.',
                                'M2' => 'Cardholder name, billing address, and postal code matches',
                                'M3' => 'Cardholder name and billing code matches', 'M4' => 'Cardholder name and billing address match',
                                'M5' => 'Cardholder name incorrect, billing address and postal code match',
                                'M6' => 'Cardholder name incorrect, billing postal code matches',
                                'M7' => 'Cardholder name incorrect, billing address matches',
                                'M8' => 'Cardholder name, billing address and postal code are all incorrect',
                                'M9' => 'Cardholder name matches',
                                'N3' => 'Address matches, ZIP not verified',
                                'N4' => 'Address and ZIP code not verified due to incompatible formats',
                                'N5' => 'Address and ZIP code match (International only)',
                                'N6' => 'Address not verified (International only)', 'N7' => 'ZIP matches, address not verified',
                                'N8' => 'Address and ZIP code match (International only)', 'N9' => 'Address and ZIP code match (UK only)',
                                'R' => 'Issuer does not participate in AVS', 'UN' => 'Unknown', 'X' => 'Zip Match/Zip 4 Match/Address Matc',
                                'Z' => 'Zip Match/Locale no match', 'blank' => 'Not applicable (non-Visa)');
    
    protected $_cvvCode = array( '1' => 'CVD Match', '2' => 'CVD Mismatch',
                                '3' => 'CVD Not Verified', '4' => 'CVD Should have been present',
                                '5' => 'CVD Issuer unable to process request',
                                '6' => 'CVD Not Provided' );
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Sales\Model\Order\Payment\Transaction $payment,
        \Rootways\Chase\Helper\Data $customHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->_paymentConfig = $paymentConfig;
        $this->paymentModel = $payment;
        $this->customHelper = $customHelper;
    }

    public function setCheckoutProgressBlock($flag)
    {
        $this->_isCheckoutProgressBlockFlag = $flag;
        return $this;
    }
    
    public function getCcTypeName()
    {
        $types = $this->_paymentConfig->getCcTypes();
        $ccType = $this->getInfo()->getCcType();
        if (isset($types[$ccType])) {
            return $types[$ccType];
        }
        return empty($ccType) ? __('N/A') : $ccType;
    }
    
    protected function avsResId($code) {
        $avsValue = '';
        if (isset($this->_avsCode[$code])) {
            $avsValue = $this->_avsCode[$code];
        }
        return $avsValue;
    }
    
    protected function cvvId($code) {
        $cvvValue = '';
        if (isset($this->_cvvCode[$code])) {
            $cvvValue = $this->_cvvCode[$code];
        }
        return $cvvValue;
    }
    
    public function getSpecificInformation()
    {
        return $this->_prepareSpecificInformation()->getData();
    }
    
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];
        $info = $this->getInfo();
        if ($ccType = $this->getCcTypeName()) {
            $data[(string) __('Credit Card Type')] = $ccType;
        }
        if ($info->getCcLast4()) {
            $data[(string) __('Credit Card Number')] = sprintf('xxxx-%s', $info->getCcLast4());
        }
        
        if ( isset($info->getAdditionalInformation()['order_tra_id']) ) {
            $data[(string) __('Transaction ID')] = $info->getAdditionalInformation()['order_tra_id'];
        }
        if ( isset( $info->getAdditionalInformation()['avs_res_id']) ) {
            if (isset($this->_avsCode[trim($info->getAdditionalInformation()['avs_res_id'])])) {
                $data[(string) __('AVS Response')] = $info->getAdditionalInformation()['avs_res_id'].' ('.$this->_avsCode[trim($info->getAdditionalInformation()['avs_res_id'])].')';
            }
        }
        if ( isset($info->getAdditionalInformation()['cvd_result']) ) {
            if (isset($this->_avsCode[trim($info->getAdditionalInformation()['cvd_result'])])) {
                $data[(string) __('CVV Response')] = $info->getAdditionalInformation()['cvd_result'].' ('.$this->_avsCode[trim($info->getAdditionalInformation()['cvd_result'])].')';
            } else {
                $data[(string) __('CVV Response')] = $info->getAdditionalInformation()['cvd_result'];
            }
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
