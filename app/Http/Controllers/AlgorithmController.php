<?php

namespace App\Http\Controllers;

use App\Models\LbLevelTwo;
use App\Models\LbLevelThree;
use Illuminate\Http\Request;
use App\Models\Subscriptions;
use Illuminate\Support\Facades\DB;

class AlgorithmController extends Controller
{
    public function discount_level_one($id, $min, $max)
    {
        //level one  that sells products with min-max% discounts
       // DB::enableQueryLog();
            $arr = [];
            $discountProduct = DB::table('products')
                                ->where('discount_percentage','>=',$min)
                                ->where('discount_percentage','<=',$max);

            $discountCode = DB::table('discount_products')
                                ->where('discount','>=',$min)
                                ->where('discount','<=',$max);

            $brands = DB::table('users')->where('dummy', 0)->where('private',0);



            $brand_list_products = DB::table('lb_level_one','one')

                                    ->select('one.Id as id', 'one.lb_category_id as lb_category_id', 'one.lb_name as lb_name','one.lb_order_no as lb_order_no', 'one.created_at as created_at','one.updated_at as updated_at', 'one.discount as discount',  DB::raw('( CASE WHEN discount_product.discount_percentage IS NULL THEN  0 ELSE discount_product.discount_percentage END) + ( CASE WHEN discount_code.discount IS NULL THEN  0 ELSE discount_code.discount END) + ( CASE WHEN two.discount IS NULL THEN  0 ELSE two.discount END) + ( CASE WHEN one.discount IS NULL THEN  0 ELSE one.discount END)as total_discount'))

                                    ->leftjoin('lb_level_two as two', 'one.Id','=','two.level_one_id')
                                    ->leftjoin('lb_lavel_three as three', 'two.Id','=','three.lavel_two_Id')
                                    ->leftjoinSub(
                                                $brands, 'brands',
                                                function($join)
                                                {
                                                    $join->on('three.user_Id', '=', 'brands.Id');
                                                })

                                    ->leftjoinSub(
                                                $discountProduct, 'discount_product',
                                                function($join)
                                                {
                                                    $join->on('discount_product.user_id', '=', 'brands.Id');
                                                })
                                    ->leftjoinSub(
                                                $discountCode, 'discount_code',
                                                function($join)
                                                {
                                                    $join->on('discount_code.user_id', '=', 'brands.Id');
                                                })

                                    ->whereIn('one.lb_category_id', [1,2,3,4]);




        $data = DB::table($brand_list_products,'brand_list_products')
                    ->select('id', 'lb_category_id', 'lb_name','lb_order_no','created_at','updated_at','discount', DB::raw('MAX(total_discount) as ave_discount'))
                    ->where('total_discount','>=',$min)
                    ->where('total_discount','<=',$max)
                    ->orderBy('ave_discount', 'DESC')
                    ->groupBy('lb_name')
                    ->get();




            foreach($data as $d) {

                $arr[] = [
                    'Id' => $d->id,
                    'lb_category_id' => $d->lb_category_id,
                    'lb_name' => $d->lb_name,
                    'lb_order_no' => $d->lb_order_no,
                    'created_at' => $d->created_at,
                    'updated_at' => $d->updated_at,
                    'discount' => $d->discount,
                    'ave_discount' =>  number_format($d->ave_discount),


                ];
            }

            $twoIds = [];
            $hreeArr = [];
            $total_buyers = [];
            $subscribers_total = [];

            foreach($arr as $bal) {
                $two = LbLevelTwo::where('level_one_id', $bal['Id'])->get();
                foreach($two as $t) {
                    $twoIds[] = $t->id;
                }
            }


            $hree = LbLevelThree::whereIn('lavel_two_id', $twoIds)->where('user_id', '!=', null)->where('user_id', '!=', 0)->get();
            foreach($hree as $th) {
                $hreeArr[] = $th->user_id;
            }

            //$total_buyers = Subscriptions::whereIn('user_id', $hreeArr)->get();

            foreach($hreeArr as $th) {
                $total_buyers[] = DB::table('subscriptions')
                ->join('users', 'subscriptions.user_id', '=', 'users.id')
                ->join('buyer_users', 'subscriptions.buyer_id', '=', 'buyer_users.id')
                ->select(DB::raw('COUNT(subscriptions.buyer_id) as total'))
                ->where('subscriptions.user_id', $th)->get();
            }



            foreach($total_buyers as $tb) {
                $subscribers_total[] = $tb[0]->total;
            }


            //dd(DB::getQueryLog()); // Show results of log

            return response()->json($arr);
    }

    public function discount_level_two($id,$min,$max)
    {
        $arr = [];
        $discountProduct = DB::table('products')
                            ->where('discount_percentage','>=',$min)
                            ->where('discount_percentage','<=',$max);

        $discountCode = DB::table('discount_products')
                            ->where('discount','>=',$min)
                            ->where('discount','<=',$max);

        $brands = DB::table('users')->where('dummy', 0)->where('private',0);


        $brand_list_products = DB::table('lb_level_two', 'two')
                                ->select('two.Id as id', 'two.level_one_Id as level_one_Id', 'two.lb_two_name as lb_two_name','two.lb_two_order_no as lb_two_order_no','two.created_at as created_at', 'two.updated_at as updated_at', 'two.discount as discount',   DB::raw('( CASE WHEN discount_product.discount_percentage IS NULL THEN  0 ELSE discount_product.discount_percentage END) + ( CASE WHEN discount_code.discount IS NULL THEN  0 ELSE discount_code.discount END) + ( CASE WHEN two.discount IS NULL THEN  0 ELSE two.discount END) + ( CASE WHEN one.discount IS NULL THEN  0 ELSE one.discount END)as total_discount'))

                                   ->leftjoin('lb_level_one as one', 'one.Id','=','two.level_one_id')
                                   ->leftjoin('lb_lavel_three as three', 'two.Id','=','three.lavel_two_Id')
                                   ->leftjoinSub(
                                                $brands, 'brands',
                                                function($join)
                                                {
                                                    $join->on('three.user_Id', '=', 'brands.Id');
                                                })

                                  ->leftjoinSub(
                                            $discountProduct, 'discount_product',
                                            function($join)
                                            {
                                                $join->on('discount_product.user_id', '=', 'brands.Id');
                                            })
                                  ->leftjoinSub(
                                            $discountCode, 'discount_code',
                                            function($join)
                                            {
                                                $join->on('discount_code.user_id', '=', 'brands.Id');
                                            })

                                  ->where('two.level_one_id', $id);



       $data = DB::table($brand_list_products,'brand_list_products')
                  ->select('id', 'level_one_Id', 'lb_two_name','lb_two_order_no','created_at','updated_at','discount', DB::raw('MAX(total_discount) as ave_discount'))
                  ->where('total_discount','>=',$min)
                  ->where('total_discount','<=',$max)
                  ->orderBy('ave_discount', 'DESC')
                  ->groupBy('id')
                  ->get();


        foreach($data as $d) {

             $arr[] = [
                'Id' => $d->id,
                'level_one_Id' => $d->level_one_Id,
                'lb_two_name' => $d->lb_two_name,
                'lb_two_order_no' => $d->lb_two_order_no,
                'created_at' => $d->created_at,
                'updated_at' => $d->updated_at,
                'discount' => $d->discount,
                'ave_discount' => number_format($d->ave_discount),

            ];
        }



        return response()->json($arr);
    }

    public function discount_level_three($id,$min,$max)
    {
        $arr = [];
        $discountProduct = DB::table('products')
                            ->where('discount_percentage','>=',$min)
                            ->where('discount_percentage','<=',$max);

        $discountCode = DB::table('discount_products')
                            ->where('discount','>=',$min)
                            ->where('discount','<=',$max);

        $brands = DB::table('users')->where('dummy', 0)->where('private',0);


        $brand_list_products = DB::table('lb_lavel_three','three')
                                ->select('three.Id as id', 'three.lavel_two_Id as lavel_two_Id', 'three.lb_three_name as lb_three_name','three.lb_three_order_no as lb_three_order_no','three.created_at as created_at', 'three.updated_at as updated_at', 'three.user_id as user_id', 'three.category_id as category_id','three.slug as slug',   DB::raw('( CASE WHEN discount_product.discount_percentage IS NULL THEN  0 ELSE discount_product.discount_percentage END) + ( CASE WHEN discount_code.discount IS NULL THEN  0 ELSE discount_code.discount END) + ( CASE WHEN two.discount IS NULL THEN  0 ELSE two.discount END) + ( CASE WHEN one.discount IS NULL THEN  0 ELSE one.discount END)as total_discount'))
                                   ->leftjoin('lb_level_two as two', 'two.Id','=','three.lavel_two_Id')

                                   ->leftjoin('lb_level_one as one', 'one.Id','=','two.level_one_id')

                                   ->leftjoinSub(
                                                $brands, 'brands',
                                                function($join)
                                                {
                                                    $join->on('three.user_Id', '=', 'brands.Id');
                                                })

                                  ->leftjoinSub(
                                            $discountProduct, 'discount_product',
                                            function($join)
                                            {
                                                $join->on('discount_product.user_id', '=', 'brands.Id');
                                            })
                                  ->leftjoinSub(
                                            $discountCode, 'discount_code',
                                            function($join)
                                            {
                                                $join->on('discount_code.user_id', '=', 'brands.Id');
                                            })

                                   ->where('three.lavel_two_id', $id);



       $data = DB::table($brand_list_products,'brand_list_products')
                  ->select('id', 'lavel_two_Id', 'lb_three_name','lb_three_order_no','created_at','updated_at','user_id','category_id','slug', DB::raw('MAX(total_discount) as ave_discount'))
                  ->where('total_discount','>=',$min)
                  ->where('total_discount','<=',$max)
                  ->orderBy('ave_discount', 'DESC')
                  ->groupBy('id')
                  ->get();


        foreach($data as $d) {

              $arr[] = [
                 'Id' => $d->id,
                 'lavel_two_Id' => $d->lavel_two_Id,
                 'lb_three_name' => $d->lb_three_name,
                 'lb_three_order_no' => $d->lb_three_order_no,
                 'created_at' => $d->created_at,
                 'updated_at' => $d->updated_at,
                 'category_id' => $d->category_id,
                 'slug' => $d->slug,
                 'ave_discount' => number_format($d->ave_discount),

            ];
        }

        return response()->json($arr);
    }
}