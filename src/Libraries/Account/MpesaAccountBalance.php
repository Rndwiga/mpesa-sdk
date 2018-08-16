<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:07 PM
 */

namespace Rndwiga\Mpesa\Libraries\Account;


use Exception;
use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaAccountBalance extends MpesaApiConnection
{

    private $CommandID;
    private $Initiator;
    private $InitiatorPassword;
    private $SecurityCredential;
    private $PartyA;
    private $IdentifierType;
    private $Remarks;
    private $QueueTimeOutURL;
    private $ResultURL;
    private $ConsumerKey;
    private $ConsumerSecret;
    private $ApplicationStatus;

    public function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getApplicationStatus()
    {
        $envStatus = env('MPESA_ACCOUNT_IS_LIVE') ? env('MPESA_ACCOUNT_IS_LIVE'): null;
        if (! is_null($envStatus)){
            $this->ApplicationStatus = $envStatus;
            return $this;
        }
        return $this->ApplicationStatus;
    }

    /**
     * @param mixed $ApplicationStatus
     * @return MpesaAccountBalance
     */
    public function setApplicationStatus($ApplicationStatus)
    {
        $this->ApplicationStatus = $ApplicationStatus;
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
     * @return MpesaAccountBalance
     */
    public function setConsumerSecret($ConsumerSecret)
    {
        $this->ConsumerSecret = $ConsumerSecret;
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
     * @return MpesaAccountBalance
     */
    public function setConsumerKey($ConsumerKey)
    {
        $this->ConsumerKey = $ConsumerKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommandID()
    {
        $this->CommandID = "AccountBalance";
        return $this->CommandID;
    }

    /**
     * @param mixed $CommandID
     * @return MpesaAccountBalance
     */
    public function setCommandID($CommandID = "AccountBalance")
    {
        $this->CommandID = $CommandID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInitiator()
    {
        return $this->Initiator;
    }

    /**
     * @param mixed $Initiator
     * @return MpesaAccountBalance
     */
    public function setInitiator($Initiator)
    {
        $this->Initiator = $Initiator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInitiatorPassword()
    {
        return $this->InitiatorPassword;
    }

    /**
     * @param mixed $InitiatorPassword
     * @return MpesaAccountBalance
     */
    public function setInitiatorPassword($InitiatorPassword)
    {
        $this->InitiatorPassword = $InitiatorPassword;
        return $this;
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public function getSecurityCredential()
    {
       return self::generateSecurityCredentials($this->getInitiatorPassword(),$this->ApplicationStatus);
    }

    /**
     * @param mixed $SecurityCredential
     * @return MpesaAccountBalance
     */
    public function setSecurityCredential($SecurityCredential)
    {
        $this->SecurityCredential = $SecurityCredential;
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
     * @return MpesaAccountBalance
     */
    public function setPartyA($PartyA)
    {
        $this->PartyA = $PartyA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentifierType()
    {
        return $this->IdentifierType;
    }

    /**
     * @param mixed $IdentifierType
     * @return MpesaAccountBalance
     */
    public function setIdentifierType($IdentifierType)
    {
        $this->IdentifierType = $IdentifierType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRemarks()
    {
        return $this->Remarks;
    }

    /**
     * @param mixed $Remarks
     * @return MpesaAccountBalance
     */
    public function setRemarks($Remarks)
    {
        $this->Remarks = $Remarks;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQueueTimeOutURL()
    {
        return $this->QueueTimeOutURL;
    }

    /**
     * @param mixed $QueueTimeOutURL
     * @return MpesaAccountBalance
     */
    public function setQueueTimeOutURL($QueueTimeOutURL)
    {
        $this->QueueTimeOutURL = $QueueTimeOutURL;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultURL()
    {
        return $this->ResultURL;
    }

    /**
     * @param mixed $ResultURL
     * @return MpesaAccountBalance
     */
    public function setResultURL($ResultURL)
    {
        $this->ResultURL = $ResultURL;
        return $this;
    }



    public function accountBalance(){

        if(!isset($this->ApplicationStatus)){
            die("please declare the application status as defined in the documentation");
        }
        if( $this->ApplicationStatus == true){
            $url = 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query';
        }elseif ($this->ApplicationStatus== false){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query';
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }

        $token = $this->generateAccessToken($this->ApplicationStatus,$this->ConsumerKey,$this->ConsumerSecret);



        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token)); //setting custom header

        $curl_post_data = array(
            'CommandID' => $this->CommandID,
            'Initiator' => $this->Initiator,
            'SecurityCredential' => $this->SecurityCredential,
            'PartyA' => $this->PartyA,
            'IdentifierType' => $this->IdentifierType,
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL
        );

        return $curl_post_data;

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

    public function testImplementation(){
        return 'suck dick';
    }

}