<?php

namespace App\Http\Controllers;

use App\Models\Credits;
use App\Models\PaymentBuyer;
use Ramsey\Uuid\Uuid;
use Square\Environment;
use Square\Models\Money;
use Square\SquareClient;
use Illuminate\Http\Request;
use Square\Exceptions\ApiException;
use Square\Models\CreatePaymentRequest;

class PaymentController extends Controller
{
    public function buyer_payment(int $bid, string $token)
    {
        if(!empty($token)) {
            return view('payments.buyer_payment', ['title' => 'Good.Yellow - Payment', 'bid' => $bid]);
        } else {
            abort('402');
        }

    }

    public function buyer_payment_post(Request $request)
    {
        if(!empty($request->bid)) {

            $start_date = date('Y-m-d H:i:s');
            $end_date = date('Y-m-d H:i:s', strtotime("+30 days"));

            $square_client = new SquareClient([
            'accessToken' => getenv('SQUARE_ACCESS_TOKEN'),
            'environment' => getenv('SQUARE_ENVIRONMENT')
            ]);

            $payments_api = $square_client->getPaymentsApi();

            $money = new Money();
            $money->setAmount(100);
            // Set currency to the currency for the location
            $money->setCurrency('USD');

            // Every payment you process with the SDK must have a unique idempotency key.
            // If you're unsure whether a particular payment succeeded, you can reattempt
            // it with the same idempotency key without worrying about double charging
            // the buyer.
            $create_payment_request = new CreatePaymentRequest($request->token, Uuid::uuid4(), $money);

            $response = $payments_api->createPayment($create_payment_request);

            $response_results = json_encode($response->getResult());

            $response_decode = json_decode($response_results);

            if ($response->isSuccess()) {

                $paymentBuyer = new PaymentBuyer;

                $paymentBuyer->buyer_id = $request->bid;
                $paymentBuyer->payment_id = $response_decode->payment->id;
                $paymentBuyer->payment_status = $response_decode->payment->status;
                $paymentBuyer->payment_receipt_url = $response_decode->payment->receipt_url;
                $paymentBuyer->payment_receipt_number = $response_decode->payment->receipt_number;
                $paymentBuyer->payment_order_id = $response_decode->payment->order_id;

                $paymentBuyer->save();

                // add credits
                // it should be upsert

                $credit = Credits::updateOrCreate
                (
                    [
                        'buyer_id' => $request->bid,
                    ],
                    [
                        'credits' => 30,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'payment_buyer_id' => $paymentBuyer->id,
                        'buyer_id' => $request->bid,
                    ],
                );

                // $credit = new Credits;

                // $credit->buyer_id = $request->bid;
                // $credit->credits = 30;
                // $credit->start_date = $start_date;
                // $credit->end_date = $end_date;
                // $credit->payment_buyer_id = $paymentBuyer->id;

                // $credit->save();

                return response()->json([$response->getResult()]);
            } else {
                return response()->json($response->getErrors());
            }
        } else {
            abort('402');
        }

    }
}