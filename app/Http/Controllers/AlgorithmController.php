<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\BrandPhoto;
use App\Models\LbLevelOne;
use App\Models\LbLevelTwo;
use App\Models\Leaderboard;
use Illuminate\Support\Arr;
use App\Models\LbLevelThree;
use Illuminate\Http\Request;
use App\Models\Subscriptions;
use App\Models\DiscountProduct;
use Illuminate\Support\Facades\DB;

class AlgorithmController extends Controller
{

    public function discount_level_one($id, $min, $max)
    {
        $arr = [];

        $maxDiscountProduct = DB::table('products')->select('user_id as user_id', DB::raw('MAX(discount_percentage) as max_discount_percentage'))->groupBy('user_id');

        $maxDiscountCode = DB::table('discount_products')->select('user_id as user_id', DB::raw('MAX(discount) as max_discount'))->groupBy('user_id');

        $brands = DB::table('users')->where('dummy', 0)->where('private',0);

        $brand_subs = DB::table('subscriptions')->select('user_Id as user_Id', DB::raw('COUNT(buyer_Id) as buyers_no'))->whereDate('end_date', '>=',date('Y-m-d'))->groupBy('user_Id');

        $data= DB::table('lb_level_one', 'one')
                  ->select('cat.name as category_name', 'one.Id as id', 'one.lb_category_id as lb_category_id', 'one.lb_name as lb_name','one.lb_order_no as lb_order_no', 'one.created_at as created_at','one.updated_at as updated_at', 'one.discount as discount',DB::raw('MAX(brand_subs.buyers_no) as buyers_no'), DB::raw('MAX(max_discount_product.max_discount_percentage) as max_discount_percentage'),  DB::raw('MAX(max_discount_code.max_discount ) as max_discount'))
                  ->leftjoin('lb_level_two as two', 'one.Id','=','two.level_one_id')
                  ->leftjoin('lb_lavel_three as three', 'two.Id','=','three.lavel_two_Id')
                  ->leftjoin('lb_category as cat', 'cat.id','=','one.lb_category_id')
                  ->leftjoinSub(
                            $brands, 'brands',
                            function($join)
                            {
                                $join->on('three.user_Id', '=', 'brands.Id');
                            })
                  ->leftjoinSub(
                            $brand_subs, 'brand_subs',
                            function($join)
                            {
                                $join->on('brands.Id', '=', 'brand_subs.user_Id');
                            })
                  ->leftjoinSub(
                            $maxDiscountProduct, 'max_discount_product',
                            function($join)
                            {
                                $join->on('max_discount_product.user_id', '=', 'brand_subs.user_Id');
                            })
                  ->leftjoinSub(
                            $maxDiscountCode, 'max_discount_code',
                            function($join)
                            {
                                $join->on('max_discount_code.user_id', '=', 'brand_subs.user_Id');
                            })
                  //->where('one.lb_category_id', $id)
                  ->groupBy('one.Id')
                  ->orderBy('buyers_no','desc')
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
                //'discount' => $d->discount,
                'discount' => max([$d->max_discount_percentage, $d->max_discount ]),
                'buyers_no' => $d->buyers_no
            ];
        }

        $final_data = [];
        foreach($arr as $d) {
            if($d['discount'] >= $min && $d['discount'] <= $max) {
                $final_data[] = [
                    'Id' => $d['Id'],
                    'lb_category_id' => $d['lb_category_id'],
                    'category_name' => $d['category_name'],
                    'lb_name' => $d['lb_name'],
                    'lb_order_no' => $d['lb_order_no'],
                    'created_at' => $d['created_at'],
                    'updated_at' => $d['updated_at'],
                    'ave_discount' => $d['discount'],
                    'totalsubs' => $d['buyers_no']
                ];
            }
        }

        return response()->json($final_data);
    }

    public function discount_level_two($id,$min,$max)
    {
        $arr = [];

        $maxDiscountProduct = DB::table('products')->select('user_id as user_id', DB::raw('MAX(discount_percentage) as max_discount_percentage'))->groupBy('user_id');

        $maxDiscountCode = DB::table('discount_products')->select('user_id as user_id', DB::raw('MAX(discount) as max_discount'))->groupBy('user_id');

        $brands = DB::table('users')->where('dummy', 0)->where('private',0);

        $brand_subs = DB::table('subscriptions')->select('user_Id as user_Id', DB::raw('COUNT(buyer_Id) as buyers_no'))->whereDate('end_date', '>=',date('Y-m-d'))->groupBy('user_Id');

        $data= DB::table('lb_level_two', 'two')
                  ->select('cat.name as category_name', 'two.Id as id', 'two.level_one_Id as level_one_Id', 'two.lb_two_name as lb_two_name','two.lb_two_order_no as lb_two_order_no','two.created_at as created_at', 'two.updated_at as updated_at', 'two.discount as discount', DB::raw('MAX(brand_subs.buyers_no) as buyers_no'), DB::raw('MAX(max_discount_product.max_discount_percentage) as max_discount_percentage'),  DB::raw('MAX(max_discount_code.max_discount ) as max_discount'))
                  ->leftjoin('lb_lavel_three as three', 'three.lavel_two_Id','=','two.Id')
                  ->leftjoin('lb_category as cat', 'cat.id','=','three.category_id')
                  ->leftjoinSub(
                            $brands, 'brands',
                            function($join)
                            {
                                $join->on('three.user_id', '=', 'brands.Id');
                            })
                  ->leftjoinSub(
                            $brand_subs, 'brand_subs',
                            function($join)
                            {
                                $join->on('brands.Id', '=', 'brand_subs.user_Id');
                            })
                  ->leftjoinSub(
                            $maxDiscountProduct, 'max_discount_product',
                            function($join)
                            {
                                $join->on('max_discount_product.user_id', '=', 'brand_subs.user_Id');
                            })
                  ->leftjoinSub(
                            $maxDiscountCode, 'max_discount_code',
                            function($join)
                            {
                                $join->on('max_discount_code.user_id', '=', 'brand_subs.user_Id');
                            })
                  ->where('two.level_one_id', $id)
                 ->groupBy('two.Id')
                  ->orderBy('buyers_no','desc')
                  ->get();

        foreach($data as $d) {

             $arr[] = [
                'Id' => $d->id,
                'category_name' => $d->category_name,
                'level_one_Id' => $d->level_one_Id,
                'lb_two_name' => $d->lb_two_name,
                'lb_two_order_no' => $d->lb_two_order_no,
                'created_at' => $d->created_at,
                'updated_at' => $d->updated_at,
                //'discount' => $d->discount,
                'discount' => max([$d->max_discount_percentage, $d->max_discount ]),
                'buyers_no' => $d->buyers_no
            ];
        }

        $final_data = [];
        foreach($arr as $d) {
            if($d['discount'] >= $min && $d['discount'] <= $max) {
                $final_data[] = [
                    'Id' => $d['Id'],
                    // 'lb_category_id' => $d['lb_category_id'],
                    'category_name' => $d['category_name'],
                    'lb_two_name' => $d['lb_two_name'],
                    'lb_order_no' => $d['lb_two_order_no'],
                    'created_at' => $d['created_at'],
                    'updated_at' => $d['updated_at'],
                    'ave_discount' => $d['discount'],
                    'totalsubs' => $d['buyers_no']
                ];
            }
        }

        return response()->json($final_data);

    }

    public function discount_level_three($id,$min,$max)
    {
        $arr = [];
        $maxDiscountProduct = DB::table('products')->select('user_id as user_id', DB::raw('MAX(discount_percentage) as max_discount_percentage'))->groupBy('user_id');

        $maxDiscountCode = DB::table('discount_products')->select('user_id as user_id', DB::raw('MAX(discount) as max_discount'))->groupBy('user_id');
        $brands = DB::table('users')->where('dummy', 0)->where('private',0);

        $brand_subs = DB::table('subscriptions')->select('user_Id as user_Id', DB::raw('COUNT(buyer_Id) as buyers_no'))->whereDate('end_date', '>=',date('Y-m-d'))->groupBy('user_Id');

         $data= DB::table('lb_lavel_three','three')
                  ->select('cat.name as category_name', 'three.Id as id', 'three.lavel_two_Id as lavel_two_Id', 'three.lb_three_name as lb_three_name','three.lb_three_order_no as lb_three_order_no','three.created_at as created_at', 'three.updated_at as updated_at', 'three.user_id as user_id', 'three.category_id as category_id','three.slug as slug',  DB::raw('MAX(brand_subs.buyers_no) as buyers_no'), DB::raw('MAX(max_discount_product.max_discount_percentage) as max_discount_percentage'),  DB::raw('MAX(max_discount_code.max_discount ) as max_discount'))
                  ->leftjoin('lb_category as cat', 'cat.id','=','three.category_id')
                  ->leftjoinSub(
                            $brands, 'brands',
                            function($join)
                            {
                                $join->on('brands.Id', '=', 'three.user_Id');
                            })
                  ->leftjoinSub(
                            $brand_subs, 'brand_subs',
                            function($join)
                            {
                                $join->on('brands.Id', '=', 'brand_subs.user_Id');
                            })
                  ->leftjoinSub(
                            $maxDiscountProduct, 'max_discount_product',
                            function($join)
                            {
                                $join->on('max_discount_product.user_id', '=', 'brand_subs.user_Id');
                            })
                  ->leftjoinSub(
                            $maxDiscountCode, 'max_discount_code',
                            function($join)
                            {
                                $join->on('max_discount_code.user_id', '=', 'brand_subs.user_Id');
                            })
                  ->where('three.lavel_two_id', $id)
                  ->groupBy( 'three.lb_three_name')
                  ->orderBy('buyers_no','desc')
                  ->get();


        foreach($data as $d) {

            $arr[] = [
                 'Id' => $d->id,
                 'category_name' => $d->category_name,
                 'lavel_two_Id' => $d->lavel_two_Id,
                 'lb_three_name' => $d->lb_three_name,
                 'lb_three_order_no' => $d->lb_three_order_no,
                 'created_at' => $d->created_at,
                 'updated_at' => $d->updated_at,
                 'category_id' => $d->category_id,
                 'slug' => $d->slug,
                 'discount' => max([$d->max_discount_percentage, $d->max_discount ]),
                'buyers_no' => $d->buyers_no
            ];
        }

        $final_data = [];
        foreach($arr as $d) {
            if($d['discount'] >= $min && $d['discount'] <= $max) {
                $final_data[] = [
                    'Id' => $d['Id'],
                    // 'lb_category_id' => $d['lb_category_id'],
                    'category_name' => $d['category_name'],
                    'lb_three_name' => $d['lb_three_name'],
                    'lb_three_order_no' => $d['lb_three_order_no'],
                    'created_at' => $d['created_at'],
                    'updated_at' => $d['updated_at'],
                    'ave_discount' => $d['discount'],
                    'totalsubs' => $d['buyers_no'],
                    'slug' => $d['slug'],
                ];
            }
        }

        return response()->json($final_data);
    }


    public function discount_leaderboards($slug, $min, $max)
    {
        $arr = [];
        $userIds = [];

        $level_three_user_id = LbLevelThree::where('slug', $slug)->where('user_id', '!=', '')->where('category_id', '!=', '')->select('user_id')->get();

        foreach($level_three_user_id as $th_id) {
            $userIds[] = $th_id->user_id;
        }


       $data = DB::table('users')
       ->whereIn('users.id', $userIds)
       ->select('users.id as uid', 'users.company', 'users.company_slug', 'users.dummy_customers', 'users.dummy_sales', 'users.dummy_discount', 'users.dummy', 'users.private')
       ->groupBy('users.company')
       ->get();



        foreach($data as $d) {

            //$brandPhotos = BrandPhoto::where('user_id', $d->uid)->where('type', 'photos_of_brand')->take(4)->get();

            $brandphoto_brand = BrandPhoto::where('user_id', $d->uid)->where('type', 'brand')->take(1)->get();
            $brandphoto_main = BrandPhoto::where('user_id', $d->uid)->where('type', 'main')->take(1)->get();

            $maxDiscountProduct = Product::where('user_id', $d->uid)->max('discount_percentage');

            $maxDiscountCode = DiscountProduct::where('user_id', $d->uid)->max('discount');

            $maxx = [ $maxDiscountProduct, $maxDiscountCode ];

            $maxDiscount = max($maxx);

            if(count($brandphoto_brand) > 0) {
                $brandPhotos = $brandphoto_brand;
            } elseif(count($brandphoto_main) > 0) {
                $brandPhotos = $brandphoto_main;
            } else {
                $brandPhotos = "https://www.goodyellowco.com/assets/imgs/default_gy.webp";
            }

            if($maxDiscount >= $min && $maxDiscount <= $max) {
                $arr[] = [
                    'user_id' => $d->uid,
                    'company' => $d->company,
                    'slug' => $d->company_slug,
                    //'leaderboard' => $d->title,
                    'sales' => '0',
                    'customers' => '0',
                    'brand_photos' => $brandPhotos[0]->url ?? '',
                    'discount' => number_format($maxDiscount),
                    'dummy_customers' => $d->dummy_customers,
                    'dummy_sales' => $d->dummy_sales,
                    'dummy_discount' => $d->dummy_discount,
                    'dummy' => $d->dummy,
                    'private' => $d->private,
                ];
            }
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