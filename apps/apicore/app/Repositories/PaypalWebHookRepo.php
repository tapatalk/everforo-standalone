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


class PaypalWebHookRepo
{
    protected $NVPConfig;

    /** @var string(json) */
    protected $bodyReceived;
    /** @var PayPal\Rest\ApiContext */
    protected $apiContext;
    /** @var object */
    protected $bodyObject;

    protected $webHokId;



    public function __construct()
    {
        $this->setApiContext();
    }


    public function setApiContext($nvp = false)
    {

        $paypal_id = env('PAYPAL_ID');
        $paypal_secret = env('PAYPAL_SECRET');
        $mode          = env('PAYPAL_MODE');
        $this->webHokId = env('PAYPAL_WEBHOOKID');

        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($paypal_id, $paypal_secret)
        );

        $log_path = dirname(__FILE__) . '/../../storage/logs/PayPal-webhook.log';

        $this->apiContext->setConfig(
            [
                'mode'           => $mode,
                 'log.LogEnabled' => true,
                 'log.FileName'   => $log_path,
                 'log.LogLevel'   => 'INFO', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                 'cache.enabled'  => true,
            ]
        );


        $this->bodyReceived = $this->getBody();
        if (!$this->bodyReceived)
        {
            exit();
        }

        try
        {
            $this->validateWebHook($this->apiContext, $this->bodyReceived);
        } catch (\Exception $e)
        {
            \Log::error('[Validation Failed]:'.$e->getMessage());
//            $this->PayPal->payPalLog('[Validation Failed]:"' . $e->getMessage(), $e);
//            $this->response->statusCode(501);
//            $this->response->body($e->getMessage());
//            $this->response->send();
            exit();
        }

    }


    /**
     * @return bool|string
     */
    public function getBody()
    {
        try
        {
            /**
             * Receive the entire body that you received from PayPal webhook.
             */
            $bodyReceived = file_get_contents('php://input');

            if ($bodyReceived == '')
            {
                throw new \Exception('empty body');
            }

            return $bodyReceived;
        } catch (\Exception $e)
        {
            \Log::error('error:' . $e->getMessage());
//            $this->payPalLog('[Receive Body Failed]', $e);
            return false;
        }
    }

    /**
     * validate webhook
     * if not pass validation, will not perform further action
     * @param $apiContext
     * @param $bodyReceived
     * @return bool
     * @throws \Exception
     */
    public function validateWebHook($apiContext, $bodyReceived)
    {
        try
        {

            if (!$this->webHokId)
            {
                throw new \Exception('no PAYPAL_WEBHOOK_ID');
            }

            /**
             * Receive HTTP headers that you received from PayPal webhook.
             */
            // getallheaders will cause fatal error, don't know why
            // $headers = getallheaders();

            $headers = [];
            foreach ($_SERVER as $name => $value)
            {
                if (substr($name, 0, 5) == 'HTTP_')
                {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            /**
             * In Documentions https://developer.paypal.com/docs/api/webhooks/#verify-webhook-signature_post
             * All header keys as UPPERCASE, but I recive the header key as the example array, First letter as UPPERCASE
             */
            $headers = array_change_key_case($headers, CASE_UPPER);

            $signatureVerification = new VerifyWebhookSignature();
            $signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);
            $signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);
            $signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);
            $signatureVerification->setWebhookId($this->webHokId); // Note that the Webhook ID must be a currently valid Webhook that you created with your client ID/secret.
            $signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);
            $signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);

            // setWebhookEvent is deprecated, should use setRequestBody
            // but older version payapl sdk does not have setRequestBody
            if (method_exists($signatureVerification, 'setRequestBody'))
            {
                $signatureVerification->setRequestBody($bodyReceived);
            }
            elseif (method_exists($signatureVerification, 'setWebhookEvent'))
            {
                $webhookEvent = new WebhookEvent();
                $webhookEvent->fromJson($bodyReceived);
                $signatureVerification->setWebhookEvent($webhookEvent);
            }

            /** @var \PayPal\Api\VerifyWebhookSignatureResponse $output */
            $output = $signatureVerification->post($apiContext);
            // be careful, if paypal sdk version is different between forumhosting and siteowners
            // here will be FAILURE
            if ($output->verification_status == 'FAILURE')
            {
                throw new \Exception('[VerifyWebhookSignatureResponse FAILURE]');
            }
            return true;
        } catch (\Exception $e)
        {
            \Log::error('[Validation Failed]:'.$e->getMessage(). "[$bodyReceived]");
            throw new \Exception('[Validation Failed]' . $e->getMessage());
        }
    }



    /**
     * here is the real business logic
     */
    public function webHook()
    {
//        try
//        {

            $this->bodyObject = json_decode($this->bodyReceived);

            if (!is_object($this->bodyObject))
            {
                \Log::error('[Parse Body Failed] invalid body');
                throw new \Exception('Invalid request null');
            }

            return $this->bodyObject;

//            if ($this->TtgPaymentHistory->checkDuplicateWebHook($this->bodyObject->id))
//            {
//                throw new \Exception('[NOTICE] web hook already saved, web hook id:' . $this->bodyObject->id);
//            }
//
//            if ($this->TtgPaymentHistory->checkDuplicateAgreementPayment($this->bodyObject->resource))
//            {
//                throw new \Exception('[NOTICE] agreement payment already saved, sub id:' . $this->bodyObject->resource->billing_agreement_id . ", trans id: " . $this->bodyObject->resource->id);
//            }



//        } catch (\Exception $e)
//        {
            \Log::error('error web hook');
//            $this->PayPal->payPalLog('[Save Event Data Failed] event type:"' . $this->bodyObject->event_type . '" web hook id:"' . $this->bodyObject->id . '"', $e);
//            $this->response->statusCode(501);
//            $this->response->body($e->getMessage());
//            $this->response->send();
//        }
        // this is necessary
//        exit();
    }


}