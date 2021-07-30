<?php

namespace App\Http\Controllers;
use Auth;
use Illuminate\Http\Request;
use App\Models\Transfer;

class TransferController extends Controller
{
    protected $senderDetails;
    protected $receiverDetails;
    protected $intiateTransferResult;
    protected $finalTransferResult;

    public function verifyAccountInfo(Request $request)
    {
        $accountNumber = $request['account_number'];

        $bankCode = $request['bank_code'];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/bank/resolve?account_number=".rawurlencode($accountNumber)."&bank_code=".rawurlencode($bankCode),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer sk_test_d87b35348acccdb6c6036ea86a39c17dcc55ef59",
            "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "URL Error #:" . $err;
        } else {
            $senderDetails = json_decode($response);

            $verify = $senderDetails->status;
        }

        if($verify){
            $senderName = $senderDetails->data->account_name;

            return response()->json([
                'name'=>$senderName,
                'account_number'=> $accountNumber,
                'bank_code' => $bankCode,
                'details' => $senderDetails,

            ]);

        }
    }

    public function recipient(Request $request)
    {
        $recipientName = $request['name'];

        $recipientAccountNumber = $request['account_number'];

        $bankCode = $request['bank_code'] ;

        $url = "https://api.paystack.co/transferrecipient";

        $fields = [
            'type' => "nuban",
            'name' => $recipientName,
            'account_number' => $recipientAccountNumber,
            'bank_code' => $bankCode,
            'currency' => "NGN"
        ];

        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);

        curl_setopt($ch,CURLOPT_POST, true);

        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer sk_test_b87efad5cde78a0b6346ce41a2599ba758934ca7",
            "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        $receiverDetails = json_decode($result);

        //return $receiver_name;
        return response()->json([
           "info" => $receiverDetails,
        ], 200);

    }

    public function initiateTransfer(Request $request)
    {
        $receiver_code = $request['reciever_code'];
        $reason = $request['reason'];

        $url = "https://api.paystack.co/transfer/";
        $fields = [
          'source' => "balance",
          'amount' => $request['amount']*100,
          'recipient' => $receiver_code,
          'reason' => $reason
        ];

        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);

        curl_setopt($ch,CURLOPT_POST, true);

        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Authorization: Bearer sk_test_b87efad5cde78a0b6346ce41a2599ba758934ca7",
          "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);

        $intiateTransferResult = json_decode($result);

        $status = $intiateTransferResult->status;

        if($status == "true"){
            return response()->json([
                'data' => $intiateTransferResult
            ], 200);

        } else {
            return response()->json([
                'data' => 'Transfer initiation failed',
                'initiate' => $intiateTransferResult,
                'receiver' => $receiver_code
            ], 400);
        }

    }


    public function finalizeTransfer(Request $request){
        $otp = $request['otp'];
        $transfer_code =  $request['transfer_code'];
        //return $transfer_code;

        $url = "https://api.paystack.co/transfer/finalize_transfer";
        $fields = [
            "transfer_code" =>  $transfer_code,
            "otp" => $otp
        ];
        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);

        curl_setopt($ch,CURLOPT_POST, true);

        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer sk_test_b87efad5cde78a0b6346ce41a2599ba758934ca7",
            "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        $finalTransferResult = json_decode($result);


        if($finalTransferResult->status == "true") {

            $newTransaction = Transfer::create([
                'user_id' => 4,
                'amount' => $finalTransferResult->data->amount,
                'currency' => $finalTransferResult->data->currency,
                'reason' => $finalTransferResult->data->reason,
                'status' => $finalTransferResult->data->status,
                'reference' => $finalTransferResult->data->reference,
                'transfer_id' => $finalTransferResult->data->id,
                'transfer_code' => $finalTransferResult->data->transfer_code,
                'transfer_created_at' => $finalTransferResult->data->createdAt

            ]);

            if($newTransaction){
                return response()->json([
                    'data' => $newTransaction
                ], 200);

            } else {
                return response()->json([
                    'data' => 'Fail to save to database'
                ], 200);
            }

        } else {
            return response()->json([
                'response' => $finalTransferResult->message,
            ], 400);
        }
    }
}
