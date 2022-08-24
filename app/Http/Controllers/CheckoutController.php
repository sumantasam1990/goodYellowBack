<?php

namespace App\Http\Controllers;

use App\Mail\Order;
use App\Models\User;
use App\Models\Orders;
use App\Models\Product;
use App\Models\AddToCart;
use App\Models\Variations;
use Illuminate\Support\Env;
use App\Models\ShippingCost;
use App\Models\VendorStripe;
use Illuminate\Http\Request;
use App\Models\PaymentOrders;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {

        try{

            // checking quantity -------------------

            // $cart_quantity = AddToCart::where('user_id', $uid)->where('buyer_id', $bid)->where('product_id', $product->p_uniq)->sum('quantity');

            $cart_vari_quantity = DB::select('select t2.id, t1.product_id, t1.variation_size, t1.variation_color, t2.p_name, sum(t1.quantity) as total, sum(t2.inventory) as total2
            from add_to_cart t1
            join products t2
            on(t1.product_id=t2.p_uniq)
            where t1.user_id = ?
            and t1.buyer_id = ?
            group by t1.product_id, t1.variation_size, t1.variation_color', [$request->user_id, $request->buyer_id]);


            if(count($cart_vari_quantity) > 0) {

                foreach($cart_vari_quantity as $vari_q) {

                    $product = Product::where('id', $vari_q->id)->first();

                    if($product->inventory <= 0 || $product->inventory < $vari_q->total) {
                            return response()->json(['status' => 0, 'msg' => 'Sorry! One or more products are out of stock. Please remove them before continue to checkout.', 'p_name' => $product->p_name, 'p_uniq' => $product->p_uniq]);
                        }
                    }

                    $vari2 = Variations::where('product_id', $vari_q->id)->select('inventory', 'var_key', 'var_val')->get();

                    if(count($vari2) > 0) {
                        $vari_quantity = Variations::where('product_id', $vari_q->id)->where('var_key', $vari_q->variation_size)->Where('var_val', $vari_q->variation_color)->select('inventory')->get();

                        if(count($vari_quantity) > 0) {
                            if($vari_quantity[0]->inventory == 0 || $vari_quantity[0]->inventory < $vari_q->total) {
                                return response()->json(['status' => 0, 'msg' => 'Sorry! One or more products or variation are out of stock. Please remove them before continue to checkout.', 'p_name' => $vari_q->p_name, 'p_uniq' => $vari_q->product_id, 'size' => $vari_q->variation_size, 'color' => $vari_q->variation_color]);
                            }
                        }

                    }

                }


            // end checking quantity ---------------

            //get api_key from a vendor

            $userAPI = VendorStripe::where('user_id', $request->user_id)->select('api_key', 'secret_key')->first();

            $addtocarts = AddToCart::where('user_id', $request->user_id)->where('buyer_id', $request->buyer_id)->get();

            foreach($addtocarts as $add) {

                $product = Product::where('p_uniq', $add->product_id)->select('id', 'p_name', 'user_id', 'p_price as price', 'discount_percentage as discount', 'inventory', 'shipping_cost')->first();

                // ------------ get variation ------------

                $variations = Variations::where('product_id', $product->id)
                            ->where('var_key', $add->variation_size)
                            ->where('var_val', $add->variation_color)
                            ->select('var_key', 'var_val', 'inventory', 'price')
                            ->get();


                // end variation ------------------------

                if(count($variations) > 0) {

                    $price = number_format($variations[0]->price, 2);
                    $price_discount = number_format($variations[0]->price - ($variations[0]->price*$product->discount)/100, 2);

                    $price_discount = $price_discount;

                } else {

                    $price = number_format($product->price, 2);
                    $price_discount = number_format($product->price - ($product->price*$product->discount)/100, 2);

                    $price_discount = $price_discount;

                }

                // $price = number_format($product->price, 2);
                // $price_discount = number_format($product->price - ($product->price*$product->discount)/100, 2);

                // $price_discount = $price_discount;

                $products_data[] = [
                    'price' => $price,
                    'discount_price' => $price_discount,
                    'product' => $product->p_name,
                    'product_id' => $product->id,
                    'quantity' => $add->quantity,
                    'inventory' => $product->inventory,
                    'shipping_cost' => $product->shipping_cost,
                    'p_uniq' => $add->product_id,
                    'var_size' => count($variations) > 0 ? $variations[0]->var_key : '',
                    'var_color' => count($variations) > 0 ? $variations[0]->var_val : '',
                ];

                $plus_price[] = $price_discount;
                $plus_quantity[] = $add->quantity;
            }

            // start shipping cost calculation ------------------------

                $productAddToCartQuantity = DB::select('select t2.id, t1.product_id, sum(t1.quantity) as total from add_to_cart t1 join products t2 on(t1.product_id=t2.p_uniq) where t1.user_id = ? group by t1.product_id', [$request->user_id]);

                $ship_cost = [];

                foreach($productAddToCartQuantity as $q) {

                    $shipping = ShippingCost::where('product_id', $q->id)
                        ->where('ship_from_quantity', '<=', $q->total)
                        ->Where('ship_to_quantity', '>=', $q->total)
                        ->select('ship_cost')
                        ->first();

                    $ship_cost[] = $shipping?->ship_cost;

                }

            // end shipping cost calculation --------------------------

            $chargeAmount = array_sum($ship_cost) + array_sum($plus_price);

            if($userAPI && $userAPI->api_key != '') {
                $stripe = new \Stripe\StripeClient($userAPI->secret_key);
                    $token_results = $stripe->tokens->create([
                    'card' => [
                        'number' => $request->card_no,
                        'exp_month' => $request->card_exp_month,
                        'exp_year' => $request->card_exp_year,
                        'cvc' => $request->card_cvv,
                    ],
                ]);

                if($token_results) {
                    $token = $token_results->id;
                    $charge = $stripe->charges->create([
                        'amount' => $chargeAmount*100,
                        'currency' => 'inr',
                        'source' => $token,
                        'description' => 'Good.Yellow.purchase',
                    ]);

                    if($charge && $charge->status == 'succeeded') {
                        // now we can insert everything order, payment, shipping address
                        $orderNo = 'ord_GY_' . md5(uniqid(). time().$request->user_id.$request->buyer_id.$charge->id);

                        $paymentOrder = new PaymentOrders;

                        $paymentOrder->buyer_id = $request->buyer_id;
                        $paymentOrder->tran_id = $charge->id;
                        $paymentOrder->tran_status = $charge->status;
                        $paymentOrder->tran_receipt_url = $charge->receipt_url;
                        $paymentOrder->tran_amount = $charge->amount;

                        $paymentOrder->save();

                        $shipping = new ShippingAddress;

                        $shipping->buyer_id = $request->buyer_id;
                        $shipping->address = $request->address;
                        $shipping->phone = $request->phone;
                        $shipping->email = $request->email;
                        $shipping->ship_order_no = $orderNo;

                        $shipping->save();


                        foreach($products_data as $prod) {

                            $product_inventory = Product::where('id', $prod['product_id'])->select('inventory')->first();

                            $variation_inventory = Variations::where('product_id', $prod['product_id'])->where('var_key', $prod['var_size'])->where('var_val', $prod['var_color'])->select('inventory')->get();

                            $orders = new Orders;

                            $orders->buyer_id = $request->buyer_id;
                            $orders->seller_id = $request->user_id;
                            $orders->product_id = $prod['product_id'];
                            $orders->product_price = $prod['price'];
                            $orders->product_discount_price = $prod['discount_price'];
                            $orders->tax = '';
                            $orders->order_uniq = $orderNo;
                            $orders->payment_order_id = $paymentOrder->id;
                            $orders->order_quantity = $prod['quantity'];
                            $orders->order_price = $chargeAmount;
                            $orders->order_shipping_cost = array_sum($ship_cost);
                            $orders->vari_size = $prod['var_size'];
                            $orders->vari_color = $prod['var_color'];

                            $orders->save();

                            // -1 product quantity from product & variation table

                            Product::where('id', $prod['product_id'])->update(['inventory' => $product_inventory->inventory - 1]);

                            if(count($variation_inventory) > 0) {
                                Variations::where('product_id', $prod['product_id'])->update(['inventory' => $variation_inventory[0]->inventory - 1]);
                            }

                        }

                        //delete from add to cart

                        AddToCart::where('user_id', $request->user_id)->where('buyer_id', $request->buyer_id)->delete();

                        // sending email to vendor

                        $userInfo = User::where('id', $request->user_id)->select('company', 'email')->first();

                        $emailData = [
                            'name' => $userInfo->company,
                            'url' => Env::get('FRONTEND_URL') . 'vendor-orders',
                        ];

                        Mail::to($userInfo->email)->send(new Order($emailData));

                        return response()->json(['succ' => $charge->id, 'order' => $orderNo]);
                    } else {
                        return response()->json(['err' => 'Error! Your payment is not successfull. Please try with another valid card.']);
                    }

                } else {
                    return response()->json(['err' => 'Error! Your payment is not successfull. Please try with another valid card.']);
                }


            } else {
                return response()->json(['err' => 'Error! Your payment is not successfull. Please try with another valid card.']);
            }


        } catch(\Throwable $th) {
            return response()->json(['err' => $th->getMessage() . ' | ' . $th->getLine()]);
        }


    }

    public function brand_info($uid, $bid)
    {
        $user = User::where('id', $uid)->first();

        $addtocarts = AddToCart::where('user_id', $uid)->where('buyer_id', $bid)->get();

        // checking quantity -------------------

            $cart_vari_quantity = DB::select('select t2.id, t1.product_id, t1.variation_size, t1.variation_color, t2.p_name, sum(t1.quantity) as total, sum(t2.inventory) as total2
            from add_to_cart t1
            join products t2
            on(t1.product_id=t2.p_uniq)
            where t1.user_id = ?
            and t1.buyer_id = ?
            group by t1.product_id, t1.variation_size, t1.variation_color', [$uid, $bid]);


            if(count($cart_vari_quantity) > 0) {

                foreach($cart_vari_quantity as $vari_q) {

                    $product = Product::where('id', $vari_q->id)->first();

                    if($product->inventory <= 0 || $product->inventory < $vari_q->total) {
                            return response()->json(['status' => 0, 'msg' => 'Sorry! One or more products are out of stock. Please remove them before continue to checkout.', 'p_name' => $product->p_name, 'p_uniq' => $product->p_uniq]);
                        }
                    }

                    $vari2 = Variations::where('product_id', $vari_q->id)->select('inventory', 'var_key', 'var_val')->get();

                    if(count($vari2) > 0) {
                        $vari_quantity = Variations::where('product_id', $vari_q->id)->where('var_key', $vari_q->variation_size)->Where('var_val', $vari_q->variation_color)->select('inventory')->get();

                        if(count($vari_quantity) > 0) {
                            if($vari_quantity[0]->inventory == 0 || $vari_quantity[0]->inventory < $vari_q->total) {
                                return response()->json(['status' => 0, 'msg' => 'Sorry! One or more products or variation are out of stock. Please remove them before continue to checkout.', 'p_name' => $vari_q->p_name, 'p_uniq' => $vari_q->product_id, 'size' => $vari_q->variation_size, 'color' => $vari_q->variation_color]);
                            }
                        }

                    }

                }


            // end checking quantity ---------------

        foreach($addtocarts as $add) {

            $product = Product::where('p_uniq', $add->product_id)->select('id', 'p_name', 'user_id', 'p_price as price', 'discount_percentage as discount', 'shipping_cost', 'inventory', 'p_uniq')->first();

            //balllllllllllllllll--------------------------

            // ------------ get variation ------------

            $variations = Variations::where('product_id', $product->id)
                        ->where('var_key', $add->variation_size)
                        ->where('var_val', $add->variation_color)
                        ->select('var_key', 'var_val', 'inventory', 'price')
                        ->get();


            // end variation ------------------------

            if(count($variations) > 0) {

                $price = number_format($variations[0]->price, 2);
                $price_discount = number_format($variations[0]->price - ($variations[0]->price*$product->discount)/100, 2);

                $price_discount = $price_discount;

            } else {

                $price = number_format($product->price, 2);
                $price_discount = number_format($product->price - ($product->price*$product->discount)/100, 2);

                $price_discount = $price_discount;

            }

            // $price = number_format($product->price, 2);
            // $price_discount = number_format($product->price - ($product->price*$product->discount)/100, 2);

            // $price_discount = $price_discount;


            $products_data[] = [
                'price' => $price,
                'discount_price' => $price_discount,
                'product' => $product->p_name,
                'product_id' => $product->id,
                'quantity' => $add->quantity,
                'shipping_cost' => $product->shipping_cost,
                'p_uniq' => $add->product_id,
            ];

            $plus_price[] = $price_discount;
            $plus_quantity[] = $add->quantity;
        }

        // start shipping cost calculation ------------------------

        $productAddToCartQuantity = DB::select('select t2.id, t1.product_id, sum(t1.quantity) as total from add_to_cart t1 join products t2 on(t1.product_id=t2.p_uniq) where t1.user_id = ? group by t1.product_id', [$user->id]);

        $ship_cost = [];

        foreach($productAddToCartQuantity as $q) {

            $shipping = ShippingCost::where('product_id', $q->id)
                ->where('ship_from_quantity', '<=', $q->total)
                ->Where('ship_to_quantity', '>=', $q->total)
                ->select('ship_cost')
                ->first();

            $ship_cost[] = $shipping?->ship_cost;

        }

            // end shipping cost calculation --------------------------

        return response()->json(['brand' => $user->company, 'total' => array_sum($plus_price) + array_sum($ship_cost)]);
    }
}