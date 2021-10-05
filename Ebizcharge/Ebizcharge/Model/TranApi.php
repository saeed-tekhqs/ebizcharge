<?php
declare(strict_types=1);
/**
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\TokenRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Payment\Model\InfoInterface;
use Laminas\Log\Writer\Stream as LogWriter;
use Laminas\Log\Logger as CustomLogger;

define("EBIZCHARGE_VERSION", "2.4.2");

/**
 * EBizCharge Transaction Class.
 *
 * Class TranApi
 * @package Ebizcharge\Ebizcharge\Model
 */
class TranApi
{
    use EbizLogger;

    // Required for all transactions

    /**
     * User Id
     *
     * @var int
     */
    private $userid;

    /**
     * Source key | security Id
     *
     * @var mixed
     */
    private $key;

    /**
     * Source pin/password (optional)
     *
     * @var mixed
     */
    private $pin;

    /**
     * The entire amount that will be charged to the customers card (including tax, shipping, etc)
     *
     * @var mixed
     */
    private $amount;

    /**
     * Invoice number.
     * Must be unique. Limited to 10 digits.
     * Use orderId if you need longer.
     *
     * @var  int
     */
    private $invoice;

    // Required for Commercial Card support

    /**
     * Purchase Order Number
     *
     * @var mixed
     */
    private $ponum;

    /**
     * Tax
     *
     * @var mixed
     */
    private $tax;

    /**
     * Order is non-taxable
     *
     * @var bool
     */
    private $nontaxable;

    // Amount details (optional)

    /**
     * Tip
     * @var mixed
     */
    private $tip;

    /**
     * Shipping charge
     *
     * @var mixed
     */
    private $shipping = 0;

    /**
     * Discount amount (ie gift certificate or coupon code)
     *
     * @var mixed
     */
    private $discount = 0;

    /**
     *  if subtotal is set, then
     *  subtotal + tip + shipping - discount + tax must equal amount
     *  or the transaction will be declined.  If subtotal is left blank
     *  then it will be ignored
     * @var mixed
     */
    private $subtotal = 0;

    /**
     * Currency of $amount
     *
     * @var mixed
     */
    private $currency;

    // Required Fields for Card Not Present transacitons (Ecommerce)

    /**
     * card number, no dashes, no spaces
     *
     * @var string
     */
    private $card;

    /**
     * Type of the card
     *
     * @var string
     */
    private $cardtype;

    /**
     * Expiration date 4 digits no
     *
     * @var mixed
     */
    private $exp;

    /**
     * Name of card-holder. Please enter a valid credit card type number.
     *
     * @var mixed
     */
    private $cardholder;

    /**
     * Street address
     * @var mixed
     */
    private $street;

    /**
     * Zip code
     *
     * @var mixed
     */
    private $zip;

    /**
     * Current saved method Id
     *
     * @var int
     */
    private $savedMethodId;

    // Required Fields for Ach

    /**
     * @var mixed
     */
    private $achroute;

    /**
     * @var string
     */
    private $achtype;

    // Fields for Card Present (POS)

    /**
     * Mag stripe data.  can be either Track 1, Track2  or  Both  (Required if card,exp,cardholder,street and zip aren't filled in)
     *
     * @var mixed
     */
    private $magstripe;

    /**
     * Must be set to true if processing a card present transaction  (Default is false)
     *
     * @var bool
     */
    private $cardpresent = false;

    // fields required for check transactions

    /**
     *  Bank account number
     *
     * @var mixed
     */
    private $account;

    /**
     * Bank routing number
     *
     * @var mixed
     */
    private $routing;

    // Fields required for Secure Vault Payments (Direct Pay)

    /**
     * ID of cardholders bank
     *
     * @var mixed
     */
    private $svpbank;

    /**
     * URL that the bank should return the user to when tran is completed
     *
     * @var mixed
     */
    private $svpreturnurl;

    /**
     * URL that the bank should return the user if they cancel
     *
     * @var mixed
     */
    private $svpcancelurl;

    // Option parameters

    /**
     * Required if running post auth transaction.
     *
     * @var mixed
     */
    private $origauthcode;

    /**
     * Type of command to run; Possible values are:
     * sale, credit, void, preauth, postauth, check and checkcredit.
     * Default is sale.
     *
     * @var string
     */
    private $command = 'sale';

    /**
     * Unique order identifier.  This field can be used to reference
     * the order for which this transaction corresponds to. This field
     * can contain up to 64 characters and should be used instead of
     * UMinvoice when orderids longer than 10 digits are needed.
     *
     * @var mixed
     */
    private $orderid;

    /**
     * Alpha-numeric id that uniquely identifies the customer.
     *
     * @var mixed
     */
    private $custid;

    /**
     * Description of charge
     *
     * @var string
     */
    private $description;

    /**
     * cvv2 code
     *
     * @var mixed
     */
    private $cvv2;

    /**
     * Customer email address
     *
     * @var string
     */
    private $custemail;

    /**
     * Send customer a receipt
     *
     * @var mixed
     */
    private $custreceipt = false;

    /**
     * Select receipt template
     *
     * @var mixed
     */
    private $custreceipt_template;

    /**
     * Prevent the system from detecting and folding duplicates
     *
     * @var bool
     */
    private $ignoreduplicate;

    /**
     *  IP address of remote host
     *
     * @var mixed
     */
    private $ip;

    /**
     *  Transaction timeout.  defaults to 90 seconds
     *
     * @var int
     */
    private $timeout = 90;

    // Recurring Billing

    /**
     * Save transaction as a recurring transaction
     *
     * @var bool
     */
    private $recurring;

    /**
     * How often to run transaction: daily, weekly, biweekly, monthly, bimonthly, quarterly, annually.
     * Default is monthly.
     *
     * @var mixed
     */
    private $schedule;

    /**
     *  The number of times to run. Either a number or * for unlimited.
     * Default is unlimited.
     *
     * @var mixed
     */
    private $numleft;

    /**
     * When to start the schedule.
     * Default is tomorrow.
     * Must be in YYYYMMDD  format.
     *
     * @var mixed
     */
    private $start;

    /**
     * When to stop running transactions. Default is to run forever.
     * If both end and numleft are specified, transaction will stop when the earliest condition is met.
     *
     * @var mixed
     */
    private $expire;

    /**
     * Recurring is infinite
     *
     * @var bool
     */
    private $recurringIndefinitely;

    /**
     * Method ID to set for recurring payment.
     *
     * @var mixed
     */
    private $recurringMethodId;

    /**
     * Optional recurring billing amount.
     * If not specified, the amount field will be used for future recurring billing payments
     *
     * @var mixed
     */
    private $billamount;

    /**
     * @var mixed
     */
    private $billtax;

    /**
     * @var mixed
     */
    private $billsourcekey;

    // Billing Fields

    /**
     * @var string
     */
    private $billfname;

    /**
     * @var string
     */
    private $billlname;

    /**
     * @var string
     */
    private $billcompany;

    /**
     * @var string
     */
    private $billstreet;

    /**
     * @var string
     */
    private $billstreet2;

    /**
     * @var string
     */
    private $billcity;

    /**
     * @var string
     */
    private $billstate;

    /**
     * @var mixed
     */
    private $billzip;

    /**
     * @var string
     */
    private $billcountry;

    /**
     * @var mixed
     */
    private $billphone;

    /**
     * @var string
     */
    private $email;

    /**
     * @var mixed
     */
    private $fax;

    /**
     * @var mixed
     */
    private $website;

    // Shipping Fields

    /**
     * Type of delivery method ('ship','pickup','download')
     *
     * @var string
     */
    private $delivery;

    /**
     * @var string
     */
    private $shipfname;

    /**
     * @var string
     */
    private $shiplname;

    /**
     * @var string
     */
    private $shipcompany;

    /**
     * @var string
     */
    private $shipstreet;

    /**
     * @var string
     */
    private $shipstreet2;

    /**
     * @var string
     */
    private $shipcity;

    /**
     * @var string
     */
    private $shipstate;

    /**
     * @var mixed
     */
    private $shipzip;

    /**
     * @var string
     */
    private $shipcountry;

    /**
     * @var mixed
     */
    private $shipphone;


    /**
     * Line items - see addLine()
     *
     * @var mixed
     */
    private $lineitems = [];

    /**
     * Line items for tokenization - see addLineItem()
     *
     * @var mixed
     */
    private $lineItems;

    // Recurring items for add recurrings
    //private $recurringLineItems;

    /**
     * Additional transaction details or comments (free form text field supports up to 65,000 chars)
     *
     * @var string
     */
    private $comments;

    /**
     * Allows developers to identify their application to the gateway (for troubleshooting purposes)
     *
     * @var string
     */
    private $software = 'Magento2';

    // response fields

    /**
     * Raw result from gateway
     *
     * @var mixed
     */
    private $rawresult;

    /**
     * Full result:  Approved, Declined, Error
     *
     * @var mixed
     */
    private $result = 'Error';

    /**
     * Abbreviated result code: A|D|E
     *
     * @var mixed
     */
    private $resultcode = 'E';

    /**
     * Authorization code
     *
     * @var mixed
     */
    private $authcode;

    /**
     * Reference number
     *
     * @var mixed
     */
    private $refnum;

    /**
     * Batch number
     *
     * @var mixed
     */
    private $batch;

    /**
     * AVS result
     *
     * @var mixed
     */
    private $avs_result;

    /**
     * AVS result code
     *
     * @var mixed
     */
    private $avs_result_code;

    /**
     * Obsolete avs result
     *
     * @var mixed
     */
    private $avs;

    /**
     * cvv2 result
     *
     * @var mixed
     */
    private $cvv2_result;

    /**
     * cvv2 result code
     *
     * @var mixed
     */
    private $cvv2_result_code;

    /**
     *  vpas result code
     *
     * @var mixed
     */
    private $vpas_result_code;

    /**
     * system identified transaction as a duplicate
     *
     * @var bool
     */
    private $isduplicate;

    /**
     * Transaction amount after server has converted it to merchants currency
     *
     * @var mixed
     */
    private $convertedamount;

    /**
     * Merchants currency
     *
     * @var mixed
     */
    private $convertedamountcurrency;

    /**
     * The conversion rate that was used
     *
     * @var mixed
     */
    private $conversionrate;

    /**
     * Gateway assigned customer ref number for recurring billing
     *
     * @var mixed
     */
    private $custnum;

    // Cardinal Response Fields

    /**
     * Card auth url
     *
     * @var mixed
     */
    private $acsurl;

    /**
     * card auth request
     *
     * @var mixed
     */
    private $pareq;

    /**
     * Cardinal transid
     *
     * @var mixed
     */
    private $cctransid;

    // Errors Response Fields

    /**
     * Error message if result is an error
     *
     * @var mixed
     */
    private $error = 'Transaction not processed yet.';

    /**
     * Numerical error code
     *
     * @var mixed
     */
    private $errorcode;

    // For TranAPI Config import

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    /**
     * @var bool
     */
    private $achStatus = false;

    const ACH = 'ACH';

    /**
     * @var string
     */
    private $ebiz_version;

    /**
     * @var ClientFactory
     */
    private $soapClientFactory;

    /**
     * @var \SoapClient|null $ebizSoapClient
     */
    private $ebizSoapClient = null;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollection;

    /**
     * @param ClientFactory $soapClientFactory
     * @param Config $config
     * @param TokenFactory $tokenFactory
     * @param TokenRepositoryInterface $tokenRepository
     * @param CustomerCollectionFactory $customerCollection
     * @param PaymentConfig $paymentConfig
     */
    public function __construct(
        ClientFactory $soapClientFactory,
        Config $config,
        TokenFactory $tokenFactory,
        TokenRepositoryInterface $tokenRepository,
        CustomerCollectionFactory $customerCollection,
        PaymentConfig $paymentConfig
    ) {
        $this->soapClientFactory = $soapClientFactory;
        $this->config = $config;
        $this->paymentConfig = $paymentConfig;
        $this->ebiz_version = "Ebizcharge_Ebizcharge" . EBIZCHARGE_VERSION;
        if(isset($_SERVER['REMOTE_ADDR'])) {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }
        $this->initTransactionAPI();
        $this->tokenFactory = $tokenFactory;
        $this->tokenRepository = $tokenRepository;
        $this->customerCollection = $customerCollection;
    }

    public function _getGatewayBaseUrl()
    {
        return 'https://soap.ebizcharge.net';
    }

    public function getWsdlUrl()
    {
        return 'https://soap.ebizcharge.net/eBizService.svc?singleWsdl'; // production
        //return 'https://ebizsoapapidev1.azurewebsites.net/eBizService.svc?singleWsdl'; // dev
    }

    /**
     * Get Ebizcharge gateway soap object
     *
     * @return \SoapClient
     */
    public function getClient(): \SoapClient
    {
        if($this->ebizSoapClient == null) {
            $this->ebizSoapClient = $this->soapClientFactory->create($this->getWsdlUrl(), $this->soapParams());
        }
        return $this->ebizSoapClient;
    }

    public function initTransactionAPI()
    {
        $this->key = $this->config->getSourceKey();
        $this->userid = $this->config->getSourceId();
        $this->pin = $this->config->getSourcePin();
        return $this;
    }

    public function getUeSecurityToken(): array
    {
        return [
            'SecurityId' => $this->key,
            'UserId' => $this->userid,
            'Password' => $this->pin
        ];
    }

    public function soapParams()
    {
        return array(
            'soap_version' => SOAP_1_1,
            // Work For local and live both
            //'trace'      => true, // NOt in use
            //'exceptions' => true, // disable exceptions // not in use
            //'features' => SOAP_SINGLE_ELEMENT_ARRAYS, // not in use
            //'encoding' => 'UTF-8', // not in use
            //'connection_timeout' => 300, // Work For local and live both
            //'cache_wsdl' => false, // disable any caching on the wsdl, encase you alter the wsdl server // Work For local and live both
            'cache_wsdl' => 0,
            // disable any caching on the wsdl, encase you alter the wsdl server // Work For local and live both
            //'keep_alive' => false // Work For live only
        );
    }

    /**
     * Logger for ebiz gateway
     *
     * @param $message
     * @param null $level
     * @return CustomLogger
     */
    public function log($message, $level = null)
    {
        return $this->ebizLog()->info($message);
    }

    /**
     * Cron logger
     *
     * @param $message
     * @param null $level
     * @return CustomLogger
     */
    public function cronlog($message, $level = null)
    {
        return $this->ebizCronLog()->info($message);
    }


    /**
     * Run Customer payment transaction for One ResultCode
     * @throws LocalizedException
     */
    public function validateSecurityKey()
    {
        try {
            $transaction = $this->getClient()->SearchCustomers(
                array(
                    'securityToken' => $this->getUeSecurityToken(),
                    'start' => 0,
                    'limit' => 1
                ));

            if(!empty($transactionResult = $transaction->SearchCustomersResult)) {
                return 'valid';
            } else {
                return 'invalid';
            }

        } catch (\Exception $ex) {
            throw new LocalizedException(__('SoapFault: ' . $ex->getMessage()));
        }

        return false;
    }

    /**
     * Add a line item to the transaction
     * @param  $item
     * @todo: where we are using lineitems?
     */
    public function addLine($item)
    {
        $this->lineitems[] = [
            'sku' => $item->getSku(),
            'name' => $item->getName(),
            'description' => '',
            'cost' => $item->getPrice(),
            'taxable' => ($item->getTaxAmount() > 0) ? 'Y' : 'N',
            'qty' => $item->getQtyOrdered(),
            'id' => $item->getProductId(),
            'discount' => $item->getDiscountAmount()
        ];
    }

    /**
     * Add line items to the transaction
     * @param $item
     */
    public function addLineItem($item)
    {
        $this->lineItems[] = [
            'SKU' => $item->getSku(),
            'ProductName' => $item->getName(),
            'Description' => '',
            'UnitPrice' => $item->getPrice(),
            'Taxable' => ($item->getTaxAmount() > 0) ? 'Y' : 'N',
            'TaxAmount' => $item->getTaxAmount(),
            'Qty' => $item->getQtyOrdered(),
            'Id' => $item->getProductId(),
            'DiscountAmount' => $item->getDiscountAmount(),
            'DiscountRate' => $item->getDiscountPercent(),
            'DiscountInvoiced' => $item->getDiscountInvoiced()
        ];
    }

    public function clearLines()
    {
        $this->lineitems = [];
    }

    public function clearLineItems()
    {
        $this->lineItems = [];
    }

    /**
     * Verify that all required data has been set
     *
     * @return string
     */
    public function checkData()
    {
        if(!$this->key) {
            return "Source Key is required";
        }

        if(in_array(strtolower($this->command), array(
            "quickcredit",
            "quicksale",
            "cc:capture",
            "cc:refund",
            "refund",
            "check:refund",
            "capture",
            "creditvoid"
        ))) {
            if(!$this->refnum) {
                return "Reference Number is required";
            }
        } elseif(strtolower($this->command) == "svp") {
            if(!$this->svpbank) {
                return "Bank ID is required";
            }

            if(!$this->svpreturnurl) {
                return "Return URL is required";
            }

            if(!$this->svpcancelurl) {
                return "Cancel URL is required";
            }
        } else {
            if(in_array(strtolower($this->command),
                array("check:sale", "check:credit", "check", "checkcredit", "reverseach"))) {
                if(!$this->account) {
                    return "Account Number is required";
                }

                if(!$this->achroute) {
                    return "Routing Number is required";
                }
            } else {
                if(!$this->magstripe) {
                    if(!$this->card) {
                        return "Credit Card Number is required ({$this->command})";
                    }

                    if(!$this->exp) {
                        return "Expiration Date is required";
                    }
                }
            }

            $this->amount = preg_replace('/[^\d.]+/', '', $this->amount);

            if(!$this->amount) {
                return "Amount is required";
            }

            if(!$this->invoice && !$this->orderid) {
                return "Invoice number or Order ID is required";
            }

        }

        return 0;
    }

    //------------------- Developer New Functions Added Start -------------------

    /**
     * set order shipping info
     * @param $order
     */
    public function setOrderShipping($order)
    {
        $shipping = $order->getShippingAddress();
        if(!empty($shipping)) {

            $shippingStreet = $shipping->getStreet();
            $streetShip = $shippingStreet[0];

            if(empty($shipping->getStreet(2))) {
                $street2Ship = '';
            } else {
                $shippingStreet2 = $shipping->getStreet(2);
                $street2Ship = $shippingStreet2[0];
            }

            $this->shipfname = $shipping->getFirstname();
            $this->shiplname = $shipping->getLastname();
            $this->shipcompany = $shipping->getCompany();
            $this->shipstreet = $streetShip;
            $this->shipstreet2 = $street2Ship;
            $this->shipcity = $shipping->getCity();
            $this->shipstate = $shipping->getRegion();
            $this->shipzip = $shipping->getPostcode();
            $this->shipcountry = $shipping->getCountryId();
            $this->shipphone = $shipping->getTelephone();
        }
    }

    /**
     * add order billing info
     * @param $order
     */
    public function setOrderBilling($order)
    {
        $billing = $order->getBillingAddress();
        if(!empty($billing)) {

            $street = $billing->getStreet();
            $streetBill = $street[0];

            if(empty($billing->getStreet(2))) {
                $street2Bill = '';
            } else {
                $Street2 = $billing->getStreet(2);
                $street2Bill = $Street2[0];
            }

            $this->billfname = $billing->getFirstname();
            $this->billlname = $billing->getLastname();
            $this->billcompany = $billing->getCompany();
            $this->billstreet = $streetBill;
            $this->billstreet2 = $street2Bill;
            $this->billcity = $billing->getCity();
            $this->billstate = $billing->getRegion();
            $this->billzip = $billing->getPostcode();
            $this->billcountry = $billing->getCountryId();
            $this->billphone = $billing->getTelephone();

            if($this->custid == null) {
                $this->custid = $billing->getCustomerId();
            }
        }
    }

    /**
     * set payment transaction result
     * @param $transaction
     * @return bool
     */
    public function setTransactionResult($transaction)
    {
        if(isset($transaction->Error)) {
            $this->ebizLog()->info('Transaction Failed Error: ' . $transaction->Error);
        }
        $this->result = $transaction->Result;
        $this->resultcode = $transaction->ResultCode;
        $this->authcode = $transaction->AuthCode ?? '';
        $this->refnum = $transaction->RefNum;
        $this->batch = $transaction->BatchNum;
        $this->avs_result = $transaction->AvsResult;
        $this->avs_result_code = $transaction->AvsResultCode ?? '';
        $this->cvv2_result = '';
        $this->cvv2_result_code = '';
        $this->vpas_result_code = $transaction->VpasResultCode;
        $this->convertedamount = $transaction->ConvertedAmount;
        $this->convertedamountcurrency = $transaction->ConvertedAmountCurrency;
        $this->conversionrate = $transaction->ConversionRate;
        $this->error = $transaction->Error ?? '';
        $this->errorcode = $transaction->ErrorCode;
        $this->custnum = $transaction->CustNum;
        // Obsolete variable (for backward compatibility) At some point they will no longer be set.
        $this->avs = '';
        $this->cvv2 = '';

        $this->acsurl = $transaction->AcsUrl;
        $this->pareq = $transaction->Payload;

        if($this->resultcode == 'A') {
            return true;
        }

        return false;
    }

    /**
     * get order billing address
     * @return array
     */
    private function getBillingAddress()
    {
        return array(
            'FirstName' => $this->billfname,
            'LastName' => $this->billlname,
            'Company' => $this->billcompany,
            'Street' => $this->billstreet,
            'Street2' => $this->billstreet2,
            'City' => $this->billcity,
            'State' => $this->billstate,
            'Zip' => $this->billzip,
            'Country' => $this->billcountry,
            'Phone' => $this->billphone,
            'Fax' => $this->fax,
            'Email' => $this->email
        );
    }

    /**
     * get order shipping address
     * @return array
     */
    private function getShippingAddress()
    {
        return array(
            'FirstName' => $this->shipfname,
            'LastName' => $this->shiplname,
            'Company' => $this->shipcompany,
            'Street' => $this->shipstreet,
            'Street2' => $this->shipstreet2,
            'City' => $this->shipcity,
            'State' => $this->shipstate,
            'Zip' => $this->shipzip,
            'Country' => $this->shipcountry,
            'Phone' => $this->shipphone,
            'Fax' => $this->fax,
            'Email' => $this->email
        );
    }

    /**
     * get order transaction details
     * @return array
     */
    private function getTransactionDetails()
    {
        return array(
            'OrderID' => $this->orderid,
            'Invoice' => $this->invoice,
            'PONum' => $this->ponum,
            'Description' => $this->description,
            'Amount' => $this->amount,
            'Tax' => $this->tax,
            'Currency' => $this->currency,
            'Shipping' => $this->shipping,
            'ShipFromZip' => $this->shipzip,
            'Discount' => $this->discount,
            'Subtotal' => $this->subtotal,
            'AllowPartialAuth' => false,
            'Tip' => 0,
            'NonTax' => false,
            'Duty' => 0,
        );
    }

    /**
     * @return bool
     */
    private function isCardTypeACH()
    {
        return $this->cardtype === self::ACH;

    }

    /**
     * get transaction request
     * @return array
     */
    private function getTransactionRequest()
    {
        $magCustomerId = empty($this->custid) ? 'Guest' : $this->custid;
        $magCommand = $this->isCardTypeACH() ? 'check' : $this->command;

        return array(
            'CustReceipt' => $this->custreceipt,
            'CustReceiptName' => $this->custreceipt_template,
            'Software' => $this->software,
            'LineItems' => $this->lineItems,
            'IsRecurring' => false,
            'IgnoreDuplicate' => false,
            'Details' => $this->getTransactionDetails(),
            'CustomerID' => $magCustomerId,
            'CreditCardData' => array(
                'InternalCardAuth' => false,
                'CardPresent' => true,
                'CardNumber' => $this->card,
                'CardExpiration' => $this->exp,
                'CardCode' => $this->cvv2,
                'AvsStreet' => $this->billstreet,
                'AvsZip' => $this->billzip
            ),
            'CheckData' => array(
                'Account' => $this->card,
                'AccountType' => $this->achtype,
                'Routing' => $this->achroute
            ),
            'Command' => $magCommand,
            'ClientIP' => $this->ip,
            'AccountHolder' => $this->cardholder,
            'RefNum' => $this->refnum,
            'BillingAddress' => $this->getBillingAddress(),
            'ShippingAddress' => $this->getShippingAddress()
        );
    }

    /**
     * Get Customer Data for Add New Customer
     * @param $customerId
     * @return array
     */
    public function getCustomerData($customerId)
    {
        return array(
            'CustomerId' => $customerId,
            'FirstName' => $this->billfname,
            'LastName' => $this->billlname,
            'CompanyName' => $this->billcompany,
            'Phone' => $this->billphone,
            'CellPhone' => $this->billphone,
            'Fax' => $this->fax,
            'Email' => $this->email,
            'WebSite' => $this->website,
            'ShippingAddress' => $this->getShippingAddressAddCust(),
            'BillingAddress' => $this->getBillingAddressAddCust(),
            'SoftwareId' => 'Magento2',
        );
    }

    /**
     * Get Customer Payment data
     * @return array
     */
    private function getCustomerPayment()
    {
        $paymentTypes = $this->paymentConfig->getCcTypes();
        $cardType = $this->cardtype;

        if($cardType != self::ACH) {

            foreach ($paymentTypes as $code => $text) {
                if($code == $this->cardtype) {
                    $cardType = $text;
                }
            }

            $paymentMethod = array(
                'MethodName' => $cardType . ' ' . substr($this->card, -4) . ' - ' . $this->cardholder,
                # . ' - Expires on: ' . $this->exp,
                'AccountHolderName' => $this->cardholder ?? '',
                'SecondarySort' => 1,
                'Created' => date('Y-m-d\TH:i:s'),
                'Modified' => date('Y-m-d\TH:i:s'),
                'AvsStreet' => $this->billstreet,
                'AvsZip' => $this->billzip,
                'CardCode' => $this->cvv2,
                'CardExpiration' => $this->exp,
                'CardNumber' => $this->card,
                'CardType' => $this->cardtype,
                'Balance' => $this->amount,
                'MaxBalance' => $this->amount,
            );

        } else {
            $paymentMethod = array(
                'MethodName' => ucwords($this->achtype) . ' ' . substr($this->card, -4) . ' - ' . $this->cardholder,
                'SecondarySort' => 1,
                'Created' => date('Y-m-d\TH:i:s'),
                'Modified' => date('Y-m-d\TH:i:s'),
                'Account' => $this->card,
                'AccountType' => $this->achtype,
                'AccountHolderName' => $this->cardholder ?? '',
                'Routing' => $this->achroute,
                'MethodType' => self::ACH,
                'Balance' => $this->amount,
                'MaxBalance' => $this->amount,
            );
        }

        return $paymentMethod;
    }

    /**
     * get Customer transaction request
     * @return array
     */
    private function getCustomerTransactionRequest()
    {
        $command = $this->isCardTypeACH() ? 'check' : $this->command;
        $result = array(
            'isRecurring' => false,
            'IgnoreDuplicate' => true,
            'Details' => $this->getTransactionDetails(),
            'Software' => $this->software,
            'MerchReceipt' => true,
            'CustReceiptName' => $this->custreceipt_template,
            'CustReceiptEmail' => '',
            'CustReceipt' => $this->custreceipt,
            'ClientIP' => $this->ip,
            'CardCode' => $this->cvv2,
            'Command' => $command,
            'LineItems' => $this->lineItems
        );
        return $result;
    }

    /**
     * New billing address for add customer
     * @return array
     */
    private function getBillingAddressAddCust()
    {
        return array(
            'FirstName' => $this->billfname,
            'LastName' => $this->billlname,
            'Company' => $this->billcompany,
            'Address1' => $this->billstreet,
            'Address2' => $this->billstreet2,
            'City' => $this->billcity,
            'State' => $this->billstate,
            'ZipCode' => $this->billzip,
            'Country' => $this->billcountry,
            'Phone' => $this->billphone,
            'Fax' => $this->fax,
            'Email' => $this->email
        );
    }

    /**
     * New shipping for add customer
     * @return array
     */
    private function getShippingAddressAddCust()
    {
        return array(
            'FirstName' => $this->shipfname,
            'LastName' => $this->shiplname,
            'Company' => $this->shipcompany,
            'Address1' => $this->shipstreet,
            'Address2' => $this->shipstreet2,
            'City' => $this->shipcity,
            'State' => $this->shipstate,
            'ZipCode' => $this->shipzip,
            'Country' => $this->shipcountry,
            'Phone' => $this->shipphone,
            'Fax' => $this->fax,
            'Email' => $this->email
        );
    }

    /**
     * Set Transaction Data for Void, Cancel and Refund
     * @param $payment
     */
    public function setTransactionData($payment)
    {
        $this->refnum = $payment->getCcTransId();
        $this->cardholder = $payment->getCcOwner();
        $this->card = $payment->getCcNumber();
        $this->routing = $payment->getAchRoute();
        $this->achtype = $payment->getAchType();

        $this->exp = $payment->getCcExpMonth() . substr($payment->getCcExpYear() ?? '', 2, 2);
        $this->cvv2 = $payment->getCcCid();

        $order = $payment->getOrder();

        if(!empty($order)) {
            $orderId = $order->getIncrementId();
            $this->orderid = $orderId;
            $this->invoice = $orderId;
            $this->ponum = $orderId;

            $this->custid = $order->getCustomerId();
            $this->ip = $order->getRemoteIp();
            $this->email = $order->getCustomerEmail();
            $this->tax = $order->getTaxAmount();
            $this->shipping = $order->getShippingAmount();

            // avs data
            list($avsStreet) = $order->getBillingAddress()->getStreet();
            $this->street = $avsStreet;
            $this->zip = $order->getBillingAddress()->getPostcode();

            $this->setOrderBilling($order);
            $this->setOrderShipping($order);

            if($order->hasInvoices()) {
                foreach ($order->getInvoiceCollection() as $invoice) {
                    foreach ($invoice->getAllItems() as $item) {
                        $this->addLine($item);
                        // for tokenization
                        $this->addLineItem($item);
                    }
                }
            }
        }
    }


    //-------------------- EBizCharge Connect Methods Start ---------------

    /**
     * search customer by local magento id
     * @param $mageCustomerId
     * @return string 'Found' or 'Not Found'
     * @throws LocalizedException
     */
    function searchCustomers($mageCustomerId)
    {
        $mappedCustomerId = $this->getMappedCustomerId($mageCustomerId);

        try {
            $searchCustomer = $this->getClient()->SearchCustomers(
                array(
                    'securityToken' => $this->getUeSecurityToken(),
                    'customerId' => $mappedCustomerId,
                    'start' => 0,
                    'limit' => 1
                ));

            if(!isset($searchCustomer->SearchCustomersResult->Customer) || empty($searchCustomer->SearchCustomersResult->Customer)) {
                $ebzcCustomer = 'Not Found';
                $this->ebizLog()->info('Customer ' . $mappedCustomerId . ' Not Found');
            } else {
                $ebzcCustomer = $searchCustomer->SearchCustomersResult->Customer;
            }

        } catch (\Exception $ex) {
            $this->ebizLog()->info('Error in search customer ' . $ex->getMessage());
            throw new LocalizedException(__('searchCustomers ' . $ex->getMessage()));
        }

        return $ebzcCustomer;
    }

    /**
     * @param $magCustomerId
     * @return mixed
     */
    function getCustomerToken($magCustomerId)
    {
        if($customer = $this->getCustomer($magCustomerId)) {
            return $customer->CustomerToken;
        }

        return null;
    }

    public function getCustomer($magCustomerId)
    {
        try {
            $mappedCustomerId = $this->getMappedCustomerId($magCustomerId);

            $customer = $this->getClient()->GetCustomer(
                [
                    'securityToken' => $this->getUeSecurityToken(),
                    'customerId' => $mappedCustomerId
                ]
            );

            if(isset($customer->GetCustomerResult)) {
                return $customer->GetCustomerResult;
            }

        } catch (\Exception $ex) {
            $this->ebizLog()->info(__METHOD__ . ' Error in getting customer ' . $ex->getMessage());
            return null;
        }

        return null;
    }

    /**
     * @param $tableName
     * @param $mageCustomerId
     * @param $ebzcCustomerId
     */
    public function runUpdateCustomer($tableName, $mageCustomerId, $ebzcCustomerId)
    {
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);

        $dataToUpdate = [
            'ebzc_cust_id' => $ebzcCustomerId
        ];
        $where = ['mage_cust_id = ?' => $mageCustomerId];

        try {
            $connection->beginTransaction();
            $connection->update($tableName, $dataToUpdate, $where);
            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $this->ebizLog()->crit($e->getMessage());
            $connection->rollBack();
            return false;
        }
    }

    /**
     * @param $tableName
     * @param $column
     * @param $ecItemSyncStatus
     * @param $ecItemInternalId
     * @param $ecCustomerId
     * @param $ebizCustomerToken
     * @param $entityId
     */
    public function runUpdateQueryCustomer(
        $tableName,
        $column,
        $ecItemSyncStatus,
        $ecItemInternalId,
        $ecCustomerId,
        $ebizCustomerToken,
        $entityId
    ) {
        $timeNow = date('Y-m-d h:i:s');
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);

        $dataToUpdate = [
            'ec_' . $column . '_sync_status' => $ecItemSyncStatus,
            'ec_' . $column . '_internalid' => $ecItemInternalId,
            'ec_' . $column . '_id' => $ecCustomerId,
            'ec_' . $column . '_token' => $ebizCustomerToken,
            'ec_' . $column . '_lastsyncdate' => $timeNow,
        ];
        $where = ['entity_id = ?' => $entityId];

        try {
            $connection->beginTransaction();
            $connection->update($tableName, $dataToUpdate, $where);
            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $this->ebizLog()->crit($e->getMessage());
            $connection->rollBack();
            return false;
        }
    }

    //---------------- EBizCharge Connect Methods End ---------------

    //---------------- EBizCharge Methods Start -----------

    /**
     * return Econnect customer id if found in mapping else return local magento customer id
     * @param $magCustomerId
     * @return mixed
     */
    public function getMappedCustomerId($magCustomerId)
    {
        try {
            $eConnectId = $this->customerCollection->create()
                ->addFilter('entity_id', $magCustomerId)
                ->getFirstItem()
                ->getData('ec_cust_id');

            return !empty($eConnectId) ? $eConnectId : $magCustomerId;
        } catch (\Exception $e) {
            return $magCustomerId;
        }
    }

    /**
     * Tokenization customer checkout: Add customer to gateway and process the transaction
     * a user is logged in option is new customer + add payment method
     * @param $customerId
     * @param $paymentObj
     * @return bool
     * @throws LocalizedException
     */
    function tokenProcess($customerId, $paymentObj)
    {
        try {
            $searchCustomerResult = $this->searchCustomers($customerId);
            # Case 1 Local = No, Live = No
            if(($searchCustomerResult == 'Not Found') || ($searchCustomerResult == null)) {
                $this->addCustomerAndRunCustomerTransaction($customerId, $paymentObj);

            } else {
                $this->runTransaction();
            }
            # Case 3 Local = No, Live = Yes
            # Added on Payment.php line #404

        } catch (\Exception $e) {
            throw new LocalizedException(__('SoapFault: ' . $e->getMessage()));
        }

        return false;
    }

    /**
     * @param $customerInternalId
     * @param $parameters
     * @return null|mixed
     */
    public function addCustomerPaymentMethod($customerInternalId, $parameters)
    {
        $paymentMethod = $this->getClient()->addCustomerPaymentMethodProfile(
            array(
                'securityToken' => $this->getUeSecurityToken(),
                'customerInternalId' => $customerInternalId,
                'paymentMethodProfile' => $parameters
            ));

        if(isset($paymentMethod->AddCustomerPaymentMethodProfileResult)) {
            return $paymentMethod->AddCustomerPaymentMethodProfileResult;
        }

        return null;
    }

    /**
     * set customer payment id in Magento payment object
     * @param $methodId
     * @param $paymentObj
     */
    private function setPaymentMethodId($methodId, $paymentObj)
    {
        $paymentObj->setEbzcMethodId($methodId);
        $paymentObj->setAdditionalInformation('ebzc_method_id', $methodId);
        $this->savedMethodId = $methodId;
        $this->recurringMethodId = $methodId;
    }

    /**
     * @param $customerId
     * @param $paymentObj
     * @return bool
     */
    private function addCustomerAndRunCustomerTransaction($customerId, $paymentObj)
    {
        try {
            $customerResult = $this->getClient()->AddCustomer([
                'securityToken' => $this->getUeSecurityToken(),
                'customer' => $this->getCustomerData($customerId)
            ]);

            $ebizCustomer = $customerResult->AddCustomerResult;
            //add customer payment method
            $paymentMethodId = $this->addCustomerPaymentMethod($ebizCustomer->CustomerInternalId,
                $this->getCustomerPayment());
            $this->setPaymentMethodId($paymentMethodId, $paymentObj);

            // GetCustomerToken by calling GetCustomerToken function
            $ebizCustomerNumber = $this->getCustomerToken($ebizCustomer->CustomerId);

            // add token in ebizcharge_token table
            $tokenModel = $this->tokenFactory->create();
            $tokenModel->setMageCustId((int)$customerId);
            $tokenModel->setEbzcCustId((int)$ebizCustomerNumber);
            $this->tokenRepository->save($tokenModel);

            $paymentObj->setAdditionalInformation('ebzc_cust_id', $ebizCustomerNumber);
            $this->runUpdateQueryCustomer('customer_entity', 'cust', 1, $ebizCustomer->CustomerInternalId,
                $ebizCustomer->CustomerId, $ebizCustomerNumber, $customerId);

            // run Customer Transaction using saved payment method
            $this->savedProcess($ebizCustomerNumber, $paymentMethodId, $paymentObj);

        } catch (\Exception $e) {
            throw new LocalizedException(__('SoapFault: ' . __METHOD__ . $e->getMessage()));
        }

        return false;
    }

    /**
     * Add new payment method and process the transaction
     * A user is logged in option is existing customer only AddCustomerPaymentMethodProfile
     * @param $customerId
     * @param $ebzCustomerId
     * @param $paymentObj
     * @return bool
     */
    function newPaymentProcess($customerId, $ebzCustomerId, $paymentObj)
    {
        try {
            $customer = $this->getCustomer($customerId);

            # Case 2 Local = Yes, Live = No
            if($customer == null) {
                $this->addCustomerAndRunCustomerTransaction($customerId, $paymentObj);

            } elseif($customer && ($customer->CustomerToken == $ebzCustomerId)) {
                # Case 5 Local = Yes, Live = Yes , Token = Same
                try {
                    $paymentMethodId = $this->addCustomerPaymentMethod($customer->CustomerInternalId,
                        $this->getCustomerPayment());

                    $this->setPaymentMethodId($paymentMethodId, $paymentObj);
                    // run customer transaction
                    return $this->savedProcess($ebzCustomerId, $paymentMethodId, $paymentObj);

                } catch (\Exception $ex) {
                    throw new LocalizedException(__('newPaymentProcess: ' . __METHOD__ . $ex->getMessage()));
                }

            } elseif($customer && ($customer->CustomerToken != $ebzCustomerId)) {
                # Case 4 Local = Yes, Live = Yes , Token = Different
                throw new LocalizedException(__(__METHOD__ . ': Customer already exist and token mismatch.'));
            } else {
                # Case 6 In all other cases default
                throw new LocalizedException(__(__METHOD__ . ': Error in adding process.'));
            }

        } catch (\Exception $ex) {
            throw new LocalizedException(__('SoapFault: ' . __METHOD__ . $ex->getMessage()));
        }

        return false;
    }

    /**
     * Process a transaction from saved payment method
     * A user is logged in option is existing customer pay from saved payment methods
     * @param int $ebzcCustomerId EBizCharge Customer ID
     * @param int $ebzcMethodId EBizCharge Payment method ID
     * @return boolean
     */
    public function savedProcess($ebzcCustomerId, $ebzcMethodId, $payment)
    {
        try {
            $transactionResult = $this->getClient()->runCustomerTransaction(
                array(
                    'securityToken' => $this->getUeSecurityToken(),
                    'custNum' => $ebzcCustomerId,
                    'paymentMethodID' => $ebzcMethodId,
                    'tran' => $this->getCustomerTransactionRequest()
                ));

            $transaction = $transactionResult->runCustomerTransactionResult;

            if(isset($transaction)) {
                $transactionApproved = $this->setTransactionResult($transaction);

                if($transactionApproved && $this->config->isRecurringEnabled() == 1) {
                    $this->runRecurring($payment);
                }
                return $transactionApproved;
            }

        } catch (\Exception $ex) {
            throw new LocalizedException(__('savedProcess ' . $ex->getMessage()));
        }

        return false;
    }

    /**
     * @param $ebzcCustomerId
     * @param $ebzcMethodId
     * @return null|mixed
     */
    public function getCustomerPaymentMethodProfile($ebzcCustomerId, $ebzcMethodId)
    {
        try {
            $paymentMethod = $this->getClient()->getCustomerPaymentMethodProfile(
                array(
                    'securityToken' => $this->getUeSecurityToken(),
                    'customerToken' => $ebzcCustomerId,
                    'paymentMethodId' => $ebzcMethodId,
                ));

            if(isset($paymentMethod->GetCustomerPaymentMethodProfileResult)) {
                return $paymentMethod->GetCustomerPaymentMethodProfileResult;
            }

            return null;

        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * @param $customerToken
     * @return array|null
     */
    public function getCustomerPaymentMethods($customerToken)
    {
        if(!empty($customerToken)) {
            try {
                $methodProfiles = $this->getClient()->getCustomerPaymentMethodProfiles(
                    array(
                        'securityToken' => $this->getUeSecurityToken(),
                        'customerToken' => $customerToken
                    ));

                if(!isset ($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile)) {
                    $paymentMethods = array();
                } elseif(
                    (is_array($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile))
                    &&
                    (count($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile))
                    > 1) {

                    $paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;

                } else {
                    $paymentMethods[] = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;
                }
                return $paymentMethods;

            } catch (\Exception $ex) {
                return [];
                //throw new \Magento\Framework\Exception\LocalizedException(__('SoapFault: ' . $ex->getMessage()));
            }
        }

        return [];
    }

    public function getSavedAccounts($customerToken)
    {
        if(!empty($customerToken)) {

            $paymentMethods = $this->getCustomerPaymentMethods($customerToken);

            $accounts = array();
            foreach ($paymentMethods as $key => $payment) {
                if($payment->MethodType == 'check') {
                    $accounts[] = $payment;
                }
            }

            return $accounts;
        }

        return [];
    }

    /**
     * @param $customerToken
     * @param $methodId
     * @return bool
     */
    public function setDefaultPaymentMethod($customerToken, $methodId)
    {
        $setDefaultMethod = $this->getClient()->SetDefaultCustomerPaymentMethodProfile(
            array(
                'securityToken' => $this->getUeSecurityToken(),
                'customerToken' => $customerToken,
                'paymentMethodId' => $methodId
            ));

        if(isset($setDefaultMethod->SetDefaultCustomerPaymentMethodProfileResult)) {
            return true;
        }

        return false;
    }

    /**
     * Function Change #5 Ebiz Method Senario #5
     * a user is logged in option is existing customer pay from saved payment methods and update card details
     *
     * @param $ebzcCustomerId
     * @param $ebzcMethodId
     * @param $payment
     * @return bool
     * @throws LocalizedException
     */
    public function updateProcess($ebzcCustomerId, $ebzcMethodId, $payment)
    {
        $ueSecurityToken = $this->getUeSecurityToken();

        try {
            $paymentMethodProfile = $this->getCustomerPaymentMethodProfile($ebzcCustomerId, $ebzcMethodId);

            $paymentMethodProfile->AccountHolderName = !empty($paymentMethodProfile->AccountHolderName)
                ? $paymentMethodProfile->AccountHolderName
                : $payment->getCcOwner();
            $paymentMethodProfile->CardNumber = 'XXXXXX' . substr($paymentMethodProfile->CardNumber, 6);
            $paymentMethodProfile->CardExpiration = $payment->getCcExpYear() . '-' . $payment->getCcExpMonth();

            if($payment->getEbzcAvsStreet() != null) {
                $paymentMethodProfile->AvsStreet = $payment->getEbzcAvsStreet();
            } else {
                if($payment->getAdditionalInformation('ebzc_avs_street') != null) {
                    $paymentMethodProfile->AvsStreet = $payment->getAdditionalInformation('ebzc_avs_street');
                } else {
                    $paymentMethodProfile->AvsStreet = $this->billstreet;
                }
            }

            if($payment->getEbzcAvsZip() != null) {
                $paymentMethodProfile->AvsZip = $payment->getEbzcAvsZip();
            } else {
                if($payment->getAdditionalInformation('ebzc_avs_zip') != null) {
                    $paymentMethodProfile->AvsZip = $payment->getAdditionalInformation('ebzc_avs_zip');
                } else {
                    $paymentMethodProfile->AvsZip = $this->billzip;
                }
            }

            $updatedMethodProfile = $this->getClient()->updateCustomerPaymentMethodProfile(
                array(
                    'securityToken' => $ueSecurityToken,
                    'customerToken' => $ebzcCustomerId,
                    'paymentMethodProfile' => $paymentMethodProfile,
                ));

            if(isset($updatedMethodProfile->UpdateCustomerPaymentMethodProfileResult)) {
                return $this->savedProcess($ebzcCustomerId, $ebzcMethodId, $payment);

            } else {
                throw new LocalizedException(__('Unable to update card.'));
            }


        } catch (\Exception $ex) {
            throw new LocalizedException(__('SoapFault: ' . $ex->getMessage()));
        }
    }

    /**
     * Run Customer payment transaction for One ResultCode
     * @throws LocalizedException
     */
    public function runTransaction()
    {
        try {
            $transaction = $this->getClient()->runTransaction(
                array(
                    'securityToken' => $this->getUeSecurityToken(),
                    'tran' => $this->getTransactionRequest()
                ));

            if(!empty($transactionResult = $transaction->runTransactionResult)) {
                return $this->setTransactionResult($transactionResult);
            }

        } catch (\Exception $ex) {
            throw new LocalizedException(__('SoapFault: ' . $ex->getMessage()));
        }

        return false;
    }

    /**
     * Run Customer payment transaction for One ResultCode
     * @throws LocalizedException
     */
    public function runCustomerTransaction($ebzcCustomerId, $ebzcMethodId, $payment, $recurringOnOff)
    {
        $getCustomerTransactionRequest = $this->getCustomerTransactionRequest();

        try {
            if($this->config->isRecurringEnabled() == 1) {

                if((!empty($payment->getExcludeAmount()))
                    && ($payment->getExcludeAmount() > 0)
                    && ($recurringOnOff == 1)) {

                    $transactionRequest = $this->getCustomerTransactionRequest();

                    $transactionRequest['Details']['Amount'] = ($transactionRequest['Details']['Amount'] - $payment->getExcludeAmount());

                    $transactionRequest['Details']['Description'] = 'This is recurring order #' .
                        $transactionRequest['Details']['OrderID'] . ' Amount [' .
                        $payment->getExcludeAmount() . '] already paid. Remaining amount will be charge now.';

                    $transactionResult = $this->getClient()->runCustomerTransaction(
                        array(
                            'securityToken' => $this->getUeSecurityToken(),
                            'custNum' => $ebzcCustomerId,
                            'paymentMethodID' => $ebzcMethodId,
                            'tran' => $transactionRequest
                        ));

                    $transaction = $transactionResult->runCustomerTransactionResult;

                    if(isset($transaction)) {
                        return $this->setTransactionResult($transaction);
                    } else {
                        return false;
                    }

                } else {
                    return false;
                }
            }

        } catch (\Exception $ex) {
            throw new LocalizedException(__('runCustomerTransaction: ' . $ex->getMessage()));
        }

        return false;
    }

    /**
     * Refund previous transaction
     * @return bool
     * @throws LocalizedException
     */
    public function refundTransaction()
    {
        try {

            $transaction = $this->getClient()->runTransaction(
                [
                    'securityToken' => $this->getUeSecurityToken(),
                    'tran' => $this->getTransactionRequest()
                ]
            );

            $transaction = $transaction->runTransactionResult;

            $this->result = $transaction->Result;
            $this->resultcode = $transaction->ResultCode;
            $this->authcode = $transaction->AuthCode;
            // Caused refund issue. Commented out on 12/13/16
            //$this->refnum = $transaction->RefNum;
            $this->batch = $transaction->BatchNum;
            $this->avs_result = $transaction->AvsResult;
            $this->avs_result_code = $transaction->AvsResultCode;
            $this->cvv2_result = $transaction->CardCodeResult;
            $this->cvv2_result_code = $transaction->CardCodeResultCode;
            $this->vpas_result_code = $transaction->VpasResultCode;
            $this->convertedamount = $transaction->ConvertedAmount;
            $this->convertedamountcurrency = $transaction->ConvertedAmountCurrency;
            $this->conversionrate = $transaction->ConversionRate;
            $this->error = $transaction->Error;
            $this->errorcode = $transaction->ErrorCode;
            $this->custnum = $transaction->CustNum;

            // Obsolete variable (for backward compatibility) At some point they will no longer be set.
            $this->avs = $transaction->AvsResult;
            $this->cvv2 = $transaction->CardCodeResult;

            $this->cctransid = $transaction->RefNum;
            $this->acsurl = $transaction->AcsUrl;
            $this->pareq = $transaction->Payload;

            if($this->resultcode == 'A') {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            throw new LocalizedException(__('SoapFault: ' . $ex->getMessage()));
        }
    }

    /**
     * @param $queryParameters
     * @return array
     */
    public function runSelectQuery($queryParameters)
    {
        $resource = $this->config->getQueryResourceConnection();
        $tableNamef = $resource->getTableName($queryParameters['tableName']); // add table name along with prefix
        $sql = $resource->getConnection()->select()
            ->from($tableNamef, $queryParameters['tableFields']) // to select all fields remove fields part
            ->where($queryParameters['whereKey'] . ' = ?', $queryParameters['whereValue']);

        return $resource->getConnection()->fetchAll($sql);
    }

    public function runSelectQueryMultipleInput($queryParameters, $noOfFilters)
    {
        $resource = $this->config->getQueryResourceConnection();
        $tableNamef = $resource->getTableName($queryParameters['tableName']); // add table name along with prefix
        if($noOfFilters == 1) {

            $sql = $resource->getConnection()->select()
                ->from($tableNamef, $queryParameters['tableFields']) // to select all fields remove fields part
                ->where($queryParameters['whereKey'] . ' = ?', $queryParameters['whereValue']);
        } elseif($noOfFilters == 2) {
            $sql = $resource->getConnection()->select()
                ->from($tableNamef, $queryParameters['tableFields']) // to select all fields remove fields part
                ->where($queryParameters['whereKey'] . ' = ?', $queryParameters['whereValue'])
                ->where($queryParameters['whereKey2'] . ' = ?', $queryParameters['whereValue2']);
        } elseif($noOfFilters == 3) {
            $sql = $resource->getConnection()->select()
                ->from($tableNamef, $queryParameters['tableFields']) // to select all fields remove fields part
                ->where($queryParameters['whereKey'] . ' = ?', $queryParameters['whereValue'])
                ->where($queryParameters['whereKey2'] . ' = ?', $queryParameters['whereValue2'])
                ->where($queryParameters['whereKey3'] . ' = ?', $queryParameters['whereValue3']);
        } elseif($noOfFilters == 4) {
            $sql = $resource->getConnection()->select()
                ->from($tableNamef, $queryParameters['tableFields']) // to select all fields remove fields part
                ->where($queryParameters['whereKey'] . ' = ?', $queryParameters['whereValue'])
                ->where($queryParameters['whereKey2'] . ' = ?', $queryParameters['whereValue2'])
                ->where($queryParameters['whereKey3'] . ' = ?', $queryParameters['whereValue3'])
                ->where($queryParameters['whereKey4'] . ' = ?', $queryParameters['whereValue4']);
        } elseif($noOfFilters == 5) {
            $sql = $resource->getConnection()->select()
                ->from($tableNamef, $queryParameters['tableFields']) // to select all fields remove fields part
                ->where($queryParameters['whereKey'] . ' = ?', $queryParameters['whereValue'])
                ->where($queryParameters['whereKey2'] . ' = ?', $queryParameters['whereValue2'])
                ->where($queryParameters['whereKey3'] . ' = ?', $queryParameters['whereValue3'])
                ->where($queryParameters['whereKey4'] . ' = ?', $queryParameters['whereValue4'])
                ->where($queryParameters['whereKey5'] . ' = ?', $queryParameters['whereValue5']);
        } else {
            $sql = $resource->getConnection()->select()
                ->from($tableNamef, $queryParameters['tableFields']) // to select all fields remove fields part
                ->where($queryParameters['whereKey'] . ' = ?', $queryParameters['whereValue']);
        }

        return $resource->getConnection()->fetchAll($sql);
    }

    public function runSelectQueryTwoFields($queryParameters)
    {
        $resource = $this->config->getQueryResourceConnection();
        $tableNamef = $resource->getTableName($queryParameters['tableName']); // add table name along with prefix
        $sql = $resource->getConnection()->select()
            ->from($tableNamef, $queryParameters['tableFields']) // to select all fields remove fields part
            ->where($queryParameters['whereKey'] . ' = ?', $queryParameters['whereValue'])
            ->where($queryParameters['whereKey2'] . ' = ?', $queryParameters['whereValue2']);

        return $resource->getConnection()->fetchAll($sql);
    }

    /**
     * @param $queryParameters
     * @return mixed
     */
    public function runInsertQuery($queryParameters)
    {
        $resource = $this->config->getQueryResourceConnection();
        $tableName = $resource->getTableName($queryParameters['tableName']);
        $connection = $resource->getConnection();
        $connection->insert($tableName, $queryParameters['data']);

        return $connection->lastInsertId($tableName);
    }

    public function runBulkInsertQuery($tableName, $data)
    {
        $resource = $this->config->getQueryResourceConnection();
        $tableName = $resource->getTableName($tableName);

        $resource->getConnection()->insertMultiple($tableName, $data);
    }

    /**
     * @param $queryParameters
     * @return int
     */
    public function runUpdateQuery($queryParameters)
    {
        $resource = $this->config->getQueryResourceConnection();
        $tableNamef = $resource->getTableName($queryParameters['tableName']);
        return $resource->getConnection()->update($tableNamef, $queryParameters['data'], $queryParameters['where']);
    }

    public function strToTime($data, $plusYears = '')
    {
        if(!empty($plusYears)) {
            $date = date("Y-m-d", strtotime("+" . $plusYears . " years", strtotime($data)));
        } else {
            $date = date("Y-m-d", strtotime($data));
        }
        return $date;
    }

    public function getMagentoCustomer($customerId, $colums)
    {
        $selectQueryParameters = array(
            'option' => 'select',
            'tableName' => 'customer_entity',
            'tableFields' => $colums,
            'whereKey' => 'ec_cust_id',
            'whereValue' => $customerId
        );

        $customerData = $this->runSelectQuery($selectQueryParameters);

        if(empty($customerData)) {
            $selectQueryParametersE = array(
                'option' => 'select',
                'tableName' => 'customer_entity',
                'tableFields' => $colums,
                'whereKey' => 'entity_id',
                'whereValue' => $customerId
            );

            $customerData = $this->runSelectQuery($selectQueryParametersE);
        }

        return $customerData;
    }

    /**
     * run recurring payments for EBizCharge
     * @param $payment
     * @return $this
     */
    public function runRecurring($payment)
    {
        sleep(5);

        $order = $payment->getOrder();

        foreach ($order->getAllVisibleItems() as $item) {
            $itemOptions = $item->getProductOptions();

            if(isset($itemOptions['info_buyRequest']['recurring']) && !empty($itemOptions['info_buyRequest']['recurring']['rec_activate'])) {

                $itemRecurringBuyRequest = $itemOptions['info_buyRequest'];
                $itemRecurringOptions = $itemOptions['info_buyRequest']['recurring'];
                $itemRecurringQty = $itemOptions['info_buyRequest']['qty'];
                $getMainProductId = $item->getProductId();
                $getParentProductId = (!empty($itemRecurringBuyRequest['product'])) ? $itemRecurringBuyRequest['product'] : $getMainProductId;
                $getChildProductId = (!empty($itemRecurringBuyRequest['currentpid'])) ? $itemRecurringBuyRequest['currentpid'] : $getMainProductId;
                $getProductSimpleName = (!empty($itemOptions['simple_name'])) ? $itemOptions['simple_name'] : $item->getName();
                $recActivateValue = $itemRecurringOptions['rec_activate'];
                $recSdate = $this->strToTime($itemRecurringOptions['sdate']);
                $recEdate = isset($itemRecurringOptions['edate']) ? $itemRecurringOptions['edate'] : null;
                $itemPrice = (!empty($item->getSpecialPrice())) ? $item->getSpecialPrice() : $item->getPrice();
                // apply coupon discount .. we need to apply current price in recurring orders, how this work in recurring orders
                $itemDiscountAmount = (!empty($item->getDiscountAmount())) ? $item->getDiscountAmount() : 0;
                $singleItemDiscountAmount = ($itemDiscountAmount / $item->getQtyOrdered());
                $recurringItemTotalDiscount = ($singleItemDiscountAmount * $itemRecurringQty);
                $recurringItemAmountBeforeDisc = ($itemPrice * $itemRecurringQty);
                $recurringItemFinalAmount = ($recurringItemAmountBeforeDisc - $recurringItemTotalDiscount);

                if(($recActivateValue == 1) && ($recurringItemFinalAmount > 0) || ($item->getProductType() == 'virtual')) {
                    if(!empty($itemRecurringOptions['rec_frequency'])) {
                        $recurringBilling = array(
                            'Amount' => $recurringItemFinalAmount,
                            'Tax' => ($item->getTaxAmount() * $itemRecurringQty),
                            'Enabled' => true,
                            'Start' => $recSdate,
                            'Schedule' => $itemRecurringOptions['rec_frequency'],
                            'ScheduleName' => $getChildProductId . '-' . $this->orderid . '-' . $order->getCustomerId() . '-' . $itemRecurringOptions['rec_frequency'] . '-' . $this->recurringMethodId,
                            'ReceiptNote' => 'Item [' . $getChildProductId . '-' . $getProductSimpleName . '] recurring payment added.',
                            'ReceiptTemplateName' => false,
                            'SendCustomerReceipt' => true
                        );
                    }

                    if((!empty($recEdate)) || ($recEdate != null)) {
                        $recurringBilling['Expire'] = $this->strToTime($recEdate);
                        $recurringBilling['Next'] = $recSdate;
                        $recIndefinitely = isset($itemRecurringOptions['rec_indefinitely']) ? 1 : 0;
                    } else {
                        $recurringBilling['Expire'] = $this->strToTime($itemRecurringOptions['sdate'], '10');
                        $recurringBilling['Next'] = $recSdate;
                        $recIndefinitely = '1';
                    }

                    $this->addRecurring($getChildProductId, $getParentProductId, $getProductSimpleName,
                        $itemRecurringQty, $recurringBilling, $recIndefinitely, $order);

                } else {
                    $this->ebizLog()->info('Subscription not added because product(' . $getProductSimpleName . ') price is: ' . $recurringItemFinalAmount);
                }
            }
        }

        return $this;
    }

    /**
     * Adding recurring to EBizCharge
     */
    public function addRecurring(
        $getChildProductId,
        $getParentProductId,
        $getProductSimpleName,
        $itemRecurringQty,
        $recurringBilling,
        $recIndefinitely,
        $order
    ) {
        $customer = $this->getCustomer($order->getCustomerId());
        if($customer == null) {
            $this->ebizLog()->info('Recurring not added because customer Not found. Id: ' . $order->getCustomerId());
            return false;
        }

        try {
            $paymentMethodName = $this->getRecurringPaymentMethodName($customer);

            $addRecurringParameters = array(
                'securityToken' => $this->getUeSecurityToken(),
                'customerInternalId' => $customer->CustomerInternalId,
                'paymentMethodProfileId' => $this->recurringMethodId,
                'recurringBilling' => $recurringBilling
            );

            $transaction = $this->getClient()->ScheduleRecurringPayment($addRecurringParameters);

            if(!empty($scheduledPaymentInternalId = $transaction->ScheduleRecurringPaymentResult)) {

                $customerShippingAddressId = '';
                $customerBillingAddressId = '';

                if(!empty($order->getShippingAddress())) {
                    $customerShippingAddressId = $order->getShippingAddress()->getCustomerAddressId();
                }
                if(!empty($order->getBillingAddress())) {
                    $customerBillingAddressId = $order->getBillingAddress()->getCustomerAddressId();
                }

                $amount = $recurringBilling['Amount'];
                $shippingMethodName = $order->getShippingMethod();

                $recurringDates = $this->getRecurringScheduledDates($scheduledPaymentInternalId);

                $insertQueryParameters = array(
                    'option' => 'insert',
                    'tableName' => 'ebizcharge_recurring',
                    'data' => array(
                        'rec_status' => 0,
                        'rec_indefinitely' => $recIndefinitely,
                        'mage_cust_id' => $this->custid,
                        'mage_order_id' => $this->orderid,
                        'mage_item_id' => $getChildProductId,
                        'mage_parent_item_id' => $getParentProductId,
                        'mage_item_name' => $getProductSimpleName,
                        'qty_ordered' => $itemRecurringQty,
                        'eb_rec_start_date' => $recurringBilling['Start'],
                        'eb_rec_end_date' => $recurringBilling['Expire'],
                        'eb_rec_frequency' => $recurringBilling['Schedule'],
                        'eb_rec_method_id' => $this->recurringMethodId,
                        'eb_rec_scheduled_payment_internal_id' => $scheduledPaymentInternalId,
                        'eb_rec_total' => count($recurringDates),
                        'eb_rec_processed' => 0,
                        'eb_rec_next' => $this->getNextRecurringDate($recurringDates),
                        'eb_rec_remaining' => count($recurringDates),
                        'eb_rec_due_dates' => serialize($recurringDates),
                        'shipping_address_id' => $customerShippingAddressId,
                        'billing_address_id' => $customerBillingAddressId,
                        'amount' => $amount,
                        'payment_method_name' => $paymentMethodName,
                        'shipping_method' => $shippingMethodName,
                    )
                );

                $recurringId = $this->runInsertQuery($insertQueryParameters);

                $this->insertScheduleDates($recurringId, $recurringDates);
            }

        } catch (\Exception $ex) {
            throw new LocalizedException(__('addRecurring ' . $ex->getMessage()));
        }

        return $this;
    }

    public function getNextRecurringDate($recurringDates)
    {
        if(isset($recurringDates[0]) && $recurringDates[0] == date('Y-m-d')) {
            return $recurringDates[1] ?? null;
        } else {
            return $recurringDates[0] ?? null;
        }
    }

    public function getRecurringPaymentMethodName($ebizCustomer)
    {
        // get recurring payment method name
        $paymentMethodName = '';
        $profiles = $ebizCustomer->PaymentMethodProfiles->PaymentMethodProfile;

        if(is_object($profiles)) {
            $paymentMethods[] = $profiles;
        } else {
            $paymentMethods = $profiles;
        }

        foreach ($paymentMethods as $paymentMethod) {
            if($paymentMethod->MethodID == $this->recurringMethodId) {
                $paymentMethodName = $paymentMethod->MethodName;
                break;
            }
        }

        return $paymentMethodName;
    }

    /**
     * @throws LocalizedException
     */
    public function getRecurringScheduledDates($schedulePaymentId)
    {
        try {
            $transaction = $this->getClient()->GetScheduledDates(
                [
                    'securityToken' => $this->getUeSecurityToken(),
                    'scheduledPaymentInternalId' => $schedulePaymentId,
                ]
            );

            if(!empty($transaction->GetScheduledDatesResult)) {
                $dates = $transaction->GetScheduledDatesResult->ScheduledDates;
                return json_decode($dates);
            }

        } catch (\Exception $ex) {
            throw new LocalizedException(__(__METHOD__ . $ex->getMessage()));
        }

        return [];
    }

    /**
     * @param $recurringId
     * @param $recurringDates
     */
    public function insertScheduleDates($recurringId, $recurringDates)
    {
        if(is_array($recurringDates) && !empty($recurringDates)) {
            $dates = [];
            foreach ($recurringDates as $date) {
                $dates[] = [
                    'recurring_id' => $recurringId,
                    'recurring_date' => $date,
                ];
            }
            // delete all existing dates first
            $this->deleteRecurringScheduleDates($recurringId);

            $this->runBulkInsertQuery('ebizcharge_recurring_dates', $dates);
        }
    }

    /**
     * @param $recurringId
     */
    public function deleteRecurringScheduleDates($recurringId)
    {
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName('ebizcharge_recurring_dates');
        return $connection->delete($tableName, ['recurring_id = ?' => $recurringId,]);
    }

    /**
     * @param $methodId
     * @param $schedulePaymentInternalId
     * @return bool
     */
    public function modifyRecurringPaymentMethod($methodId, $schedulePaymentInternalId)
    {
        $paymentMethodProfile = array(
            'securityToken' => $this->getUeSecurityToken(),
            'scheduledPaymentInternalId' => trim($schedulePaymentInternalId),
            'paymentMethodProfileId' => $methodId
        );

        $paymentMethodProfileResponse = $this->getClient()->ModifyScheduledRecurringPayment_PaymentMethodProfile($paymentMethodProfile);
        $paymentMethodProfileResult = $paymentMethodProfileResponse->ModifyScheduledRecurringPayment_PaymentMethodProfileResult;
        if(isset($paymentMethodProfileResult)) {
            return $paymentMethodProfileResult->StatusCode;
        }

        return false;
    }

    /**
     * @param $customerId
     * @param $customerInternalId
     * @param $schedulePaymentId
     * @return mixed|null
     */
    public function getSearchScheduledRecurringPayments($customerId, $customerInternalId, $schedulePaymentId)
    {
        try {
            $response = $this->getClient()->SearchScheduledRecurringPayments(
                array(
                    'securityToken' => $this->getUeSecurityToken(),
                    'customerInternalId' => $customerInternalId,
                    'customerId' => $customerId,
                    'start' => 0,
                    'limit' => 900,
                ));

            if(!isset ($response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails)) {
                $recurringDetail = array();
            } elseif(
                (is_array($response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails))
                &&
                (count($response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails))
                > 1) {
                $recurringDetail = $response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails;
            } else {
                $recurringDetail[] = $response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails;
            }

            if(!empty($recurringDetail)) {
                $key = array_search($schedulePaymentId, array_column($recurringDetail, 'ScheduledPaymentInternalId'));
                return $paymentMethods = $recurringDetail[$key] ?? null;
            }
        } catch (\Exception $ex) {
            $this->ebizLog()->info(__METHOD__ . $ex->getMessage());
            return null;
        }

        return null;
    }

    /**
     * @param null $customerId
     * @param int $start
     * @param int $limit
     * @param bool|string $createdDate
     * @return array
     */
    public function getSearchTransactions($customerId = null, $start = 0, $limit = 1000, $createdDate = false)
    {
        $date = $createdDate ? date('Y-m-d', strtotime($createdDate)) : '2021-01-15';

        try {
            $filterClerk = array(
                'FieldName' => 'Clerk',
                'ComparisonOperator' => 'eq',
                'FieldValue' => 'Recurring'
            );
            $filterStart = array(
                'FieldName' => 'created',
                'ComparisonOperator' => 'gt',
                'FieldValue' => $date
            );
            $searchFilters['SearchFilter'][0] = $filterClerk;
            $searchFilters['SearchFilter'][1] = $filterStart;

            if(!empty($customerId)) {
                $filterCustomer = array(
                    'FieldName' => 'CustID',
                    'ComparisonOperator' => 'eq',
                    'FieldValue' => $customerId
                );

                $searchFilters['SearchFilter'][2] = $filterCustomer;
            }

            $searchTransactionsReq = array(
                'securityToken' => $this->getUeSecurityToken(),
                'filters' => $searchFilters,
                'matchAll' => 1,
                'countOnly' => 0,
                'start' => $start,
                'limit' => $limit,
                'sort' => 'DateTime DESC'
            );

            $response = $this->getClient()->SearchTransactions($searchTransactionsReq);

            if(!isset ($response->SearchTransactionsResult->Transactions->TransactionObject)) {
                $recurringDetail = array();
            } elseif(
                (is_array($response->SearchTransactionsResult->Transactions->TransactionObject))
                &&
                (count($response->SearchTransactionsResult->Transactions->TransactionObject))
                > 1) {
                $recurringDetail = $response->SearchTransactionsResult->Transactions->TransactionObject;
            } else {
                $recurringDetail[] = $response->SearchTransactionsResult->Transactions->TransactionObject;
            }
            return $recurringDetail;

        } catch (\Exception $ex) {
            $this->ebizLog()->info(__METHOD__ . $ex->getMessage());
            return [];
        }
    }

    /**
     * @return string
     */
    public function getReceiptRefNumber()
    {
        $receiptsList = array(
            'securityToken' => $this->getUeSecurityToken(),
            'receiptType' => 'email'
        );

        $receiptsList = $this->getClient()->GetReceiptsList($receiptsList);
        $getReceiptsListResult = $receiptsList->GetReceiptsListResult;
        $needle = 'Transaction API and Payment Form (Customer)';
        $receiptRefNum = '';
        if(isset($getReceiptsListResult)) {
            foreach ($getReceiptsListResult as $array) {
                foreach ($array as $item) {
                    if($item->Name == $needle) {
                        $receiptRefNum = $item->ReceiptRefNum;
                        break;
                    }
                }
            }
        }

        return $receiptRefNum;
    }

    /**
     * @param $recurring
     * @param $status
     */
    public function suspendScheduledRecurringPaymentStatus($recurring, $status)
    {
        try {
            $scheduledPaymentInternalId = $recurring->getData('eb_rec_scheduled_payment_internal_id');
            $scheduledRecurringPaymentStatus = $this->getClient()->ModifyScheduledRecurringPaymentStatus(
                array(
                    'securityToken' => $this->getUeSecurityToken(),
                    'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
                    'statusId' => $status
                ));

            $scheduledRecurringStatusResult = $scheduledRecurringPaymentStatus->ModifyScheduledRecurringPaymentStatusResult;

            if(!empty($scheduledRecurringStatusResult) && $scheduledRecurringStatusResult->StatusCode == 1) {
                return true;
            }
            $this->ebizCronLog()->info($scheduledPaymentInternalId . ' - ScheduledPaymentInternalId is not suspended!');
        } catch (\Exception $ex) {
            $this->ebizCronLog()->crit($ex->getMessage());
        }
        return false;
    }

    /**
     * @param $customerId
     * @param $scheduledPaymentInternalId
     * @param $orderDate
     * @return string|null
     */
    public function searchRecurringPayment($customerId, $scheduledPaymentInternalId, $orderDate)
    {
        try {
            // Get full schedule
            $parametersSearch = array(
                'securityToken' => $this->getUeSecurityToken(),
                'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
                'customerId' => $customerId,
                'fromDateTime' => '2020-11-01',
                'toDateTime' => date('Y-m-d'),
                'start' => 0,
                'limit' => 1000
            );
            $searchRecurringPayments = $this->getClient()->SearchRecurringPayments($parametersSearch);

            $recurringPaymentsResult = $searchRecurringPayments->SearchRecurringPaymentsResult;
            if(!empty($recurringPaymentsResult)) {

                if(isset($recurringPaymentsResult->Payment)) {

                    $payments = $recurringPaymentsResult->Payment;

                    if(is_object($payments)) {
                        $paymentData[] = $payments;
                    } else {
                        $paymentData = $payments;
                    }

                    foreach ($paymentData as $payment) {
                        if($orderDate == date('Y-m-d', strtotime($payment->DatePaid))) {
                            $paymentInternalId = $payment->PaymentInternalId;
                            return $paymentInternalId;
                        }
                    }

                } else {
                    $this->ebizCronLog()->info('No payment found against scheduledPaymentInternalId = ' . $scheduledPaymentInternalId);
                }

            } else {
                $this->ebizLog()->info('No payment found against scheduledPaymentInternalId = ' . $scheduledPaymentInternalId);
            }

            return null;
        } catch (\Exception $ex) {
            $this->ebizCronLog()->info(__METHOD__ . $ex->getMessage());
        }

    }

    /**
     * @param $paymentInternalId
     */
    public function markRecurringPaymentAsApplied($paymentInternalId)
    {
        try {
            // Get full schedule
            $parametersPayment = array(
                'securityToken' => $this->getUeSecurityToken(),
                'paymentInternalId' => $paymentInternalId
            );

            $markRecurringPaymentAsApplied = $this->getClient()->MarkRecurringPaymentAsApplied($parametersPayment);
            $markRecurringPaymentAsAppliedResult = $markRecurringPaymentAsApplied->MarkRecurringPaymentAsAppliedResult;

            if(!empty($markRecurringPaymentAsAppliedResult)) {
                if($markRecurringPaymentAsAppliedResult->StatusCode == 1) {
                    $this->ebizCronLog()->info('Payment is marked as applied against PaymentInternalId = ' . $paymentInternalId);
                } else {
                    $this->ebizCronLog()->info('Payment is not marked as applied against PaymentInternalId = ' . $paymentInternalId);
                }
            } else {
                $this->ebizCronLog()->info('Payment is not marked as applied against PaymentInternalId = ' . $paymentInternalId);
            }

        } catch (\Exception $ex) {
            $this->ebizCronLog()->info('There is an error in mark as applied process.');
        }

    }

    /**
     * This method sets authorization data for gateway request
     *
     * @param array $newAuthData
     */
    public function setAuthorizeData(array $newAuthData)
    {
        $this->authcode = $newAuthData['AuthCode'] ?? null;
        $this->refnum = $newAuthData['RefNum'] ?? null;
        $this->avs_result_code = $newAuthData['AvsResultCode'] ?? null;
        $this->cvv2_result_code = $newAuthData['CardCodeResultCode'] ?? null;
        $this->resultcode = $newAuthData['ResultCode'] ?? null;
        $this->result = $newAuthData['Result'] ?? null;
        $this->command = $newAuthData['TransactionType'] ?? null;

    }

    /**
     * This method returns payment gateway authorized data
     *
     * @return array
     */
    public function getAuthorizeData(): array
    {
        return [
            'authcode' => $this->authcode,
            'refnum' => $this->refnum,
            'avs_result_code' => $this->avs_result_code,
            'cvv2_result_code' => $this->cvv2_result_code,
            'resultcode' => $this->resultcode,
            'result' => $this->result,
            'command' => $this->command
        ];
    }

    /**
     * @param InfoInterface $payment
     * @param $amount
     */
    public function setPaymentData(InfoInterface $payment, $amount)
    {
        $this->cardholder = $payment->getCcOwner();
        $this->card = $payment->getCcNumber();
        $this->cardtype = $payment->getCcType();
        $this->exp = $payment->getCcExpMonth() . substr($payment->getCcExpYear(), 2, 2);
        $this->cvv2 = $payment->getCcCid();
        $this->amount = $amount;

        $this->achtype = $payment->getAdditionalInformation('ach_type');
        $this->achroute = $payment->getAdditionalInformation('ach_route');
    }

    /**
     * Set order data
     *
     * @param InfoInterface $payment
     */
    public function setOrderData(InfoInterface $payment)
    {
        $order = $payment->getOrder();
        $orderId = $order->getIncrementId();
        $this->invoice = $orderId;
        $this->orderid = $orderId;
        $this->ponum = $orderId;
        $this->ip = $order->getRemoteIp();
        $this->custid = $order->getCustomerId();
        $this->email = $order->getCustomerEmail();

        $this->tax = $order->getTaxAmount();
        $this->shipping = $order->getShippingAmount();

        // avs data
        list($avsstreet) = $order->getBillingAddress()->getStreet();
        $this->street = $avsstreet;
        $this->zip = $order->getBillingAddress()->getPostcode();

        $this->description = "Magento Order #" . $orderId;
        if($description = $this->config->getPaymentDescription()) {
            $this->description = str_replace('[orderid]', $orderId, $description);
        }

        // Set Recurring Values
        $this->recurringMethodId = $payment->getAdditionalInformation('ebzc_method_id');
    }

    /**
     * Get order general data
     *
     * @return array
     */
    public function getOrderData(): array
    {
        return [
            'orderId' => $this->orderid,
            'invoiceId' => $this->invoice,
            'customerId' => $this->custid,
            'customerEmail' => $this->email,
            'recurringMethodId' => $this->recurringMethodId,
            'ip' => $this->ip
        ];

    }

    /**
     * Set guest checkout
     */
    public function setGuestCustomer()
    {
        $this->custid = 'Guest';
    }

    /**
     * set command for payment gateway
     *
     * @param string $command
     */
    public function setCommand(string $command)
    {
        if(!empty($command)) {
            $this->command = $command;
        }
    }

    /**
     * Get payment error
     *
     * @return array
     */
    public function getPaymentError(): array
    {
        return [
            'error' => $this->error,
            'errorcode' => $this->errorcode
        ];
    }

    /**
     * set ach status
     *
     * @param bool $achStatus
     */
    public function setAchStatus(bool $achStatus)
    {
        $this->achStatus = $achStatus;
    }

    /**
     * get current ach status
     *
     * @return  bool
     */
    public function getAchStatus(): bool
    {
        return $this->achStatus;
    }

    /**
     * Overwrite data in the current object.
     *
     * @param string $property
     * @param mixed $value
     * @return void
     * @throws \Exception
     */
    public function setData(string $property, $value = null)
    {
        try {
            if(property_exists($this, $property)) {
                $this->$property = $value;
            }
        } catch (\Exception $e) {
            throw new \Exception(__('Provided property not exist'));
        }
    }

    /**
     * Get data from current object.
     *
     * @param string $property
     * @return $this
     * @throws \Exception
     */
    public function getData(string $property)
    {
        try {
            if(property_exists($this, $property)) {
                return $this->$property;
            }
        } catch (\Exception $e) {
            throw new \Exception(__('Provided property not exist'));
        }
    }


    //---------------- EBizCharge Methods End -----------
}
