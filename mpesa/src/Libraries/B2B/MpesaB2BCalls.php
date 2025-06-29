<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 4/2/18
 * Time: 6:44 PM
 */

namespace Rndwiga\Mpesa\Libraries\B2B;


use Exception;
use Rndwiga\Mpesa\Libraries\BaseRequest;
use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaB2BCalls extends BaseRequest
{
    /**
     * Sample method to send funds to a business
     * 
     * @return string The API response
     */
    public function sendFundsToBusiness()
    {
        $response = $this->setPartyB("600000")
            ->setCommandId("BusinessToBusinessTransfer")
            ->setSenderIdentifierType("4")
            ->setReceiverIdentifierType(4)
            ->setAccountReference("BusinessPaybill")
            ->setRemarks("Business Payment To Business")
            ->makeB2bCall(100);
        return $response;
    }

    /**
     * Make a B2B API call
     * 
     * @param int $amount The amount to transfer
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return string The API response
     */
    public function makeB2bCall($amount, $verifySSL = true)
    {
        // Set the amount if it's provided as a parameter
        if ($amount !== null) {
            $this->setAmount($amount);
        }

        // Validate required fields
        if (!isset($this->InitiatorName) || !isset($this->SecurityCredential) || 
            !isset($this->CommandID) || !isset($this->Amount) || 
            !isset($this->PartyA) || !isset($this->PartyB) || 
            !isset($this->QueueTimeOutURL) || !isset($this->ResultURL)) {
            throw new \InvalidArgumentException("Missing required parameters for B2B call");
        }

        // Prepare request data
        $requestData = [
            'Initiator' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID,
            'SenderIdentifierType' => $this->SenderIdentifierType,
            'RecieverIdentifierType' => $this->ReceiverIdentifierType,
            'Amount' => $this->Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => $this->PartyB,
            'AccountReference' => $this->AccountReference,
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL
        ];

        // Make the API request
        return $this->makeApiRequest(self::B2B_ENDPOINT, $requestData, $verifySSL);
    }
}
