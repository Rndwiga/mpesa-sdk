<?php

namespace Rndwiga\Mpesa\Libraries\C2B;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Tyondo\Cirembo\CiremboLogger;
use Tyondo\Cirembo\Modules\Client\Models\Client;
use Tyondo\Cirembo\Modules\Gateway\Events\LoanObligationMetEvent;
use Tyondo\Cirembo\Modules\Gateway\Events\LoanPaymentEvent;
use Tyondo\Cirembo\Modules\Gateway\Events\SentTransactionExistsEvent;
use Tyondo\Cirembo\Modules\Loan\Models\Loan;
use Tyondo\Cirembo\Modules\Loan\Models\LoanTransaction;
use Tyondo\Cirembo\Modules\Gateway\Models\c2bTransactions;

class MpesaApiC2BProcessing
{
    protected $client;
    protected $transaction;
    protected $unprocessedTransaction;
    protected $time;
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
        $this->storeClientTransaction();
        $this->client = null;
    }

    public function storeClientTransaction(){
        $transactionExists = c2bTransactions::all()->where('TransID',$this->transaction['TransID']);
        if (count($transactionExists) == 0){
            $c2bTransaction = new c2bTransactions();
            $c2bTransaction->user_id = 1 ;
            $c2bTransaction->TransID = $this->transaction['TransID'];
            $c2bTransaction->TransTime = (new \DateTime($this->transaction['TransTime']))->format('Y-m-d H:i:s'); //converting the date to timestamp
            $c2bTransaction->TransAmount = $this->transaction['TransAmount'];
            $c2bTransaction->InvoiceNumber = $this->transaction['InvoiceNumber'];
            $c2bTransaction->MSISDN = $this->transaction['MSISDN'];
            $c2bTransaction->Name = $this->transaction['FirstName'] . ' '. $this->transaction['MiddleName'] . ' ' . $this->transaction['LastName'];
            $c2bTransaction->TransactionType = $this->transaction['TransactionType'];
            $c2bTransaction->BusinessShortCode = $this->transaction['BusinessShortCode'];
            $c2bTransaction->OrgAccountBalance = $this->transaction['OrgAccountBalance'];
            $c2bTransaction->ThirdPartyTransID = $this->transaction['ThirdPartyTransID'];
            $c2bTransaction->BillRefNumber = $this->transaction['BillRefNumber'];
                //CiremboLogger::info(json_encode($c2bTransaction),'transactions_c2b_stored_logs');
            $c2bTransaction->save();
        }else{
            //if the code already exists log it.
            $reportTransaction = [
                'Message' => 'Transaction already exists',
                'data' => $this->transaction
            ];
            CiremboLogger::info(json_encode($reportTransaction),'transactions_c2b_exits_logs');
            event(new SentTransactionExistsEvent($this->transaction, $transactionExists));
        }
        //process posed data
        $this->postClientApiTransactions();
    }

    private function postClientApiTransactions(){
        //Loan transaction status code (490->booked, 492->suspended)
        //Loan Transaction type codes (350->loan payment, 355->fee payment, 360->interest payment, 365 -> loan booked)
        $unprocessedTransactions = c2bTransactions::where('is_processed',0)->get();
         //CiremboLogger::info(json_encode($unprocessedTransactions),'unprocessed_c2b_transactions_logs');
        if (count($unprocessedTransactions) > 0){
            foreach ($unprocessedTransactions as $transaction){
                if ($this->getClientByMsisdn($transaction->MSISDN)){ //if a client exist
                    CiremboLogger::info(json_encode($transaction),'retrieved_client_base2_logs');
                        $activeLoan = $this->getFirstActiveLoan();
                    if ($activeLoan){ //if there is an active loan or one that is overdue or written off
                        CiremboLogger::info(json_encode($transaction),'retrieved_client_base3_logs');
                        $this->bookLoanTransaction($transaction,490,350,$activeLoan); //post the transaction, the status should be set to booked
                       // $this->markC2BAsProcessed($transaction->id,1); //marking the transaction as processed
                    }else{
                        CiremboLogger::info(json_encode($transaction),'retrieved_client_base33_logs');
                        $this->bookLoanTransaction($transaction,492,350); //post the transaction, status should be suspended
                       // $this->markC2BAsProcessed($transaction->id, 1);
                    }
                }elseif ($this->getClientByMsisdn($transaction->BillRefNumber)){
                        $activeLoan = $this->getFirstActiveLoan();
                    if ($activeLoan){ //if there is an active loan or one that is overdue or written off
                        $this->bookLoanTransaction($transaction,492,350,$activeLoan); //post the transaction, the status should be set to booked
                        //$this->markC2BAsProcessed($transaction->id,1); //marking the transaction as processed
                    }else{
                        $this->bookLoanTransaction($transaction,492,350); //post the transaction, status should be suspended
                        //$this->markC2BAsProcessed($transaction->id, 1);
                    }
                }else{
                    //if cant find a client by either parameters
                    CiremboLogger::info(json_encode($transaction),'retrieved_client_base4_logs');
                    $this->bookLoanTransaction($transaction,492,350); //post the transaction, status suspended
                    //$this->markC2BAsProcessed($transaction->id, 1);
                }
            }
        }
        return [
            'message' => 'All transactions processed'
        ];
    }


    /***
     * This function checks if a client exists using their phone number
     * @param $msisdn
     * @return bool|\Illuminate\Database\Eloquent\Collection|static[]
     */

    protected function getClientByMsisdn($msisdn){
        //$client = Client::where('phone_number',$msisdn)->where('client_status', 220)->limit(1)->get(['id','client_status','client_uid']);
        /*
         * 1. Log the number
         * 2. Check if the number starts with 245, if that is the case split it
         * //log the split it
         * 3. Pull the client based on that response
         */
        if (preg_match("/^254+(?!$)/", $msisdn)){ //if the number starts with 254
            $newMssdn = preg_replace("/^254+(?!$)/", "0", $msisdn);
           // CiremboLogger::info(json_encode($newMssdn),'formatted_mssdn_logs');

            $accessedClient = Client::where('phone_number',$newMssdn)->first();
            CiremboLogger::info(json_encode($accessedClient),'retrieved_client_base1_logs');
            if (count($accessedClient) > 0){
                //log this for testing
                CiremboLogger::info(json_encode($accessedClient),'retrieved_client_logs');
                return $this->client = $accessedClient;
            }else{
                return false;
            }
        }else{
            //CiremboLogger::info(json_encode($msisdn),'Improperly_formatted_Safaricom_numbers_logs');
            return false;
        }

    }

    protected function getFirstActiveLoan(){
        /*---> The payment allocation will be done based on the level of lateness
        * Check if client is set
         * fetch client loans using the client_id
         * 1. if loan is over due fetch that first
         * 2. If loan is active fetch it second
         */

        $loans = $this->getLoansWithGivenStatus();
        //Log these loans for testing
        CiremboLogger::info(json_encode($loans),'retrieved_loans_logs');
        if (count($loans) > 0){ //check if there are active loans
            // return count($loans);
            //return $loans[0]->id;
            return reset($loans);
        }else{
            return false; //no active loans
        }
    }

    /***This function looks for client loans that are:
     * Active, Overdue and written off. and returns them in that order for processing
     * @return bool|array
     */
    protected function getLoansWithGivenStatus(){
        //(300 ->pending, 305->approved, 310->disbursed, 315->active, 320->closed, 325->overpaid
        //330->written-off, 340 -> Overdue)
        if ($this->client){
            $activeLoans = Loan::where('client_id', $this->client->id)
                ->where('loan_status', 315) //active
                ->get();
            if ($activeLoans){
                return $activeLoans;
            }else{
                $overdueLoans = Loan::where('client_id', $this->client->id)
                    ->where('loan_status', 340) //overdue
                    ->get();
                if ($overdueLoans){
                    return $overdueLoans;
                }else{
                    $writtenOffLoans = Loan::where('client_id', $this->client->id)
                        ->where('loan_status', 330) //written-off
                        ->get();
                    if ($writtenOffLoans){
                        return $writtenOffLoans;
                    }else{
                        return false;
                    }
                }
            }
        }
        return false;
    }

    protected function markC2BAsProcessed($c2bTransactionId, $flagged = 1){
        $c2bTransaction = c2bTransactions::find($c2bTransactionId);
        $c2bTransaction->is_processed = $flagged; //processing status: 0-not processed, 1- Processed , 2- Flagged, 3 - pending
        $c2bTransaction->update();

        return $c2bTransaction;
    }

    protected function bookLoanTransaction($transaction,$transactionStatus = 492, $transactionType = 350, $loan = null){
        //log the transaction wating to be processed
        CiremboLogger::info(json_encode($transaction),'accessed_transaction_for_processing_logs');
        $transactionExists = LoanTransaction::where('payment_detail_id',$transaction->TransID)->first();
        //log the existing transaction
        CiremboLogger::info(json_encode($transactionExists),'accessed_existing_transaction_for_processing_logs');
        if (is_null($transactionExists)){ //process if transaction does not exist
            if ($loan){ //if loan is set
                $postTransaction = new LoanTransaction();
                $postTransaction->payment_detail_id = $transaction->TransID;
                $postTransaction->transaction_time = $transaction->TransTime;
                $postTransaction->paid_by_msisdn = $transaction->MSISDN;
                $postTransaction->paid_to_msisdn = $transaction->BillRefNumber; //does this refer to the account? id? phone number?
                $postTransaction->transaction_type = $transactionType; //loan payment
                $postTransaction->transaction_status = $transactionStatus; //booked
                $postTransaction->amount = $transaction->TransAmount;
                $postTransaction->user_id = 1;
                $postTransaction->loan_id = $loan[0]->id;
                $postTransaction->save();

                if ($loan[0]->id && ($transactionStatus == 490)){
                    //doing posting to the loan table
                    $loan = $loan[0]; //can re-use the loan object already accessed
                    $newOutstandingAmount = ($loan->outstanding_amount)- ($postTransaction->amount);
                    $loan->outstanding_amount = $newOutstandingAmount;
                    if ($newOutstandingAmount < 0){ //if the loan is overpaid
                        //TODO:: refund the amount or use it to offset future loan
                        $loan->loan_status = 325; //set the loan is overpaid
                        $loan->date_completely_repaid = Carbon::now(); //set the time loan is completely paid
                        $loan->update();
                        event(new LoanObligationMetEvent($loan,$postTransaction));
                    }elseif ($newOutstandingAmount == 0){
                        $loan->loan_status = 320; //set the loan is closed
                        $loan->date_completely_repaid = Carbon::now(); //set the time loan is completely paid
                        $loan->update();
                        event(new LoanObligationMetEvent($loan,$postTransaction));
                    }else{
                        $loan->update();
                        event(new LoanPaymentEvent($loan,$postTransaction));
                    }
                }
                $this->markC2BAsProcessed($transaction->id, 1); //mark transaction as processed
                return $postTransaction;
            }
            //TODO:: if transaction does not exist, but captured in system, log it for future processing
            $this->markC2BAsProcessed($transaction->id, 3);

        }else {
            //TODO:: if transaction exist, flag it, log it, and report to admin
            $this->markC2BAsProcessed($transaction->id, 2); //mark it as flagged

            CiremboLogger::info($transaction, 'transactions_exits_logs'); //testing the logging
            return true;
        }
    }
}