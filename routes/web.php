<?php

use App\Http\Controllers\AdminBuyersController;
use App\Models\User;
use App\Models\Product;
use App\Models\LbLevelOne;
use App\Models\LbLevelTwo;
use App\Models\Leaderboard;
use App\Models\LbLevelThree;
use Illuminate\Http\Request;
use App\Models\DiscountProduct;
use App\Models\LeaderboardList;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\LoginToken;
use App\Http\Controllers\DummyBrands;
use Illuminate\Support\Facades\Route;
use App\Models\LeaderboardListRelationship;
use App\Http\Controllers\AdminLeaderboardList;
use App\Http\Controllers\DiscountListController;
use App\Http\Controllers\PaymentController;
use App\Models\AddToCart;
use App\Models\BuyerUser;
use App\Models\DiscountImages;
use App\Models\ProductImages;





Route::get('/', function () {
    return 'Unauthorize access';
});


Route::prefix('admin')->group(function () {

    Route::get('/users/list', function () {

        //$users = User::where('dummy', 0)->get();

        $users = DB::table('users')
                ->leftJoin('discount_products', 'users.id', '=', 'discount_products.user_id')
                ->where('users.dummy', 0)
                ->select('users.id as uid', 'users.name', 'users.email', 'users.email_verified_at', 'users.private', 'discount_products.discount', 'discount_products.discount_code', 'users.company_slug', 'users.company')
                ->groupBy('users.id')
                ->get();

        foreach($users as $u) {

            //$highestDiscount = DiscountProduct::where('user_id', $u->uid)->max('discount');

            $maxDiscountProduct = Product::where('user_id', $u->uid)->max('discount_percentage');

            $maxDiscountCode = DiscountProduct::where('user_id', $u->uid)->max('discount');

            $max = [ $maxDiscountProduct, $maxDiscountCode ];

            $maxDiscount = max($max);

            $data[] = [
                'name' => $u->name,
                'email' => $u->email,
                'email_verified_at' => $u->email_verified_at,
                'discount' => $maxDiscount ? $maxDiscount : '0',
                'private' => $u->private,
                'company_slug' => $u->company_slug,
                'company' => $u->company,
                'discount_code' => $u->discount_code,
                'uid' => $u->uid,
            ];
        }

        //return $data;

        return view('admin.users_list', ['users' => $data]);

    })->name('users.list');


    Route::get('/user/delete/{uid}', function ($uid) {

        try {
            User::where('id', $uid)->delete();
            Product::where('user_id', $uid)->delete();
            ProductImages::where('user_id', $uid)->delete();
            AddToCart::where('user_id', $uid)->delete();
            DiscountProduct::where('user_id', $uid)->delete();
            DiscountImages::where('user_id', $uid)->delete();

            return redirect(route('users.list'));
        } catch(\Throwable $th) {
            abort('402');
        }


    })->name('user.delete');








    Route::get('/users/list/level/{id}', function (int $id) {

        $one_data = [];
        $two_data = [];
        $three_data = [];

        $three = LbLevelThree::where('user_id', $id)->get();

        foreach($three as $th) {
            $two = LbLevelTwo::where('id', $th->lavel_two_id)->get();

            foreach($two as $tw) {
                $one = LbLevelOne::where('id', $tw->level_one_id)->get();

                foreach($one as $on) {
                    $one_data[] = [
                        'data' => $on->lb_name,
                    ];
                }

                $two_data[] = [
                    'data' => $tw->lb_two_name,
                ];
            }

            $three_data[] = [
                'data' => $th->lb_three_name,

            ];
        }

        $data = [
            'one' => count($one_data) > 0 ? $one_data : [],
            'two' => count($two_data) > 0 ? $two_data : [],
            'three' => count($three_data) > 0 ? $three_data : [],
        ];

        //return $data['one'];
        return view('admin.users_list_levels', ['data' => $data]);


    })->name('users.list.level');



    Route::get('/leaderboard/category', [AdminLeaderboardList::class, 'category'])->name('lb.category');

    Route::post('/leaderboard/category', [AdminLeaderboardList::class, 'category_post'])->name('lb.category.post');

    Route::get('/leaderboard/level/one/{id}', [AdminLeaderboardList::class, 'level_one'])->name('lb.level.one');

    Route::post('/leaderboard/level/one', [AdminLeaderboardList::class, 'level_one_post'])->name('lb.level.one.post');

    Route::get('/leaderboard/level/two/{id}', [AdminLeaderboardList::class, 'level_two'])->name('lb.level.two');

    Route::post('/leaderboard/level/two', [AdminLeaderboardList::class, 'level_two_post'])->name('lb.level.two.post');

    Route::get('/leaderboard/level/three/{id}', [AdminLeaderboardList::class, 'level_three'])->name('lb.level.three');

    Route::post('/leaderboard/level/three', [AdminLeaderboardList::class, 'level_three_post'])->name('lb.level.three.post');

    Route::get('/leaderboard/level/four/{id}', [AdminLeaderboardList::class, 'level_four'])->name('lb.level.four');

    Route::post('/leaderboard/level/four', [AdminLeaderboardList::class, 'level_four_post'])->name('lb.level.four.post');



    // Route::get('/leaderboard/list/category', [AdminLeaderboardList::class, 'lb_list'])->name('leaderboard.list');

    Route::get('/leaderboard/list/lavel/one/{id}', [AdminLeaderboardList::class, 'lb_list_one'])->name('leaderboard.list.one');

    Route::get('/leaderboard/list/lavel/two/{id}', [AdminLeaderboardList::class, 'lb_list_two'])->name('leaderboard.list.two');

    Route::get('/leaderboard/list/lavel/three/{id}', [AdminLeaderboardList::class, 'lb_list_three'])->name('leaderboard.list.three');

    Route::get('/leaderboard/list/lavel/all/{id}', [AdminLeaderboardList::class, 'lb_list_all'])->name('leaderboard.list.all');

    Route::get('/leaderboard/list/brands/{id}/{slug}', [AdminLeaderboardList::class, 'lb_list_brands'])->name('leaderboard.list.brands');

    Route::get('/dummy/brands', [DummyBrands::class, 'index'])->name('dummy.brands');

    Route::post('/dummy/brands/post', [DummyBrands::class, 'index_post'])->name('dummy.brands.post');

    Route::get('/dummy/brands/leaderboard/{id}', [DummyBrands::class, 'brand_leaderboard'])->name('dummy.brands.leaderboard');

    Route::post('/dummy/brands/leaderboard/post', [DummyBrands::class, 'brand_leaderboard_post'])->name('dummy.brands.leaderboard.post');

    Route::get('/level/delete/{id}/{status}', [AdminLeaderboardList::class, 'delete_level'])->name('level.delete');

    Route::get('/level/edit/{id}/{idd}/{status}', [AdminLeaderboardList::class, 'edit_level'])->name('edit.level');

    Route::post('/level/edit/post', [AdminLeaderboardList::class, 'edit_level_post'])->name('edit.level.post');

    Route::get('/level/delete/{id}/{idd}/{status}', [AdminLeaderboardList::class, 'delete_level'])->name('delete.level');


    Route::get('/discount/list/add', [DiscountListController::class, 'index'])->name('discount.list.add');

    Route::post('/discount/list/add/post', [DiscountListController::class, 'index_post'])->name('discount.list.add.post');

    Route::get('/discount/list/category/{did}', [DiscountListController::class, 'category'])->name('discount.list.category');

    Route::post('/discount/list/category/post', [DiscountListController::class, 'category_post'])->name('discount.list.category.post');

    Route::get('/discount/list/level/one/{did}/{cate_id}', [DiscountListController::class, 'level_one'])->name('discount.list.level.one');

    Route::post('/discount/list/level/one/post', [DiscountListController::class, 'level_one_post'])->name('discount.list.level.one.post');

    Route::get('/buyers/list', [AdminBuyersController::class, 'buyers_list'])->name('buyers.list');









});



// payment buyer

Route::get('/pay/subscription/{bid}/{token}', [PaymentController::class, 'buyer_payment'])->name('pay.buyer');
Route::post('/pay/subscription/post', [PaymentController::class, 'buyer_payment_post'])->name('pay.buyer.post');