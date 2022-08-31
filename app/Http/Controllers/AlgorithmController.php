<?php

namespace App\Http\Controllers;

use App\Models\LbLevelOne;
use App\Models\LbLevelTwo;
use App\Models\LbLevelThree;
use App\Models\Leaderboard;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Subscriptions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class AlgorithmController extends Controller
{

    public function discount_level_one($id, $min, $max)
    {
        $arr = [];
        $discountProduct = DB::table('products')
                            ->where('discount_percentage','>=',$min)
                            ->where('discount_percentage','<=',$max);

        $discountCode = DB::table('discount_products')
                            ->where('discount','>=',$min)
                            ->where('discount','<=',$max);



        // $brands = DB::table('users')->where('dummy', 0)->where('private',0);
        $brands = DB::table('users')->where('dummy', 0);

        // $brand_subs = DB::table('subscriptions')->select('user_Id as user_Id', DB::raw('COUNT(buyer_Id) as buyers_no'))->whereDate('end_date', '>=',date('Y-m-d'))->groupBy('user_Id');


        $brand_list_products = DB::table('lb_level_one','one')

                                  ->select('one.Id as id', 'cat.name as category_name', 'one.lb_category_id as lb_category_id', 'one.lb_name as lb_name','one.lb_order_no as lb_order_no', 'one.created_at as created_at','one.updated_at as updated_at', 'one.discount as discount',  DB::raw('( CASE WHEN discount_product.discount_percentage IS NULL THEN  0 ELSE discount_product.discount_percentage END) + ( CASE WHEN discount_code.discount IS NULL THEN  0 ELSE discount_code.discount END) + ( CASE WHEN two.discount IS NULL THEN  0 ELSE two.discount END) + ( CASE WHEN one.discount IS NULL THEN  0 ELSE one.discount END)as total_discount'))

                                   ->leftjoin('lb_level_two as two', 'one.Id','=','two.level_one_id')
                                   ->leftjoin('lb_lavel_three as three', 'two.Id','=','three.lavel_two_Id')
                                   ->leftjoin('lb_category as cat', 'cat.id','=','three.category_id')
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

                                  ->where('three.category_id', '!=', '')
                                  ->where('three.user_id', '!=', '');



       $data = DB::table($brand_list_products,'brand_list_products')
                  ->select('id', 'category_name', 'lb_category_id', 'lb_name','lb_order_no','created_at','updated_at','discount', DB::raw('MAX(total_discount) as ave_discount'))
                  ->where('total_discount','>=',$min)
                  ->where('total_discount','<=',$max)
                  ->groupBy('id')
                  ->get();


        foreach($data as $d) {

             $arr[] = [
                'Id' => $d->id,
                'lb_category_id' => $d->lb_category_id,
                'category_name' => $d->category_name,
                'lb_name' => $d->lb_name,
                'lb_order_no' => $d->lb_order_no,
                'created_at' => $d->created_at,
                'updated_at' => $d->updated_at,
                'discount' => $d->discount,
                'ave_discount' =>  number_format($d->ave_discount),
            ];
        }

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

        $brands = DB::table('users')->where('dummy', 0);


        $brand_list_products = DB::table('lb_level_two', 'two')
                                ->select('two.Id as id', 'cat.name as category_name', 'two.level_one_Id as level_one_Id', 'two.lb_two_name as lb_two_name','two.lb_two_order_no as lb_two_order_no','two.created_at as created_at', 'two.updated_at as updated_at', 'two.discount as discount',   DB::raw('( CASE WHEN discount_product.discount_percentage IS NULL THEN  0 ELSE discount_product.discount_percentage END) + ( CASE WHEN discount_code.discount IS NULL THEN  0 ELSE discount_code.discount END) + ( CASE WHEN two.discount IS NULL THEN  0 ELSE two.discount END) + ( CASE WHEN one.discount IS NULL THEN  0 ELSE one.discount END)as total_discount'))

                                   ->leftjoin('lb_level_one as one', 'one.Id','=','two.level_one_id')
                                   ->leftjoin('lb_lavel_three as three', 'two.Id','=','three.lavel_two_Id')
                                   ->leftjoin('lb_category as cat', 'cat.id','=','three.category_id')
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

                                  ->where('two.level_one_id', $id)
                                  ->where('three.category_id', '!=', '')
                                  ->where('three.user_id', '!=', '');



       $data = DB::table($brand_list_products,'brand_list_products')
                  ->select('id', 'category_name', 'level_one_Id', 'lb_two_name','lb_two_order_no','created_at','updated_at','discount', DB::raw('MAX(total_discount) as ave_discount'))
                  ->where('total_discount','>=',$min)
                  ->where('total_discount','<=',$max)
                  ->groupBy('id')
                  ->get();


        foreach($data as $d) {

             $arr[] = [
                'Id' => $d->id,
                'level_one_Id' => $d->level_one_Id,
                'category_name' => $d->category_name,
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

        $brands = DB::table('users')->where('dummy', 0);


        $brand_list_products = DB::table('lb_lavel_three','three')
                                ->select('three.Id as id', 'cat.name as category_name', 'three.lavel_two_Id as lavel_two_Id', 'three.lb_three_name as lb_three_name','three.lb_three_order_no as lb_three_order_no','three.created_at as created_at', 'three.updated_at as updated_at', 'three.user_id as user_id', 'three.category_id as category_id','three.slug as slug',   DB::raw('( CASE WHEN discount_product.discount_percentage IS NULL THEN  0 ELSE discount_product.discount_percentage END) + ( CASE WHEN discount_code.discount IS NULL THEN  0 ELSE discount_code.discount END) + ( CASE WHEN two.discount IS NULL THEN  0 ELSE two.discount END) + ( CASE WHEN one.discount IS NULL THEN  0 ELSE one.discount END)as total_discount'))
                                   ->leftjoin('lb_level_two as two', 'two.Id','=','three.lavel_two_Id')

                                   ->leftjoin('lb_level_one as one', 'one.Id','=','two.level_one_id')
                                   ->leftjoin('lb_category as cat', 'cat.id','=','three.category_id')
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

                                   ->where('three.lavel_two_id', $id)
                                   ->where('three.category_id', '!=', '')
                                  ->where('three.user_id', '!=', '');



       $data = DB::table($brand_list_products,'brand_list_products')
                  ->select('id', 'category_name', 'lavel_two_Id', 'lb_three_name','lb_three_order_no','created_at','updated_at','user_id','category_id','slug', DB::raw('MAX(total_discount) as ave_discount'))
                  ->where('total_discount','>=',$min)
                  ->where('total_discount','<=',$max)
                  ->groupBy('id')
                  ->get();


        foreach($data as $d) {

              $arr[] = [
                 'Id' => $d->id,
                 'lavel_two_Id' => $d->lavel_two_Id,
                 'category_name' => $d->category_name,
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



    public function fake_subs($uid, $number)
    {
        // $start_date = date('Y-m-d H:i:s');
        // $end_date = date('Y-m-d H:i:s', strtotime("+30 days"));
        // $ids = [];

        // for($n = 1; $n <= $number; $n++) {
        //     $subs = new Subscriptions;

        //     $subs->buyer_id = 0;
        //     $subs->user_id = $uid;
        //     $subs->start_date = $start_date;
        //     $subs->end_date = $end_date;
        //     $subs->status = 1;

        //     $subs->save();

        //     $ids[] = $subs->id;
        // }


        // return response()->json(['id' => $ids]);

    }


}