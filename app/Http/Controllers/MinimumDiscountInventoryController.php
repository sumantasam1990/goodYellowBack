<?php

namespace App\Http\Controllers;

use App\Models\DiscountProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class MinimumDiscountInventoryController extends Controller
{
    private function getData($uid)
    {
        $first_product = Product::where('user_id', $uid)->select('inventory as unit', 'discount_percentage as discount', 'p_name as product', 'id as pid', 'created_at as publish')->first();

        $first_discount_product = DiscountProduct::where('user_id', $uid)->select('how_many_discount_code as unit', 'discount as discount', 'name as product', 'id as pid', 'created_at as publish')->first();

        if($first_product->publish < $first_discount_product->publish) {
            $discount = $first_product->discount;
            $units = $first_product->unit;
            $product = $first_product->product;
        }

        if($first_product->publish > $first_discount_product->publish) {
            $discount = $first_discount_product->discount;
            $units = $first_discount_product->unit;
            $product = $first_discount_product->product;
        }

        return [ 'discounts' => $discount, 'units' => $units, 'product' => $product ];
    }
    public function index($uid)
    {
        $first_product = Product::where('user_id', $uid)->select('inventory as unit', 'discount_percentage as discount', 'p_name as product', 'id as pid', 'created_at as publish')->first();

        $first_discount_product = DiscountProduct::where('user_id', $uid)->select('how_many_discount_code as unit', 'discount as discount', 'name as product', 'id as pid', 'created_at as publish')->first();

        if($first_product->publish < $first_discount_product->publish) {
            $discount = $first_product->discount;
            $units = $first_product->unit;
            $pid = $first_product->pid;
        }

        if($first_product->publish > $first_discount_product->publish) {
            $discount = $first_discount_product->discount;
            $units = $first_discount_product->unit;
            $pid = $first_discount_product->pid;
        }




        // start algorithm --------------------------

        if($discount < 50) {
            Product::where('id', $pid)->update(['discount_percentage' => 50]);

            if($units < 9) {
                //Product::where('id', $first_product->pid)->update(['inventory' => 9]);
            }

            if($units > 4) {
                Product::where('id', $pid)->update(['discount_percentage' => 50]);
            }



        }

        //check again

        $first_product = Product::where('user_id', $uid)->select('inventory as unit', 'discount_percentage as discount', 'p_name as product', 'id as pid', 'created_at as publish')->first();

        $first_discount_product = DiscountProduct::where('user_id', $uid)->select('how_many_discount_code as unit', 'discount as discount', 'name as product', 'id as pid', 'created_at as publish')->first();

        if($first_product->publish < $first_discount_product->publish) {
            $discount = $first_product->discount;
            $units = $first_product->unit;
            $pid = $first_product->pid;
        }

        if($first_product->publish > $first_discount_product->publish) {
            $discount = $first_discount_product->discount;
            $units = $first_discount_product->unit;
            $pid = $first_discount_product->pid;
        }

        if($discount >= 50) {
            if($units < 5) {
                if($discount < 65) {
                    Product::where('id', $pid)->update(['discount_percentage' => 65]);
                }

            }
        }

        $updatedData = $this->getData($uid);

        return response()->json($updatedData);
    }
}
