<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:20 PM
 */

namespace Rndwiga\Mpesa\Libraries\Express;


use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaExpressCalls extends MpesaApiConnection
{

    private $BusinessShortCode;
    private $LipaNaMpesaPasskey;
    private $TransactionType;
    private $Amount;
    private $PartyA;
    private $PartyB;
    private $PhoneNumber;
    private $CallBackURL;
    private $AccountReference;
    private $TransactionDesc;
    private $Remark;

    private $ConsumerKey;
    private $ConsumerSecret;

    private $CheckoutRequestID;
    private $Password;
    private $Timestamp;

    private $ApplicationStatus;

    public function __construct()
    {

    }


    /**
     * @return mixed
     */
    public function getCheckoutRequestID()
    {
        return $this->CheckoutRequestID;
    }

    /**
     * @param mixed $CheckoutRequestID
     * @return MpesaExpressCalls
     */
    public function setCheckoutRequestID($CheckoutRequestID)
    {
        $this->CheckoutRequestID = $CheckoutRequestID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->Password;
    }

    /**
     * @param mixed $Password
     * @return MpesaExpressCalls
     */
    public function setPassword($Password)
    {
        $this->Password = $Password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->Timestamp;
    }

    /**
     * @param mixed $Timestamp
     * @return MpesaExpressCalls
     */
    public function setTimestamp($Timestamp)
    {
        $this->Timestamp = $Timestamp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConsumerKey()
    {
        return $this->ConsumerKey;
    }

    /**
     * @param mixed $ConsumerKey
     * @return MpesaExpressCalls
     */
    public function setConsumerKey($ConsumerKey)
    {
        $this->ConsumerKey = $ConsumerKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConsumerSecret()
    {
        return $this->ConsumerSecret;
    }

    /**
     * @param mixed $ConsumerSecret
     * @return MpesaExpressCalls
     */
    public function setConsumerSecret($ConsumerSecret)
    {
        $this->ConsumerSecret = $ConsumerSecret;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplicationStatus()
    {
        return $this->ApplicationStatus;
    }

    /**
     * @param mixed $ApplicationStatus
     * @return MpesaExpressCalls
     */
    public function setApplicationStatus($ApplicationStatus)
    {
        $this->ApplicationStatus = $ApplicationStatus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountReference()
    {
        return $this->AccountReference;
    }

    /**
     * @param mixed $AccountReference
     * @return MpesaExpressCalls
     */
    public function setAccountReference($AccountReference)
    {
        $this->AccountReference = $AccountReference;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->Amount;

    }

    /**
     * @param mixed $Amount
     * @return MpesaExpressCalls
     */
    public function setAmount($Amount)
    {
        $this->Amount = $Amount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBusinessShortCode()
    {
        return $this->BusinessShortCode;
    }

    /**
     * @param mixed $BusinessShortCode
     * @return MpesaExpressCalls
     */
    public function setBusinessShortCode($BusinessShortCode)
    {
        $this->BusinessShortCode = $BusinessShortCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCallBackURL()
    {
        return $this->CallBackURL;
    }

    /**
     * @param mixed $CallBackURL
     * @return MpesaExpressCalls
     */
    public function setCallBackURL($CallBackURL)
    {
        $this->CallBackURL = $CallBackURL;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLipaNaMpesaPasskey()
    {
        return $this->LipaNaMpesaPasskey;
    }

    /**
     * @param mixed $LipaNaMpesaPasskey
     * @return MpesaExpressCalls
     */
    public function setLipaNaMpesaPasskey($LipaNaMpesaPasskey)
    {
        $this->LipaNaMpesaPasskey = $LipaNaMpesaPasskey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPartyA()
    {
        return $this->PartyA;
    }

    /**
     * @param mixed $PartyA
     * @return MpesaExpressCalls
     */
    public function setPartyA($PartyA)
    {
        $this->PartyA = $PartyA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPartyB()
    {
        return $this->PartyB;
    }

    /**
     * @param mixed $PartyB
     * @return MpesaExpressCalls
     */
    public function setPartyB($PartyB)
    {
        $this->PartyB = $PartyB;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->PhoneNumber;
    }

    /**
     * @param mixed $PhoneNumber
     * @return MpesaExpressCalls
     */
    public function setPhoneNumber($PhoneNumber)
    {
        $this->PhoneNumber = $PhoneNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRemark()
    {
        return $this->Remark;
    }

    /**
     * @param mixed $Remark
     * @return MpesaExpressCalls
     */
    public function setRemark($Remark)
    {
        $this->Remark = $Remark;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransactionDesc()
    {

        return $this->TransactionDesc;
    }

    /**
     * @param mixed $TransactionDesc
     * @return MpesaExpressCalls
     */
    public function setTransactionDesc($TransactionDesc)
    {
        $this->TransactionDesc = $TransactionDesc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransactionType()
    {
        $this->TransactionType = "CustomerPayBillOnline";
        return $this->TransactionType;
    }

    /**
     * @param mixed $TransactionType
     * @return MpesaExpressCalls
     */
    public function setTransactionType($TransactionType)
    {
        $this->TransactionType = $TransactionType;
        return $this;
    }

    /* REQUEST
     * {
            "MerchantRequestID":"15878-2630061-1",
            "CheckoutRequestID":"ws_CO_DMZ_48451723_08072018100531327",
            "ResponseCode": "0",
            "ResponseDescription":"Success. Request accepted for processing",
            "CustomerMessage":"Success. Request accepted for processing"
        }

   SUCCESS RESPONSE
    {
      "Body": {
        "stkCallback": {
          "MerchantRequestID": "16812-2660984-1",
          "CheckoutRequestID": "ws_CO_DMZ_48358274_08072018145453949",
          "ResultCode": 1032,
          "ResultDesc": "Request cancelled by user"
        }
      }
    }

    FAIL RESPONSE
    {
      "Body": {
        "stkCallback": {
          "MerchantRequestID": "15886-2691224-1",
          "CheckoutRequestID": "ws_CO_DMZ_48357162_08072018145122283",
          "ResultCode": 0,
          "ResultDesc": "The service request is processed successfully.",
          "CallbackMetadata": {
            "Item": [
              {
                "Name": "Amount",
                "Value": 5
              },
              {
                "Name": "MpesaReceiptNumber",
                "Value": "MG80EO2DO8"
              },
              {
                "Name": "Balance"
              },
              {
                "Name": "TransactionDate",
                "Value": 20180708145208
              },
              {
                "Name": "PhoneNumber",
                "Value": 254712550547
              }
            ]
          }
        }
      }
    }
     */
    public function STKPush(){
        //$live=config('gateway.module.gateway.mpesa.b2c.is_live');
        $live= $this->ApplicationStatus == 'live' ? true : false;

        if(!isset($live)){
            die("please declare the application status as defined in the documentation");
        }

        if( $live == true){
            $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        }elseif ($live== false){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }

        $token = $this->generateAccessToken($live,$this->ConsumerKey,$this->ConsumerSecret);


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token));

        $timestamp='20'.date(    "ymdhis");
        $password=base64_encode($this->BusinessShortCode.$this->LipaNaMpesaPasskey.$timestamp);


        $curl_post_data = array(
            'BusinessShortCode' => $this->BusinessShortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $this->TransactionType,
            'Amount' => $this->Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => $this->PartyB,
            'PhoneNumber' => $this->PhoneNumber,
            'CallBackURL' => $this->CallBackURL,
            'AccountReference' => $this->AccountReference,
            'TransactionDesc' => $this->TransactionDesc,
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            $curl_response = "cURL Error #:" . $err;
            return $curl_response;
        } else {
            return $curl_response;
        }

    }


    /**
     * Use this function to initiate an STKPush Status Query request.
     * @param $checkoutRequestID | Checkout RequestID
     * @param $businessShortCode | Business Short Code
     * @param $password | Password
     * @param $timestamp | Timestamp
     * @return mixed|string
     */
    public function STKPushQuery(){
        $live= $this->ApplicationStatus == 'live' ? true : false;

        if(!isset($live)){
            die("please declare the application status as defined in the documentation");
        }

        if( $live == true){
            $url = 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query';
        }elseif ($live== false){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }

        $token = $this->generateAccessToken($live,$this->ConsumerKey,$this->ConsumerSecret);


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token));

        $curl_post_data = array(
            'BusinessShortCode' => $this->BusinessShortCode,
            'Password' => $this->Password,
            'Timestamp' => $this->Timestamp,
            'CheckoutRequestID' => $this->CheckoutRequestID
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);

        $curl_response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            $curl_response = "cURL Error #:" . $err;
            return $curl_response;
        } else {
            return $curl_response;
        }
    }

}