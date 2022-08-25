<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\AddToCart;
use App\Models\BuyerUser;
use App\Models\Variations;
use App\Models\ShippingCost;
use App\Models\VendorStripe;
use Illuminate\Http\Request;
use App\Models\VendorPromoCode;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function cart_post(Request $request)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'buyer_id' => 'required|numeric|min:1',
            'product' => 'required',
        ]);

        try{

            $user = BuyerUser::where('id', $request->buyer_id)->select('email_verified')->first();

            if($user->email_verified == '') {
                return response()->json(['status' => 0, 'msg' => 'Sorry! Please verify your email id before add to cart any product.']);
            }

            $today = date('Y-m-d H:i:s');

            $product = Product::where('p_uniq', $request->product)->select('user_id', 'inventory', 'p_uniq', 'discount_percentage')->first();

            // checking if the vendor has stripe config----------

            $userAPI = VendorStripe::where('user_id', $product->user_id)->select('api_key', 'secret_key')->get();

            if(count($userAPI) == 0) {
                return response()->json(['status' => 0, 'msg' => 'Sorry! You can not buy any products from this brand. We will let you know when you can buy product from this brand.']);
            }

            // end stripe checking-------------

            // checking algorithm start -------------------------

            $buyer_promo = BuyerUser::where('id', $request->buyer_id)->select('buyer_promo', 'created_at')->first();

            $vendor_promo = VendorPromoCode::where('user_id', $product->user_id)->select('promo_code')->first();

            if(isset($buyer_promo->buyer_promo) == isset($vendor_promo->promo_code)) {

                //return 'All discounts';

                $cart_quantity = AddToCart::where('user_id', $product->user_id)->where('buyer_id', $request->buyer_id)->where('product_id', $request->product)->sum('quantity');

                if($product->inventory > 0 && $product->inventory > $cart_quantity) {

                    $cart = new AddToCart;

                    $cart->buyer_id = $request->buyer_id;
                    $cart->product_id = $request->product;
                    $cart->quantity = $request->quantity;
                    $cart->user_id = $product->user_id;
                    $cart->variation_size = $request->var_size;
                    $cart->variation_color = $request->var_color;

                    $cart->save();

                    return response()->json(['status' => 1, 'msg' => 'Product added into the cart.']);
                } else {
                    return response()->json(['status' => 0, 'msg' => 'Sorry! This product is out of stock.']);
                }

            } else {

                //return 'only 30% discounts';

                $days_30 = date('Y-m-d H:i:s', strtotime("+30 days" . $buyer_promo->created_at));
                //$days_60 = date('Y-m-d H:i:s', strtotime("+60 days" . $buyer_promo->created_at));

                if($product->discount_percentage <= 30 && $days_30 > $today) {

                    $cart_quantity = AddToCart::where('user_id', $product->user_id)->where('buyer_id', $request->buyer_id)->where('product_id', $request->product)->sum('quantity');

                    if($product->inventory > 0 && $product->inventory > $cart_quantity) {

                        $cart = new AddToCart;

                        $cart->buyer_id = $request->buyer_id;
                        $cart->product_id = $request->product;
                        $cart->quantity = $request->quantity;
                        $cart->user_id = $product->user_id;
                        $cart->variation_size = $request->var_size;
                        $cart->variation_color = $request->var_color;

                        $cart->save();

                        return response()->json(['status' => 1, 'msg' => 'Product added into the cart.']);
                    } else {
                        return response()->json(['status' => 0, 'msg' => 'Sorry! This product is out of stock.']);
                    }

                } else {
                    return response()->json(['status' => 0, 'msg' => 'You can not buy more than 30% discounted products till ' . date('F j, Y', strtotime("+30 days" . $buyer_promo->created_at))]);
                }


            }

            //---------------------end----------------------------




        } catch(\Throwable $th) {
            return response()->json(['status' => 0, 'msg' => $th->getMessage()]);
        }

    }

    public function cart($id)
    {
        $carts = DB::table('add_to_cart')
            ->Join('users', 'add_to_cart.user_id', '=', 'users.id')
            ->where('add_to_cart.buyer_id', $id)
            ->groupBy('add_to_cart.user_id')
            ->get();




        foreach($carts as $c) {
            $products_data = [];
            $plus_price = [];
            $plus_quantity = [];


            $addtocarts = AddToCart::where('user_id', $c->user_id)->where('buyer_id', $id)->get();



            foreach($addtocarts as $add) {


                $product = Product::where('p_uniq', $add->product_id)->select('id', 'p_name', 'user_id', 'p_price as price', 'discount_percentage as discount', 'shipping_cost')->first();

                if($product) {
                    // ------------ get variation ------------

                    $variations = Variations::where('product_id', $product?->id)
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

                        $price = number_format($product?->price, 2);
                        $price_discount = number_format($product?->price - ($product?->price*$product?->discount)/100, 2);

                        $price_discount = $price_discount;

                    }

                    $products_data[] = [

                        'price' => $price,
                        'discount_price' => $price_discount,
                        'product' => $product?->p_name,
                        'product_id' => $product?->id,
                        'shipping_cost' => $product?->shipping_cost,
                        'p_uniq' => $add->product_id,
                        'var_size' => count($variations) > 0 ? $variations[0]->var_key : '',
                        'var_color' => count($variations) > 0 ? $variations[0]->var_val : '',
                    ];

                    $plus_price[] = $price_discount;
                    $plus_quantity[] = $add->quantity;
                }


            }

            // start shipping cost calculation ------------------------

                $productAddToCartQuantity = DB::select('select t2.id, t1.product_id, sum(t1.quantity) as total from add_to_cart t1 join products t2 on(t1.product_id=t2.p_uniq) where t1.user_id = ? group by t1.product_id', [$c->user_id]);

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

            $arr[] = [

                'brand_id' => $c->user_id,
                'brand_name' => $c->company,
                'company_slug' => $c->company_slug,
                'products' => $products_data,
                'plus_price' => number_format(array_sum($plus_price), 2),
                'plus_quantity' => array_sum($plus_quantity),
                'total_shipping_cost' => number_format(array_sum($ship_cost), 2),
            ];


        }

        //dd($ship_cost);

        $grandTotal = [];

        foreach($arr as $ar) {
            $grandTotal[] = $ar['plus_price'];
        }

        $grand_total = array_sum($grandTotal);


        //$arr = $this->super_unique($arr,'brand_name');
        return response()->json(['data' => $arr, 'grand_total' => number_format($grand_total, 2)]);
    }

    public function super_unique($array,$key)
    {
       $temp_array = [];
       foreach ($array as &$v) {
           if (!isset($temp_array[$v[$key]]))
           $temp_array[$v[$key]] =& $v;
       }
       $array = array_values($temp_array);
       return $array;

    }

    public function removeAll($uid,$bid)
    {
        AddToCart::where('user_id', $uid)->where('buyer_id', $bid)->delete();

        return response()->json('success');
    }

    public function removeOne($pid)
    {
        AddToCart::where('product_id', $pid)->delete();

        return response()->json('success');
    }
}