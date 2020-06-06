<?php
/**
 * Chase Payment Module.
 *
 * @category  Payment Integration
 * @package   Rootways_Chase
 * @author    Developer RootwaysInc <developer@rootways.com>
 * @copyright 2017 Rootways Inc. (https://www.rootways.com)
 * @license   Rootways Custom License
 * @link      https://www.rootways.com/shop/media/extension_doc/license_agreement.pdf
 */
namespace Rootways\Chase\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Monolog\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\LocalizedException;

class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'rootways_chase_option';
    protected $_code = self::CODE;
    protected $_formBlockType = 'Rootways\Chase\Block\Form';
    protected $_infoBlockType = 'Rootways\Chase\Block\Info';
 
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc = false;
    protected $_canReviewPayment = false;
    protected $_canCancelInvoice = true;
 
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Rootways\Chase\Helper\Data $customHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = array()
    ) {
        
        $this->customHelper = $customHelper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }
    
    public function isAvailable( CartInterface $sp762271 = null ) {
		return true;
	}
    
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ( $amount <= 0 ) {
            throw new LocalizedException(__('Invalid amount for capture.'));
        }
        
        $ref_id = 0;
        $trn_type = 'A';
        $result = $this->createCharge($payment, $amount, $trn_type, $ref_id);
        if (!isset($result['APPROVALSTATUS'])) {
            $errorMsg = 'There is an error in payment processing.';
            if (isset($result['STATUSMSG'])){
                $errorMsg = 'There is an error in payment processing. Error: '.$result['STATUSMSG'];
            }
            throw new LocalizedException(__($errorMsg));
        } else {
            if ($result['PROCSTATUS'] == 0 && $result['APPROVALSTATUS'] == 1) {
                $payment->setTransactionId( $result['TXREFNUM'] );
                $payment->setLastTransId( $result['TXREFNUM'] );
                $payment->setIsTransactionClosed(0)->setAdditionalInformation( 'tx_ref_idx', $result['TXREFIDX'] );
                $payment->setAdditionalInformation('order_tra_id', $result['TXREFNUM']);
                if (isset($result['AVSRESPCODE'])) {
                    $payment->setAdditionalInformation('avs_res_id', $result['AVSRESPCODE']);
                }
                if (isset($result['CVV2RESPCODE'])) {
                    $payment->setAdditionalInformation('cvd_result', $result['CVV2RESPCODE']);
                }
            } else {
                $errorMsg = 'There is an error in payment processing.';
                if (isset($result['STATUSMSG'])){
                    $errorMsg = 'There is an error in payment processing. Error: '.$result['STATUSMSG'];
                }
                throw new LocalizedException(__($errorMsg));
            }
        }
        return $this;
    }
 
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new LocalizedException(__('ErrorInvalid amount for capture.'));
        }
        $ref_id = 0;
        $trn_type = 'AC';
        if (isset($payment->getAdditionalInformation()['order_tra_id'])) {
            if ($payment->getAdditionalInformation()['order_tra_id'] != '') {
                $ref_id = $payment->getAdditionalInformation()['order_tra_id'];
                $trn_type = 'FC';
            }
        }
        $result = $this->createCharge($payment, $amount, $trn_type, $ref_id);
        if (!isset($result['APPROVALSTATUS'])) {
            $errorMsg = 'There is an error in payment processing.';
            if (isset($result['STATUSMSG'])){
                $errorMsg = 'There is an error in payment processing. Error: '.$result['STATUSMSG'];
            }
            throw new LocalizedException(__($errorMsg));
        } else {
            if ($result['PROCSTATUS'] == 0 && $result['APPROVALSTATUS'] == 1) {
                $payment->setTransactionId( $result['TXREFNUM'] );
                $payment->setIsTransactionClosed(1)->setAdditionalInformation( 'tx_ref_idx', $result['TXREFIDX']);
                $payment->setAdditionalInformation( 'order_tra_id', $result['TXREFNUM']);
                if (isset($result['AVSRESPCODE'])) {
                    $payment->setAdditionalInformation('avs_res_id', $result['AVSRESPCODE']);
                }
                if (isset($result['CVV2RESPCODE'])) {
                    $payment->setAdditionalInformation('cvd_result', $result['CVV2RESPCODE']);
                }
            } else {
                $errorMsg = 'There is an error in payment processing.';
                if (isset($result['STATUSMSG'])){
                    $errorMsg = 'There is an error in payment processing. Error: '.$result['STATUSMSG'];
                }
                throw new LocalizedException(__($errorMsg));
            }
        }
        return $this;
    }
    
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $trn_type = 'R';
        if ($payment->getTransactionId() && $amount > 0) {
            $result = $this->createCharge($payment, $amount, $trn_type, $payment->getRefundTransactionId());
            if ($result['PROCSTATUS'] == 0 && $result['APPROVALSTATUS'] == 1) {
                //$payment->setStatus('APPROVED');
            } else {
                $errorMsg = 'There is an error in refund processing, please try again.';
                if (isset($result['STATUSMSG'])) {
                    $errorMsg = 'There is an error in refund processing. Error: '.$result['STATUSMSG'];
                }
                throw new LocalizedException(__($errorMsg));
            }
        } else {
            $errorMsg = 'There is an error in refund processing, please try again.';
            if (isset($result['STATUSMSG'])) {
                $errorMsg = 'There is an error in refund processing. Error: '.$result['STATUSMSG'];
            }
            throw new LocalizedException(__($errorMsg));
        }
        return $this;
    }
    
    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     *
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $trn_type = 'V';
        if ( $payment->getTransactionId()) {
            $amount = $payment->getAmountAuthorized();
            $result = $this->createCharge($payment, $amount, $trn_type, $payment->getTransactionId());
            if ( $result['PROCSTATUS'] == 0) {
                //$payment->setStatus('APPROVED');
            } else {
                throw new LocalizedException(__('There is an error in void processing, please try again.'));
            }
        } else {
            throw new LocalizedException(__('Error in void the payment.'));
        }
        return $this;
    }
        
    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     *
     * @return object
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->void($payment);
    }
    
    /**
     * Class for create charge.
    */
    private function createCharge($payment, $amount, $trn_type, $ref_id)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/ravi.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$logger->info('Your text message');
        $order = $payment->getOrder();
        $orderStoreId = $order->getStoreId();
        $orderID = $order->getIncrementId();
        $orderAmount = $amount * 100;
        $postUrl = $this->customHelper->getGatewayUrl($orderStoreId);
        $userName = $this->customHelper->getUsername($orderStoreId);
        $password = $this->customHelper->getPassword($orderStoreId);
        $industryType = 'EC';
        $messageType = $trn_type;
        $bin = $this->customHelper->getBin($orderStoreId);
        $merchantID = $this->customHelper->getMerchantID($orderStoreId);
        $terminalID = $this->customHelper->getTerminalID($orderStoreId);
        $accountNum = $payment->getCcNumber();
        $dt = \DateTime::createFromFormat('Y', $payment->getCcExpYear());
        $expYear = $dt->format('y');
        $expMonth = sprintf("%02d", $payment->getCcExpMonth());
        $expDate = $expMonth.$expYear;
        $cvv = $payment->getCcCid();
        $cardSecValInd = '';
        if ($payment->getCcType() == 'VI' || $payment->getCcType() == 'DI') {
            $cardSecValInd = '1';
        }
        $billingaddress = $order->getBillingAddress();
        $first_name = $billingaddress->getData('firstname');
        $last_name = $billingaddress->getData('lastname');
        $fullname = $first_name . ' ' . $last_name ;
        if (strlen($fullname) >= 14) {
            $fullname = substr($fullname, 0, 14);
        }
        $billStreet = $order->getBillingAddress()->getStreet();
        $avsAddress1 = $billStreet[0];
        if (strlen($billStreet[0]) >= 30) {
            $avsAddress1 = substr($billStreet[0], 0, 30);
        }
        $avsAddress2 = '';
        if (!empty($billStreet[1])) {
            $avsAddress2 = $billStreet[1];
            if ( strlen($billStreet[1]) >= 30) {
                $avsAddress2 = substr($billStreet[1], 0, 30);
            }
        }
        $avsCity = $billingaddress->getData('city');
        if (strlen($avsCity) >= 20) {
            $avsCity = substr($avsCity, 0, 20);
        }
        if ($billingaddress->getData('region_id') != '') {
            $avsState = $this->customHelper->getRegionCode($billingaddress->getData('region_id'));
        } else {
            $avsState = $billingaddress->getData('region');
        }
        $country_id = $billingaddress->getData('country_id');
        $avsCountryCode = $this->customHelper->getCountryName($billingaddress->getData('country_id'));
        $avsZip = $billingaddress->getData('postcode');
        if (strlen($avsZip) >= 10) {
            $avsZip = substr($avsZip, 0, 10);
        }
        $avsPhone = $billingaddress->getData('telephone');
        if (strlen($avsPhone) >= 14) {
            $avsPhone = substr($avsPhone, 0, 14);
        }
        $email = $billingaddress->getData('email');
        
        $orderCurrency = $order->getBaseCurrencyCode();
        if ($orderCurrency == 'CAD') {
             $currencyCode = 124;
        } else if ($orderCurrency == 'USD') {
            $currencyCode = 840;
        } else if ($orderCurrency == 'GBP') {
            $currencyCode = 826;
        } else if ($orderCurrency == 'EUR') {
            $currencyCode = 978;
        } else {
            $currencyCode = 840; 
        }
        if (empty($billingaddress)) {
            throw new LocalizedException(__('Invalid billing data.'));
        }
        
        if ($trn_type == 'A') {
            $xmlData = '<Request><NewOrder>';
                $xmlData.='<OrbitalConnectionUsername>'.$userName.'</OrbitalConnectionUsername>';
                $xmlData.='<OrbitalConnectionPassword>'.$password.'</OrbitalConnectionPassword>';
                $xmlData.='<IndustryType>'.$industryType.'</IndustryType>';
                $xmlData.='<MessageType>'.$messageType.'</MessageType>';
                $xmlData.='<BIN>'.$bin.'</BIN>';
                $xmlData.='<MerchantID>'.$merchantID.'</MerchantID>';
                $xmlData.='<TerminalID>'.$terminalID.'</TerminalID>';
                $xmlData.='<CardBrand></CardBrand>';
                $xmlData.='<AccountNum>'.$accountNum.'</AccountNum>';
                $xmlData.='<Exp>'.$expDate.'</Exp>';
                $xmlData.='<CurrencyCode>'.$currencyCode.'</CurrencyCode>';
                $xmlData.='<CurrencyExponent>2</CurrencyExponent>';
                $xmlData.='<CardSecValInd>'.$cardSecValInd.'</CardSecValInd>';
                $xmlData.='<CardSecVal>'.$cvv.'</CardSecVal>';
                $xmlData.='<AVSzip>'.$avsZip.'</AVSzip>';
                $xmlData.='<AVSaddress1>'.$avsAddress1.'</AVSaddress1>';
                $xmlData.='<AVSaddress2>'.$avsAddress2 .'</AVSaddress2>';
                $xmlData.='<AVScity>'.$avsCity.'</AVScity>';
                $xmlData.='<AVSstate>'.$avsState.'</AVSstate>';
                $xmlData.='<AVSphoneNum>'.$avsPhone.'</AVSphoneNum>';
                $xmlData.='<AVSname>'.$fullname.'</AVSname>';
                $xmlData.='<AVScountryCode>'.$country_id.'</AVScountryCode>';
                $xmlData.='<OrderID>'.$orderID.'</OrderID>';
                $xmlData.='<Amount>'.$orderAmount.'</Amount>';
                $xmlData.='<CustomerEmail>'.$email.'</CustomerEmail>';
                $xmlData.='<CustomerIpAddress>'.$this->getClientIp().'</CustomerIpAddress>';
            $xmlData.='</NewOrder></Request>';
        } elseif ($trn_type == 'AC') {
            $xmlData = '<Request><NewOrder>';
                $xmlData.='<OrbitalConnectionUsername>'.$userName.'</OrbitalConnectionUsername>';
                $xmlData.='<OrbitalConnectionPassword>'.$password.'</OrbitalConnectionPassword>';
                $xmlData.='<IndustryType>'.$industryType.'</IndustryType>';
                $xmlData.='<MessageType>'.$messageType.'</MessageType>';
                $xmlData.='<BIN>'.$bin.'</BIN>';
                $xmlData.='<MerchantID>'.$merchantID.'</MerchantID>';
                $xmlData.='<TerminalID>'.$terminalID.'</TerminalID>';
                $xmlData.='<CardBrand></CardBrand>';
                $xmlData.='<AccountNum>'.$accountNum.'</AccountNum>';
                $xmlData.='<Exp>'.$expDate.'</Exp>';
                $xmlData.='<CurrencyCode>'.$currencyCode.'</CurrencyCode>';
                $xmlData.='<CurrencyExponent>2</CurrencyExponent>';
                $xmlData.='<CardSecValInd>'.$cardSecValInd.'</CardSecValInd>';
                $xmlData.='<CardSecVal>'.$cvv.'</CardSecVal>';
                $xmlData.='<AVSzip>'.$avsZip.'</AVSzip>';
                $xmlData.='<AVSaddress1>'.$avsAddress1.'</AVSaddress1>';
                $xmlData.='<AVSaddress2>'.$avsAddress2 .'</AVSaddress2>';
                $xmlData.='<AVScity>'.$avsCity.'</AVScity>';
                $xmlData.='<AVSstate>'.$avsState.'</AVSstate>';
                $xmlData.='<AVSphoneNum>'.$avsPhone.'</AVSphoneNum>';
                $xmlData.='<AVSname>'.$fullname.'</AVSname>';
                $xmlData.='<AVScountryCode>'.$country_id.'</AVScountryCode>';
                $xmlData.='<OrderID>'.$orderID.'</OrderID>';
                $xmlData.='<Amount>'.$orderAmount.'</Amount>';
                $xmlData.='<CustomerEmail>'.$email.'</CustomerEmail>';
                $xmlData.='<CustomerIpAddress>'.$this->getClientIp().'</CustomerIpAddress>';
            $xmlData.='</NewOrder></Request>';
        } elseif ($trn_type == 'FC') {
            $charge_id = str_replace('-capture','',$ref_id);
            $xmlData = '<Request><MarkForCapture>';
                $xmlData.='<OrbitalConnectionUsername>'.$userName.'</OrbitalConnectionUsername>';
                $xmlData.='<OrbitalConnectionPassword>'.$password.'</OrbitalConnectionPassword>';
                $xmlData.='<OrderID>'.$orderID.'</OrderID>';
                $xmlData.='<Amount>'.$orderAmount.'</Amount>';
                $xmlData.='<BIN>'.$bin.'</BIN>';
                $xmlData.='<MerchantID>'.$merchantID.'</MerchantID>';
                $xmlData.='<TerminalID>'.$terminalID.'</TerminalID>';
                $xmlData.='<TxRefNum>'.$charge_id.'</TxRefNum>';
            $xmlData.='</MarkForCapture></Request>';
        } elseif ($trn_type == 'R') {
            $charge_id = str_replace('-refund','',$ref_id);
            $xmlData = '<Request><NewOrder>';
                $xmlData.='<OrbitalConnectionUsername>'.$userName.'</OrbitalConnectionUsername>';
                $xmlData.='<OrbitalConnectionPassword>'.$password.'</OrbitalConnectionPassword>';
                $xmlData.='<IndustryType>'.$industryType.'</IndustryType>';
                $xmlData.='<MessageType>'.$messageType.'</MessageType>';
                $xmlData.='<BIN>'.$bin.'</BIN>';
                $xmlData.='<MerchantID>'.$merchantID.'</MerchantID>';
                $xmlData.='<TerminalID>'.$terminalID.'</TerminalID>';
                $xmlData.='<CurrencyExponent>2</CurrencyExponent>';
                $xmlData.='<OrderID>'.$orderID.'</OrderID>';
                $xmlData.='<Amount>'.$orderAmount.'</Amount>';
                $xmlData.='<TxRefNum>'.$charge_id.'</TxRefNum>';
            $xmlData.='</NewOrder></Request>';
        } elseif ( $trn_type == 'V' ) {
            $charge_id = str_replace('-void','',$ref_id);
            $xmlData = '<Request><Reversal>';
                $xmlData.='<OrbitalConnectionUsername>'.$userName.'</OrbitalConnectionUsername>';
                $xmlData.='<OrbitalConnectionPassword>'.$password.'</OrbitalConnectionPassword>';
                $xmlData.='<TxRefNum>'.$charge_id.'</TxRefNum>';
                //$xmlData.='<TxRefIdx>1</TxRefIdx>';
                $xmlData.='<OrderID>'.$orderID.'</OrderID>';
                $xmlData.='<BIN>'.$bin.'</BIN>';
                $xmlData.='<MerchantID>'.$merchantID.'</MerchantID>';
                $xmlData.='<TerminalID>'.$terminalID.'</TerminalID>';
            $xmlData.='</Reversal></Request>';
        } else {
            
        }
        //$xml = simplexml_load_string($xmlData);
        //echo '<pre>';print_r($xml);exit;
        $header = array("POST /AUTHORIZE HTTP/1.0");
        $header[] = "MIME-Version: 1.0";
        $header[] = "Content-type: application/PTI74";
        $header[] = "Content-length: ".strlen($xmlData);
        $header[] = "Content-transfer-encoding: text";
        $header[] = "Request-number: 1";
        $header[] = "Document-type: Request";
        $header[] = "Interface-Version: 0.3";
        $logger->info($postUrl);
        $logger->info($xmlData);
        $ch = curl_init($postUrl);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);

        $result = curl_exec($ch);
        $logger->info($result);
        curl_close($ch);
        $parser = xml_parser_create('UTF-8');
		xml_parse_into_struct($parser, $result, $response);
        $resData = array();
        foreach ($response as $v) {
           if ( isset($v["tag"]) && isset($v["value"]) ) {
                $resData[$v["tag"]] = $v["value"];   
            }
        }
        return $resData;
    }
    
    public function getClientIp() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}
