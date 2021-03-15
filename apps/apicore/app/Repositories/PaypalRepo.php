<?php

namespace App\Repositories;

use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Common\PayPalModel;
use PayPal\PayPalAPI\BAUpdateRequestType;
use PayPal\PayPalAPI\BillAgreementUpdateReq;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

// paypal nvp/soap api sdk
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\PayPalAPI\CreateBillingAgreementReq;
use PayPal\PayPalAPI\CreateBillingAgreementRequestType;
use PayPal\PayPalAPI\DoReferenceTransactionReq;
use PayPal\PayPalAPI\DoReferenceTransactionRequestType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\EBLBaseComponents\DoReferenceTransactionRequestDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\PayPalAPI\GetBillingAgreementCustomerDetailsReq;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\Payment;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Api\PaymentExecution;


use PayPal\Api\VerifyWebhookSignature;
use PayPal\Api\WebhookEvent;
use PayPal\Api\Sale;


class PaypalRepo
{
    protected $NVPConfig;
    protected $apiContext;

    public function __construct()
    {
        $this->setApiContext();
    }


    public function setApiContext($nvp = false)
    {
        $paypal_id = env('PAYPAL_ID');
        $paypal_secret = env('PAYPAL_SECRET');
        $mode          = env('PAYPAL_MODE');

        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($paypal_id, $paypal_secret)
        );

        $log_path = dirname(__FILE__) . '/../../storage/logs/PayPal.log';

        $this->apiContext->setConfig(
            [
                'mode'           => $mode,
                 'log.LogEnabled' => true,
                 'log.FileName'   => $log_path,
                 'log.LogLevel'   => 'INFO', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                 'cache.enabled'  => true,
            ]
        );
    }

    /**
     * @param $url
     * @return string
     */
    private function getPayPalTokenFromUrl($url)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        return !empty($query['token']) ? $query['token'] : '';
    }


    /**
     * @param $money
     * @param $customData
     * @param $returnUrl
     * @param $cancelUrl
     * @return string
     * @throws Exception
     */
    public function doPayPalCharge($money,$customData, $returnUrl, $cancelUrl)
    {
        if (!isset($customData['order_id']) || !$customData['order_id']) {
            throw new \Exception('Invalid order id');
        }
        $apiContext = $this->apiContext;

        // Create new payer and method
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        // Set redirect urls
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl)
            ->setCancelUrl($cancelUrl);

        // Set payment amount
        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal($money);

        $smallCustomData = [
            'order_id' => $customData['order_id']
        ];
        $customStr = base64_encode(serialize($smallCustomData));

        // Set transaction object
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            //can not use json_encode here, because can not decode data from web hook
            ->setCustom($customStr) // Value too long (max length 256)
            ->setDescription("Payment description");

        // Create the full payment object
        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        // Create payment with valid API context
        try {
            $payment->create($apiContext);
            $token = $this->getPayPalTokenFromUrl($payment->getApprovalLink());

        } catch (PayPalConnectionException $e) {
            \Log::error('[PayPalConnectionException-doPayPalCharge]' . $e->getMessage());
//            $this->errorLog('[PayPalConnectionException-doPayPalCharge]' . $e->getMessage() . ' data:'.$e->getData(),4);
        } catch (\Exception $e) {
            \Log::error('[doPayPalCharge]' . $e->getMessage());
//            $this->errorLog('[doPayPalCharge]' . $e->getMessage(),4);
        }
        return isset($token) ? $token : '';
    }


    /**
     * @param $payment_id
     * @param $payer_id
     * @return null|Payment
     */
    public function confirmPayPalCharge($payment_id, $payer_id)
    {
        $apiContext = $this->apiContext;
        // Get payment object by passing paymentId
        $payment = Payment::get($payment_id, $apiContext);

        // Execute payment with payer id
        $execution = new PaymentExecution();
        $execution->setPayerId($payer_id);

        try
        {
            // Execute payment
            $payment = $payment->execute($execution, $apiContext);

        } catch (PayPalConnectionException $e)
        {
//            self::payPalLog($e, '[PayPalConnectionException]');
        } catch (\Exception $e)
        {
//            self::payPalLog($e);
        }

        return isset($payment) ? $payment : null;
    }


}