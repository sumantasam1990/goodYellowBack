<?php

namespace App\Http\Controllers;

use App\Mail\OrderCancelled;
use App\Models\Orders;
use App\Mail\OrderShipped;
use Illuminate\Support\Env;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function placed_order($bid)
    {
        $orders = DB::table('orders')
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->join('users', 'orders.seller_id', '=', 'users.id')
                ->join('payment_orders', 'orders.payment_order_id', 'payment_orders.id')
                ->join('shipping_address', 'orders.order_uniq', 'shipping_address.ship_order_no')
                ->where('orders.buyer_id', '=', $bid)
                ->where('orders.order_status', '=', 0)
                ->orderBy('orders.id')
                ->get();

        return response()->json($orders);
    }

    public function shippid_order($bid)
    {
        $orders = DB::table('orders')
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->join('users', 'orders.seller_id', '=', 'users.id')
                ->join('payment_orders', 'orders.payment_order_id', 'payment_orders.id')
                ->join('shipping_address', 'orders.order_uniq', 'shipping_address.ship_order_no')
                ->where('orders.buyer_id', '=', $bid)
                ->where('orders.order_status', '=', 1)
                ->orderBy('orders.id')
                ->get();

        return response()->json($orders);
    }

    public function cancelled_order($bid)
    {
        $orders = DB::table('orders')
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->join('users', 'orders.seller_id', '=', 'users.id')
                ->join('payment_orders', 'orders.payment_order_id', 'payment_orders.id')
                ->join('shipping_address', 'orders.order_uniq', 'shipping_address.ship_order_no')
                ->where('orders.buyer_id', '=', $bid)
                ->where('orders.order_status', '=', 2)
                ->orderBy('orders.id')
                ->get();

        return response()->json($orders);
    }

    // seller orders

    public function seller_placed_order($bid)
    {
        $orders = DB::table('orders')
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->join('buyer_users', 'orders.buyer_id', '=', 'buyer_users.id')
                ->join('payment_orders', 'orders.payment_order_id', 'payment_orders.id')
                ->join('shipping_address', 'orders.order_uniq', 'shipping_address.ship_order_no')
                ->where('orders.seller_id', '=', $bid)
                ->where('orders.order_status', '=', 0)
                ->select('products.p_uniq', 'products.p_name', 'orders.product_discount_price', 'orders.order_quantity', 'shipping_address.address', 'shipping_address.phone', 'shipping_address.email', 'payment_orders.tran_receipt_url', 'buyer_users.fname', 'buyer_users.lname', 'buyer_users.email', 'orders.id as order_id')
                ->orderBy('orders.id')
                ->get();

        return response()->json($orders);
    }

    public function seller_shippid_order($bid)
    {
        $orders = DB::table('orders')
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->join('buyer_users', 'orders.buyer_id', '=', 'buyer_users.id')
                ->join('payment_orders', 'orders.payment_order_id', 'payment_orders.id')
                ->join('shipping_address', 'orders.order_uniq', 'shipping_address.ship_order_no')
                ->where('orders.seller_id', '=', $bid)
                ->where('orders.order_status', '=', 1)
                ->select('products.p_uniq', 'products.p_name', 'orders.product_discount_price', 'orders.order_quantity', 'shipping_address.address', 'shipping_address.phone', 'shipping_address.email', 'payment_orders.tran_receipt_url', 'buyer_users.fname', 'buyer_users.lname', 'buyer_users.email', 'orders.id as order_id')
                ->orderBy('orders.id')
                ->get();

        return response()->json($orders);
    }

    public function seller_cancelled_order($bid)
    {
        $orders = DB::table('orders')
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->join('buyer_users', 'orders.buyer_id', '=', 'buyer_users.id')
                ->join('payment_orders', 'orders.payment_order_id', 'payment_orders.id')
                ->join('shipping_address', 'orders.order_uniq', 'shipping_address.ship_order_no')
                ->where('orders.seller_id', '=', $bid)
                ->where('orders.order_status', '=', 2)
                ->select('products.p_uniq', 'products.p_name', 'orders.product_discount_price', 'orders.order_quantity', 'shipping_address.address', 'shipping_address.phone', 'shipping_address.email', 'payment_orders.tran_receipt_url', 'buyer_users.fname', 'buyer_users.lname', 'buyer_users.email', 'orders.id as order_id')
                ->orderBy('orders.id')
                ->get();

        return response()->json($orders);
    }

    public function seller_order_status_change($uid, $order, $status)
    {
        Orders::where('seller_id', $uid)->where('id', $order)->update(['order_status' => $status]);

        if($status == 1) {

            $buyerInfo = DB::table('orders')->join('buyer_users', 'orders.buyer_id', '=', 'buyer_users.id')->join('users', 'orders.seller_id', '=', 'users.id')->where('orders.id', $order)->select('buyer_users.fname', 'buyer_users.lname', 'buyer_users.email', 'users.company')->first();

            $emailData = [
                'buyer' => $buyerInfo->fname . ' ' . $buyerInfo->lname,
                'vendor' => $buyerInfo->company,
                'url' => Env::get('FRONTEND_URL') . 'u/my/orders',
            ];

            Mail::to($buyerInfo->email)->send(new OrderShipped($emailData));

        }

        if($status == 2) {

            $buyerInfo = DB::table('orders')->join('buyer_users', 'orders.buyer_id', '=', 'buyer_users.id')->join('users', 'orders.seller_id', '=', 'users.id')->where('orders.id', $order)->select('buyer_users.fname', 'buyer_users.lname', 'buyer_users.email', 'users.company')->first();

            $emailData = [
                'buyer' => $buyerInfo->fname . ' ' . $buyerInfo->lname,
                'vendor' => $buyerInfo->company,
                'url' => Env::get('FRONTEND_URL') . 'u/my/orders',
            ];

            Mail::to($buyerInfo->email)->send(new OrderCancelled($emailData));

        }
        return response()->json('success');
    }
}