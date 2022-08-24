<?php

use App\Models\User;
use App\Mail\Support;
use Illuminate\Stripe;
use App\Models\Credits;
use App\Models\Product;
use App\Models\BrandFaq;
use Stripe\Subscription;
use App\Models\BrandInfo;
use App\Models\BuyerUser;
use App\Mail\Verification;
use App\Models\Attributes;
use App\Models\BrandCause;
use App\Models\BrandLinks;
use App\Models\BrandPhoto;
use App\Models\LbLevelOne;
use App\Models\LbLevelTwo;
use App\Models\Variations;
use App\Models\BrandPeople;
use App\Models\Leaderboard;
use Illuminate\Support\Str;
use App\Mail\Forgotpassword;
use App\Models\DiscountList;
use App\Models\LbLevelThree;
use App\Models\ShippingCost;
use App\Models\VendorStripe;
use Illuminate\Http\Request;
use App\Models\BrandCategory;
use App\Models\ProductImages;
use App\Models\ResetPassword;
use App\Models\Subscriptions;
use App\Models\DiscountImages;
use App\Models\BrandGoodPlanet;
use App\Models\DiscountProduct;
use App\Models\LeaderboardList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MembershipController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// buyer part api ----------------------------------------------------

Route::prefix('buyer')->group(function () {

    Route::post('/cart/post', [CartController::class, 'cart_post']);
    Route::get('/cart/{id}', [CartController::class, 'cart']);

    Route::post('/checkout', [CheckoutController::class, 'checkout']);

    Route::get('/checkout/brand/info/{uid}/{bid}', [CheckoutController::class, 'brand_info']);

    Route::get('/placed/order/{bid}', [OrderController::class, 'placed_order']);
    Route::get('/shippid/order/{bid}', [OrderController::class, 'shippid_order']);
    Route::get('/cancelled/order/{bid}', [OrderController::class, 'cancelled_order']);

    Route::get('/membership/{bid}', [MembershipController::class, 'index']);

    Route::get('/cart/remove/all/{uid}/{bid}', [CartController::class, 'removeAll']);
    Route::get('/cart/remove/one/{cartid}', [CartController::class, 'removeOne']);



});

Route::get('/seller/placed/order/{bid}', [OrderController::class, 'seller_placed_order']);
Route::get('/seller/shippid/order/{bid}', [OrderController::class, 'seller_shippid_order']);
Route::get('/seller/cancelled/order/{bid}', [OrderController::class, 'seller_cancelled_order']);

Route::get('/seller/order/status/change/{uid}/{order}/{status}', [OrderController::class, 'seller_order_status_change']);



// seller part api ----------------------------------------------------

Route::middleware(['log_token'])->prefix('u')->group(function () {
    Route::get('/login', function () {
        return 'successfully logged in.';
    });

    // https://connect.stripe.com/setup/c/acct_1LJDRKRjRabniVRg/QvpJ7nALXUge
    // Test account ID: acct_1LJDRKRjRabniVRg

    Route::get('/setup/stripe/vendor/emailid/{id}', function ($id) {

        //$userEmail = User::where('id', $id)->select('stripe_email')->first();
        $vendorStripe = VendorStripe::where('user_id', $id)->select('api_key', 'secret_key', 'user_id')->first();

        return response()->json(['data' => $vendorStripe]);

    });


    Route::post('/setup/stripe/vendor', function (Request $request) {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY_PROD'));

        $user = User::where('id', $request->user_id)->select('remember_token')->first();

        // Create vendor stripe account with us

        $acc = $stripe->accounts->create([
            'type' => 'custom',
            'country' => 'US',
            'email' => $request->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);

        // Generate account authorization link

        User::where('id', $request->user_id)->update(['stripe_account_id' => $acc->id, 'stripe_email' => $request->email]); // Update stripe account id...

        $link = $stripe->accountLinks->create(
            [
                'account' => $acc->id,
                'refresh_url' => env('APP_URL').'/api/u/reauth?token=' . $user->remember_token,
                'return_url' => env('APP_URL').'/api/u/return/'.$acc->id.'?token=' . $user->remember_token,
                'type' => 'account_update',
            ]
        );

        // get the url

        $url = $link->url;
        return json_encode($url);
    });

    Route::get('/return/{id}', function ($id) {

        $stripe = new \Stripe\StripeClient('sk_test_51KGEe3DYq9pFsvI8w2hU8k6ZHbTy6uQxockIk8Tr243FbKMVyQk3hNQ7AnpdH8IlVIsOKP0wxPvJQyQfaDd0ivwC00awYenRZy');

        $acc = $stripe->accounts->retrieve($id);
        if($acc->capabilities->card_payments == 'active' && $acc->capabilities->transfers == 'active') {
            return '<h1>Stripe Account Connected Successfully. Now you can close this tab or window.</h1>';
        } else {
            $html = '<h1>Your card payments is '.$acc->capabilities->card_payments.'.</h1>';
            $html .= '<h1>Your transfer is '.$acc->capabilities->transfers.'.</h1>';
            $html .= 'Please re connect the stripe account again.';
            return $html;
        }


    });

    Route::get('/reauth', function () {
        return '<h4>Error! Please close this window or tab and re connect your stripe account from your Storefront.</h4>';
    });

    Route::get('/vendor/leaderboard/dropdown/{id}', function ($id) {
        $data = Leaderboard::where('category', $id)->get();
        return json_encode($data);
    });

    Route::get('/vendor/category/{id}', function ($id) {
        $data = LbLevelThree::where('user_id', $id)->where('category_id', 1)->get();
        return json_encode($data);
    });

    Route::get('/vendor/level/three/delete/{id}/{uid}', function ($id, $uid) {
        LbLevelThree::where('id',$id)->where('user_id', $uid)->delete();
        return json_encode('deleted');
    });

    Route::get('/vendor/cause/{id}', function ($id) {
        $data = LbLevelThree::where('user_id', $id)->where('category_id', 2)->get();
        return json_encode($data);
    });

    Route::get('/vendor/cause/delete/{id}', function ($id) {
        Leaderboard::where('id',$id)->delete();
        return json_encode('deleted');
    });

    Route::get('/vendor/planets/{id}', function ($id) {
        $data = LbLevelThree::where('user_id', $id)->where('category_id', 3)->get();
        return json_encode($data);
    });

    Route::get('/vendor/planets/delete/{id}', function ($id) {
        Leaderboard::where('id',$id)->delete();
        return json_encode('deleted');
    });

    Route::get('/vendor/peoples/{id}', function ($id) {
        $data = LbLevelThree::where('user_id', $id)->where('category_id', 4)->get();
        return json_encode($data);
    });

    Route::get('/vendor/peoples/delete/{id}', function ($id) {
        Leaderboard::where('id',$id)->delete();
        return json_encode('deleted');
    });

    Route::get('/vendor/founders/{id}', function ($id) {
        $data = BrandInfo::where('user_id', $id)
        ->where('key', 'founder_story')
        ->select('txt', 'id')
        ->first();
        return json_encode([$data->txt, $data->id]);
    });

    Route::get('/vendor/brand/description/{id}', function ($id) {
        $data = BrandInfo::where('user_id', $id)
        ->where('key', 'brand_description')
        ->select('txt', 'id')
        ->first();
        return json_encode([$data->txt, $data->id]);
    });

    Route::get('/vendor/people/story/{id}', function ($id) {
        $data = BrandInfo::where('user_id', $id)
        ->where('key', 'people_story')
        ->select('txt', 'id')
        ->first();
        return json_encode($data->txt);
    });

    Route::get('/vendor/brand/story/{id}', function ($id) {
        $data = BrandInfo::where('user_id', $id)
        ->where('key', 'brand_story')
        ->select('txt', 'id')
        ->first();
        return json_encode($data->txt);
    });

    Route::get('/vendor/brand/video/{id}', function ($id) {
        $data = BrandInfo::where('user_id', $id)
        ->where('key', 'brand_video')
        ->select('txt', 'id')
        ->first();
        return json_encode($data->txt);
    });

    Route::get('/vendor/brand/links/{id}', function ($id) {
        $data = BrandLinks::where('user_id', $id)
        ->first();
        return json_encode($data);
    });


    // step 2
    Route::post('/vendor/signup/add/category', function (Request $request) {
        $brandcategory = new Leaderboard;

        $brandcategory->category = 'Brand';
        $brandcategory->user_id = $request->user_id;
        $brandcategory->title = $request->category;

        $slug = Str::slug($request->category, '-');
        $brandcategory->slug = $slug;

        $brandcategory->save();

        $data = [];
        $data = [
            'id' => $brandcategory->id,
        ];

        return json_encode($data);
    });

    Route::post('/vendor/signup/add/cause', function (Request $request) {
        $brandcategory = new Leaderboard;

        $brandcategory->category = 'Causes';
        $brandcategory->user_id = $request->user_id;
        $brandcategory->title = $request->cause;

        $slug = Str::slug($request->cause, '-');
        $brandcategory->slug = $slug;

        $brandcategory->save();

        $data = [];
        $data = [
            'id' => $brandcategory->id,
        ];

        return json_encode($data);
    });

    Route::post('/vendor/signup/add/planets', function (Request $request) {
        $brandcategory = new Leaderboard;

        $brandcategory->category = 'Good for planet';
        $brandcategory->user_id = $request->user_id;
        $brandcategory->title = $request->planet;

        $slug = Str::slug($request->planet, '-');
        $brandcategory->slug = $slug;

        $brandcategory->save();

        $data = [];
        $data = [
            'id' => $brandcategory->id,
        ];

        return json_encode($data);
    });

    Route::post('/vendor/signup/add/peoples', function (Request $request) {
        $brandcategory = new Leaderboard;

        $brandcategory->category = 'Good for people';
        $brandcategory->user_id = $request->user_id;
        $brandcategory->title = $request->people;

        $slug = Str::slug($request->people, '-');
        $brandcategory->slug = $slug;

        $brandcategory->save();

        $data = [];
        $data = [
            'id' => $brandcategory->id,
        ];

        return json_encode($data);
    });

    Route::post('/vendor/signup/brand/photos', function (Request $request) {

        $path = public_path();

        if ($request->main) {

            $uniq = time() . uniqid();
            $filename = $uniq . '.jpeg';
            $image_base64 = base64_decode($request->main);
            $upl = file_put_contents($path . '/uploads/' . $filename, $image_base64);

            if($upl != false) {
                $brandPhoto = new BrandPhoto;

                $brandPhoto->url = $filename;
                $brandPhoto->user_id = $request->user_id;
                $brandPhoto->type = 'main';

                $brandPhoto->save();
            }

        }

        if ($request->cover) {
            $uniq = time() . uniqid();
            $filename = $uniq . '.jpeg';
            $image_base64 = base64_decode($request->cover);
            $upl = file_put_contents($path . '/uploads/' . $filename, $image_base64);

            if($upl != false) {
                $brandPhoto = new BrandPhoto;

                $brandPhoto->url = $filename;
                $brandPhoto->user_id = $request->user_id;
                $brandPhoto->type = 'cover';

                $brandPhoto->save();
            }

        }

        if ($request->brand) {
            $uniq = time() . uniqid();
            $filename = $uniq . '.jpeg';
            $image_base64 = base64_decode($request->brand);
            $upl = file_put_contents($path . '/uploads/' . $filename, $image_base64);

            if($upl != false) {
                $brandPhoto = new BrandPhoto;

                $brandPhoto->url = $filename;
                $brandPhoto->user_id = $request->user_id;
                $brandPhoto->type = 'brand';

                $brandPhoto->save();
            }

        }

        if ($request->photos_of_brand) {
            $uniq = time() . uniqid();
            $filename = $uniq . '.jpeg';
            $image_base64 = base64_decode($request->photos_of_brand);
            $upl = file_put_contents($path . '/uploads/' . $filename, $image_base64);

            if($upl != false) {
                $brandPhoto = new BrandPhoto;

                $brandPhoto->url = $filename;
                $brandPhoto->user_id = $request->user_id;
                $brandPhoto->type = 'photos_of_brand';

                $brandPhoto->save();
            }

        }

        return json_encode($filename);
    });


    Route::post('/vendor/signup/brand/info', function (Request $request) {

        $brandinfo = BrandInfo::updateOrCreate
        (
            [
                'user_id' => $request->user_id,
                'key' => $request->key
            ],
            [
                'key' => $request->key,
                'txt' => $request->txt,
                'user_id' => $request->user_id
            ],
        );

        $data = [];
        $data = [
            'id' => $brandinfo->id,
        ];

        return json_encode($data);
    });


    Route::post('/vendor/signup/brand/links', function (Request $request) {

        $brandlinks = BrandLinks::updateOrCreate
        (
            [
                'user_id' => $request->user_id,
            ],
            [
                'website' => $request->website,
                'email' => $request->email,
                'social' => $request->social,
                'user_id' => $request->user_id
            ],
        );


        // $brandlinks = new BrandLinks;

        // $brandlinks->website = $request->website;
        // $brandlinks->email = $request->email;
        // $brandlinks->social = $request->social;
        // $brandlinks->user_id = $request->user_id;

        // $brandlinks->save();

        $data = [];
        $data = [
            'id' => $brandlinks->id,
        ];

        return json_encode($data);
    });


    Route::post('/vendor/signup/brand/faqs', function (Request $request) {
        $brandfaqs = new BrandFaq;

        $brandfaqs->question = $request->question;
        $brandfaqs->answer = $request->answer;
        $brandfaqs->user_id = $request->user_id;

        $brandfaqs->save();

        $data = [];
        $data = [
            'id' => $brandfaqs->id,
        ];

        return json_encode($data);
    });

    Route::get('/vendor/brand/photos/{id}', function ($id) {
        $data  = BrandPhoto::where('user_id', '=', $id)->whereIn('type', ['main', 'cover', 'brand'])->select('url', 'id', 'type')->get();
        return response()->json($data);
    });

    Route::get('/vendor/brand/photos/delete/{id}', function ($id) {
        $data  = BrandPhoto::where('id', '=', $id)->delete();
        return response()->json('success');
    });

    Route::get('/vendor/brand/photos_of_brand/{id}', function ($id) {
        $data  = BrandPhoto::where('user_id', '=', $id)->whereIn('type', ['photos_of_brand'])->select('url', 'id', 'type')->get();
        return response()->json($data);
    });

    Route::get('/vendor/brand/faq/{id}', function ($id) {
        $data  = BrandFaq::where('user_id', '=', $id)->select('question', 'answer')->get();
        return response()->json($data);
    });

    //create discount product

    Route::post('/vendor/create/product/discount', function (Request $request) {

        $validator = Validator::make($request->all(), [
            'price' => 'required',
            'name' => 'required',
            'product_description' => 'required',
            'user_id' => 'required',
            'disc_code' => 'required',
            'disc_how_many' => 'required',
            'discount_percentage' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        } else {
            $product = new DiscountProduct;

            $product->price = $request->price;
            $product->name = $request->name;
            $product->description = $request->product_description;
            $product->discount = $request->discount_percentage != 'null' ? $request->discount_percentage : 0;

            $product->discount_code = $request->disc_code;
            $product->how_many_discount_code = $request->disc_how_many;

            $product->exclusive_disc_good_yellow = $request->exclusive_discount;
            $product->user_id = $request->user_id;

            $product->save();

            return response()->json(['id' => $product->id], 200);
        }

    });

    Route::post('/vendor/create/product/discount/photo', function (Request $request) {

        $validator = Validator::make($request->all(), [
            'main' => 'required',
            'user_id' => 'required',
            'product_id' => 'required'
        ], [
            'main' => 'Your image should be less than 1MB and file type should be jpeg,png,jpg!',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        } else {
            $path = public_path();

            $uniq = time() . uniqid();
            $filename = $uniq . '.jpeg';
            $image_base64 = base64_decode($request->main);
            $upl = file_put_contents($path . '/uploads/' . $filename, $image_base64);

            if($upl != false) {
                $prod_images = new DiscountImages;

                $prod_images->url = $filename;
                $prod_images->user_id = $request->user_id;
                $prod_images->discount_prod_id = $request->product_id;

                $prod_images->save();
            }


            return json_encode($filename);
        }
    });

    Route::get('/vendor/get/product/discount/photos/{id}/{pid}', function ($id, $pid) {

        $photos = DiscountImages::where('user_id', $id)->where('discount_prod_id', $pid)->get();

        return json_encode($photos);
    });

    Route::get('/vendor/edit/product/discount/photo/delete/{id}', function ($id) {
        $pimages = DiscountImages::where('id', $id)->select('url')->first();
        $path = public_path();
        $url = $path . '/uploads/' . $pimages->url;

        DiscountImages::where('id',$id)->delete();
        unlink($url);
        return json_encode('deleted');
    });

    // create product

    Route::post('/vendor/create/product', function (Request $request) {

        $validator = Validator::make($request->all(), [
            'price' => 'required',
            'name' => 'required',
            'shipping_time' => 'required',
            // 'category' => 'required',
            // 'sub_category' => 'required',
            'product_description' => 'required',
            'inventory' => 'required',
            'user_id' => 'required',
            //'shipping_cost' => 'required|numeric|min:0|max:500',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        } else {
            $product = new Product;

            $product->p_price = $request->price;
            $product->p_name = $request->name;
            $product->p_shipping_time = $request->shipping_time;
            $product->category_id = $request->category;
            $product->subcate_id = $request->sub_category;
            $product->p_desc = $request->product_description;
            $product->shipping_interval = $request->shipping_interval;
            $product->type_discount = $request->type_discount;
            $product->inventory = $request->inventory;
            $product->discount_percentage = $request->discount_percentage != 'null' ? $request->discount_percentage : 0;
            $product->end_discount = $request->discount_end_date;
            $product->important_details = $request->important_details;
            $product->exclus_discount = $request->exclusive_discount;
            $product->exclus_product = $request->exclusive_product;
            $product->user_id = $request->user_id;
            $product->shipping_cost = $request->shipping_cost;

            $product->save();

            return response()->json(['id' => $product->id], 200);
        }

    });

    Route::post('/vendor/create/product/shipping/cost', function (Request $request) {

        $validator = Validator::make($request->all(), [
            'min' => 'required|numeric',
            'max' => 'required|numeric',
            'cost' => 'required|numeric|min:0|max:500',
            'user_id' => 'required|numeric',
            'product_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        } else {

            $shipcost = new ShippingCost;

            $shipcost->user_id = $request->user_id;
            $shipcost->product_id = $request->product_id;
            $shipcost->ship_from_quantity = $request->min;
            $shipcost->ship_to_quantity = $request->max;
            $shipcost->ship_cost = $request->cost;
            $shipcost->product_id = $request->product_id;

            $shipcost->save();

            return response()->json(['id' => $shipcost->id]);
        }

    });

    Route::post('/vendor/create/product/photo', function (Request $request) {

        $validator = Validator::make($request->all(), [
            'main' => 'required',
            'user_id' => 'required',
            'product_id' => 'required'
        ], [
            'main' => 'Your image should be less than 1MB and file type should be jpeg,png,jpg!',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        } else {
            $path = public_path();

            $uniq = time() . uniqid();
            $filename = $uniq . '.jpeg';
            $image_base64 = base64_decode($request->main);
            $upl = file_put_contents($path . '/uploads/' . $filename, $image_base64);

            if($upl != false) {
                $prod_images = new ProductImages;

                $prod_images->url = $filename;
                $prod_images->user_id = $request->user_id;
                $prod_images->product_id = $request->product_id;

                $prod_images->save();
            }


            return json_encode($filename);
        }
    });

    Route::get('/vendor/get/product/photos/{id}/{pid}', function ($id, $pid) {

        $photos = ProductImages::where('user_id', $id)->where('product_id', $pid)->get();

        return json_encode($photos);
    });


    Route::post('/vendor/create/product/attributes', function (Request $request) {

        $attributes = Attributes::where('user_id', $request->user_id)
        ->where('product_id', $request->product_id)
        ->where('key', $request->key)
        ->get();

        if(count($attributes) === 0) {
            $attr = new Attributes;

            $attr->user_id = $request->user_id;
            $attr->product_id = $request->product_id;
            $attr->key = $request->key;
            $attr->value = $request->value;

            $attr->save();

            return json_encode(['id' => $attr->id]);
        } else {
            return json_encode(['err' => 'Already added.']);
        }

    });

    Route::get('/vendor/get/product/variations/{id}/{pid}', function ($id, $pid) {

        $attr_size = Attributes::where('user_id', $id)
        ->where('product_id', $pid)
        ->where('key', 'Size')
        ->select('id', 'value')
        ->first();

        $attr_color = Attributes::where('user_id', $id)
        ->where('product_id', $pid)
        ->where('key', 'Color')
        ->select('id', 'value')
        ->first();

        return json_encode([$attr_size, $attr_color]);
    });

    Route::get('/vendor/get/product/variations/second/{idd}/tok/', function ($idd) {

        $attr_vallll = Attributes::where('id', $idd)
        ->select('id', 'value')
        ->first();

        return json_encode($attr_vallll);
    });

    Route::post('/vendor/create/product/variations', function (Request $request) {

        $vari = Variations::where('user_id', $request->user_id)
        ->where('product_id', $request->product_id)
        ->where('var_key', $request->key)
        ->where('var_val', $request->value)
        ->get();



        if(count($vari) === 0) {
            $variation = new Variations;

            $variation->user_id = $request->user_id;
            $variation->product_id = $request->product_id;
            $variation->var_key = $request->key;
            $variation->var_val = $request->value;
            $variation->inventory = $request->inventory;
            $variation->price = $request->price;

            $variation->save();

            return json_encode(['id' => $variation->id]);
        } else {
            return json_encode(['err' => 'Already added.']);
        }

    });

    Route::get('/vendor/get/product/variations/user/defined/{id}/{pid}', function ($id, $pid) {

        $variations = Variations::where('user_id', $id)
        ->where('product_id', $pid)
        ->get();

        return json_encode($variations);
    });

    Route::post('/vendor/create/product/publish', function (Request $request) {

        $uniq = md5(uniqid(rand(), true) . time() . date('YmdHis') . md5($request->product_id) . microtime() . rand());

        Product::where('id', $request->product_id)
        ->update(['publish' => 1, 'p_uniq' => $uniq]);

        return json_encode(['p_uniq_id' => $uniq]);


    });




    // preview discount

    Route::get('/vendor/preview/discount/{id?}/{pid}', function ($id = 0, $pid) {

        $arr = [];
        $vari = [];

        $user = User::where('id', $id)->select('id', 'company')->first();

        $product = DB::table('discount_products')
                    //->where('discount_products.user_id', '=', $id)
                    ->where('discount_products.id', '=', $pid)
                    ->first();


        // start algo----------------------------------

        $price = number_format($product->price, 2);
        $price_discount = number_format($product->price - ($product->price*$product->discount)/100, 2);


        $arrayProduct = array(
            'name' => $product->name,
            'price' => $price,
            'discount_price' => $price_discount,
            'discount' => $product->discount,
            'exclu_discount' => $product->exclusive_disc_good_yellow,
            'brand' => $user->company ?? '',

        );

        // product images

        $productImages = DiscountImages::where('discount_prod_id', $product->id)->get();


        //return response()->json(['product' => $product, 'array_product' => $arrayProduct, 'attributes' => $arr, 'variations' => $variations]);

        return response()->json(['product' => $arrayProduct, 'images' => $productImages, 'attributes' => $arr]);
    });

    Route::get('/vendor/preview/product/variation/{id}/{pid}/{key}/{val}/{inventory}', function ($id, $pid, $key, $val, $inventory) {

        $product = DB::table('products')
                   // ->where('products.user_id', '=', $id)
                    ->where('products.p_uniq', '=', $pid)
                    ->select('id', 'discount_percentage', 'end_discount')
                    ->first();


        if($val != 'val' && $key != 'key') {
            $variation = Variations::where('product_id', $product->id)
                    ->where('var_key', $key)
                    ->where('var_val', $val)
                    ->where('inventory', '>=', $inventory)
                    ->first();
        } elseif($val != 'val' && $key == 'key') {
            if($key == 'key' || $val == 'val') {
                return response()->json(['err' => 'Please choose correct variations.']);
            }
            $variation = Variations::where('product_id', $product->id)
                    ->where('var_val', $val)
                    ->where('inventory', '>=', $inventory)
                    ->first();
        } elseif($val == 'val' && $key != 'key') {

            $variation = Variations::where('product_id', $product->id)
                    ->where('var_key', $key)
                    ->where('inventory', '>=', $inventory)
                    ->first();

            if($variation->var_val != '') {
                if($key == 'key' || $val == 'val') {
                    return response()->json(['err' => 'Please choose correct variations.']);
                }
            }
        }


        if($variation) {
            $arr = [
                'price' => $variation->price,
                'discount_price' => number_format($variation->price - ($variation->price*$product->discount_percentage) / 100, 2),
                'inventory' => $variation->inventory,
            ];
        } else {
            $arr = ['none'];
        }



        return response()->json(['variation' => $arr]);
    });

    Route::get('/vendor/storefront/{id}', function ($id) {

        $prods = [];

        $user = User::where('id', $id)
                ->select('id', 'name', 'email', 'company', 'company_slug', 'private')
                ->first();


        $products = Product::where('user_id', $id)
                    ->get();


        $products_discount = DiscountProduct::where('user_id', $id)
                    ->get();

        $brandInfo = BrandInfo::where('user_id', $id)->where('key', 'brand_description')->select('txt')->first();

        foreach($products as $prod) {
            $image = ProductImages::where('user_id', $id)->where('product_id', $prod->id)->first();
            $prods[] = array(
                'id' => $prod->id,
                'name' => $prod->p_name,
                'price' => $prod->p_price,
                'puniq_id' => $prod->p_uniq,
                'publish' => $prod->publish,
                'image' => $image->url ?? '',
                'discount' => $prod->discount_percentage
            );
        }

        foreach($products_discount as $prod) {
            $image = DiscountImages::where('user_id', $id)->where('discount_prod_id', $prod->id)->first();
            $discountProds[] = array(
                'id' => $prod->id,
                'name' => $prod->name,
                'price' => $prod->price,
                // 'puniq_id' => $prod->p_uniq,
                // 'publish' => $prod->publish,
                'image' => $image->url ?? '',
                'discount' => $prod->discount,
                'discount_code' => $prod->discount_code,
                'how_many_discount_code' => $prod->how_many_discount_code,
                'exclusive_disc_good_yellow' => $prod->exclusive_disc_good_yellow,

            );
        }

        $brandPhotoCover = BrandPhoto::where('user_id', $id)
                        ->where('type', 'cover')
                        ->select('url', 'type')
                        ->first();


        $brandPhotoBrand = BrandPhoto::where('user_id', $id)
                        ->where('type', 'brand')
                        ->select('url', 'type')
                        ->first();

        $maxDiscountProduct = Product::where('user_id', $id)->max('discount_percentage');

        $maxDiscountCode = DiscountProduct::where('user_id', $id)->max('discount');

        $max = [ $maxDiscountProduct, $maxDiscountCode ];

        $maxDiscount = max($max);
       // dd(max($maxDiscountProduct));

       $now = date('Y-m-d H:i:s');

       $subscribersCount = Subscriptions::where('user_id', $user->id)->where('end_date', '>', $now)->get();


        $data = array(
            'brand' => $user->company ?? '',
            'slug' => $user->company_slug ?? '',
            'products' => $prods,
            'discount_products' => $discountProds ?? '',
            'cover_photo' => $brandPhotoCover->url ?? '',
            'brand_photo' => $brandPhotoBrand->url ?? '',
            'brand_description' => $brandInfo->txt ?? '',
            'max_discount' => $maxDiscount,
            'private' => $user->private,
            'total_subscribers' => number_format(count($subscribersCount), 0),
        );

        return response()->json($data);
    });




    Route::get('/vendor/product/delete/{pid}', function ($pid) {
        ProductImages::where('product_id', $pid)->delete();
        Attributes::where('product_id', $pid)->delete();
        Variations::where('product_id', $pid)->delete();
        Product::where('id', $pid)->delete();

        return response()->json('success');
    });

    Route::get('/vendor/product/discount/delete/{pid}', function ($pid) {
        DiscountImages::where('discount_prod_id', $pid)->delete();
        DiscountProduct::where('id', $pid)->delete();

        return response()->json('success');
    });

    Route::post('/vendor/create/leaderboard', function (Request $request) {


        // $ldb = Leaderboard::where('user_id', $request->user_id)
        //     ->where('category', $request->category)
        //     ->get();

        $leaderboard = new Leaderboard;

        $leaderboard->user_id = $request->user_id;
        $leaderboard->category = $request->category;
        $leaderboard->title = $request->title;

        $leaderboard->save();

        return response()->json(['id' => $leaderboard->id]);
    });

    // Route::get('/vendor/leaderboards/{id}', function ($id) {

    //     $leaderboards = LbLevelThree::where('user_id', $id)->get();
    //     //$leaderboards = DB::table('lb_lavel_three')->leftJoin('lb_category', 'lb_lavel_three.category_id', '=', 'lb_category.id')->where('lb_lavel_three.user_id', $id)->get();

    //     return response()->json($leaderboards);
    // });

    Route::get('/vendor/leaderboards/{slug}', function ($slug) {

        $userid = User::where('company_slug', $slug)->select('id')->first();

        //$leaderboards = LbLevelThree::where('user_id', $id)->get();


        $leaderboards = DB::table('lb_lavel_three')->join('lb_category', 'lb_lavel_three.category_id', '=', 'lb_category.id')->where('lb_lavel_three.user_id', $userid->id)->get();

        return response()->json($leaderboards);
    });


    Route::get('/vendor/product/discount/edit/{pid}', function ($pid) {

        $products = DiscountProduct::where('id', $pid)->first();

        return response()->json($products);
    });

    // Edit product

    Route::get('/vendor/product/edit/{pid}', function ($pid) {

        $products = Product::where('id', $pid)->first();

        return response()->json($products);
    });

    Route::post('/vendor/edit/product', function (Request $request) {

        $validator = Validator::make($request->all(), [
            'price' => 'required',
            'name' => 'required',
            'shipping_time' => 'required',
            // 'category' => 'required',
            // 'sub_category' => 'required',
            'discount_percentage' => 'required|numeric|min:5',
            'product_description' => 'required',
            'inventory' => 'required|numeric|min:1',
            'user_id' => 'required',
            'shipping_cost' => 'required|numeric|min:0|max:500',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        } else {
            $product = Product::find($request->id);

            $product->p_price = $request->price;
            $product->p_name = $request->name;
            $product->p_shipping_time = $request->shipping_time;
            $product->category_id = $request->category;
            $product->subcate_id = $request->sub_category;
            $product->p_desc = $request->product_description;
            $product->shipping_interval = $request->shipping_interval;
            $product->type_discount = $request->type_discount;
            $product->inventory = $request->inventory;
            $product->discount_percentage = $request->discount_percentage == 'null' ? 0 : $request->discount_percentage;
            $product->end_discount = $request->discount_end_date;
            $product->important_details = $request->important_details;
            $product->exclus_discount = $request->exclusive_discount;
            $product->exclus_product = $request->exclusive_product;
            $product->user_id = $request->user_id;
            $product->shipping_cost = $request->shipping_cost;

            $product->save();

            return response()->json(['id' => $product->id], 200);
        }

    });

    Route::post('/vendor/edit/product/discount', function (Request $request) {

        $validator = Validator::make($request->all(), [
            'price' => 'required',
            'name' => 'required',

            'product_description' => 'required',

            'user_id' => 'required',
            'disc_code' => 'required',
            'disc_how_many' => 'required',
            'discount_percentage' => 'required'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        } else {
            $product = DiscountProduct::find($request->id);

            $product->price = $request->price;
            $product->name = $request->name;

            $product->description = $request->product_description;
            $product->discount = $request->discount_percentage == 'null' ? 0 : $request->discount_percentage;

            $product->exclusive_disc_good_yellow = $request->exclusive_discount;
            $product->user_id = $request->user_id;


            $product->discount_code = $request->disc_code;
            $product->how_many_discount_code = $request->disc_how_many;

            $product->save();

            return response()->json(['id' => $product->id], 200);
        }

    });

    Route::get('/vendor/edit/product/photo/delete/{id}', function ($id) {
        $pimages = ProductImages::where('id', $id)->select('url')->first();
        $path = public_path();
        $url = $path . '/uploads/' . $pimages->url;

        ProductImages::where('id',$id)->delete();
        unlink($url);
        return json_encode('deleted');
    });

    Route::get('/vendor/edit/product/attributes/{id}', function ($id) {
        $attr = Attributes::where('product_id', $id)->get();

        return response()->json($attr);
    });

    Route::get('/vendor/edit/product/attributes/delete/{id}/{pid}', function ($id, $pid) {
        Attributes::where('id', $id)->delete();
        Variations::where('product_id', $pid)->delete();

        return response()->json('deleted');
    });

    Route::get('/vendor/edit/product/varriations/delete/{id}', function ($id) {
        Variations::where('id', $id)->delete();

        return response()->json('deleted');
    });

     Route::get('/vendor/leaderboards/{slug}', function ($slug) {

        $userid = User::where('company_slug', $slug)->select('id')->first();

        //$leaderboards = LbLevelThree::where('user_id', $id)->get();


        $leaderboards = DB::table('lb_lavel_three')->join('lb_category', 'lb_lavel_three.category_id', '=', 'lb_category.id')->where('lb_lavel_three.user_id', $userid->id)->get();

        return response()->json($leaderboards);
    });

    Route::get('/vendor/storefront/brand/photos/{id}', function ($id) {

        $brandPhotos = BrandPhoto::where('user_id', $id)->get();

        return response()->json($brandPhotos);
    });

    Route::post('/vendor/stripe/credentials', function (Request $request) {

        $a = $request->api_key;
        $search = 'pk_live_';

        $b = $request->secret_key;
        $search_sec = 'sk_live_';

        if(str_contains($a, $search) && str_contains($b, $search_sec)) {

            $vendorstripe = VendorStripe::updateOrCreate
            (
                [
                    'user_id' => $request->user_id,
                ],
                [
                    'api_key' => $request->api_key,
                    'secret_key' => $request->secret_key,
                    'user_id' => $request->user_id
                ],
            );

            $data = [
                'id' => $vendorstripe->id,
            ];

            return response()->json($data);

        } else {
            $data = [
                'msg' => "Your Stripe API Key and Secret Key are not valid.",
            ];
            return response()->json($data);
        }


    });















}); // end of signed in vendor user functions










// Signup process step 1

Route::post('/vendor/signup', function (Request $request) {

        //check email exist or not

        $userEmaill = User::where('email', $request->email)->select('email')->get();
        $data = [];

        if(count($userEmaill) > 0) {
            $data = [
                'msg' => 'Email already exist! Please try with another email.'
            ];

            return json_encode([$data]);
        } else {
            $user = new User;

            $token = md5(time().$request->email.$request->password.time().uniqid());
            $verifyTokenGenerate = md5(uniqid().time().date('dmY').$request->email.$token);
            $slug = Str::slug($request->company, '-');

            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = md5($request->password);
            $user->remember_token = $token;
            $user->company = $request->company;
            $user->verify_token = $verifyTokenGenerate;
            $user->company_slug = $slug;

            $user->save();

            // send verification email to registered user.
            $emailData = [
                'name' => $request->company,
                'url' => env('FRONTEND_URL') . 'vendor/email/verification/' . $verifyTokenGenerate,
                'subject' => 'Verification Email From Good Yellow.'
            ];
            Mail::to($request->email)->send(new Verification($emailData));


            $data = [
                'id' => $user->id,
                'email' => $request->email,
                'token' => $token,
                'company' => $request->company,
                'login_time' => date('Y-m-d H:i:s'),
            ];

            return json_encode([$data]);
        }




    });

    // login process

    Route::post('/vendor/signin', function (Request $request) {
        $user = User::where('email', $request->email)
        ->where('password', md5($request->password))
        ->get();

        if(count($user) === 1) {
            return json_encode(['data' => $user]);
        } else {
            return json_encode('error');
        }
    });

    Route::get('/vendor/public/leaderboards/{cate}', function ($cate) {

        $leaderboards = Leaderboard::where('category', $cate)->groupBy('title')->get();

        return response()->json($leaderboards);
    });

    Route::get('/email/verification/{token}', function ($token) {

        try{
            $ver = User::where('verify_token', $token)->update(['email_verified_at' => date('Y-m-d H:i:s')]);

            return response()->json('Your Email Has Been Successfully Verified.');


        } catch(\Throwable $th) {
            return response()->json('Token not matched. Please try again with a valid token.');
        }

    });


    Route::post('/vendor/email/forgot/password/generator', function (Request $request) {
        $user = User::where('email', $request->email)->select('id', 'email', 'name')->get();
        if(count($user) > 0) {

            $token = md5(uniqid().time().$request->email);

            ResetPassword::where('email', $request->email)->delete();

            $reset = new ResetPassword;

            $reset->email = $request->email;
            $reset->token = $token;

            $reset->save();

            $emailData = [
                'name' => $user[0]->name,
                'url' => env('FRONTEND_URL') . 'vendor/forgot/password/done/' . $token,
            ];
            Mail::to($request->email)->send(new Forgotpassword($emailData));

            $emailData = [
                'msg' => 'Email sent. Please check your SPAM folder also.'
            ];
            return response()->json($emailData);

        } else {
            $emailData = [
                'msg' => 'Email does not matched.'
            ];
            return response()->json($emailData);
        }

    });


    Route::post('/vendor/email/forgot/password/save', function (Request $request) {
        $chkPass = ResetPassword::where('token', $request->token)->select('token', 'email')->get();

        if(count($chkPass) > 0) {
            User::where('email', $chkPass[0]->email)->update(['password' => md5($request->password)]);
            ResetPassword::where('token', $request->token)->delete();
            return response()->json(['succ' => 'Your password has been changed successfully.']);
        } else {
            return response()->json(['msg' => 'Token not matched. Please try again later.']);
        }

    });


    Route::post('/support/send', function (Request $request) {

        $emailData = [
            'business_name' => $request->business_name,
            'email' => $request->email,
            'issue' => $request->issue,
            'attachOne' => $request->attachOne,
            'attachTwo' => $request->attachTwo,
            'attachThree' => $request->attachThree,
            'attachFour' => $request->attachFour,
            'attachFive' => $request->attachFive,
        ];

        Mail::to("heyhey@goodyellowco.com")->send(new Support($emailData));

        return response()->json(['msg' => 'Your issues has been submitted. We will rectify it within 12 hours.']);
    });


    Route::get('/leaderboard/brands/list/{slug}', function ($slug) {

        $arr = [];

        $data = DB::table('lb_lavel_three')->join('users', 'lb_lavel_three.user_id', '=', 'users.id')->where('lb_lavel_three.slug', '=', $slug)->select('users.id as uid', 'users.company', 'lb_lavel_three.lb_three_name as title', 'users.company_slug', 'users.dummy_customers', 'users.dummy_sales', 'users.dummy_discount', 'users.dummy', 'users.private')->groupBy('lb_lavel_three.slug')->get();

        foreach($data as $d) {

            $brandPhotos = BrandPhoto::where('user_id', $d->uid)->where('type', 'photos_of_brand')->take(4)->get();

            $brandphoto_brand = BrandPhoto::where('user_id', $d->uid)->where('type', 'brand')->take(1)->get();
            $brandphoto_main = BrandPhoto::where('user_id', $d->uid)->where('type', 'main')->take(1)->get();

            $maxDiscount = Product::where('user_id', $d->uid)->max('discount_percentage');



            if(count($brandPhotos) > 0) {
                $brandPhotos = $brandPhotos;
            } elseif(count($brandphoto_brand) > 0) {
                $brandPhotos = $brandphoto_brand;
            } elseif(count($brandphoto_main) > 0) {
                $brandPhotos = $brandphoto_main;
            }

            $arr[] = [
                'user_id' => $d->uid,
                'company' => $d->company,
                'slug' => $d->company_slug,
                'leaderboard' => $d->title,
                'sales' => '0',
                'customers' => '0',
                'brand_photos' => $brandPhotos,
                'discount' => number_format($maxDiscount, 2),
                'dummy_customers' => $d->dummy_customers,
                'dummy_sales' => $d->dummy_sales,
                'dummy_discount' => $d->dummy_discount,
                'dummy' => $d->dummy,
                'private' => $d->private,
            ];
        }

        return response()->json($arr);
    });


    Route::get('/vendor/storefront/preview/{slug}/{bid?}', function ($slug, $bid = 0) {

        $prods = [];

        $user = User::where('company_slug', $slug)
                ->select('id', 'name', 'email', 'company', 'company_slug')
                ->first();

        $id = $user->id;

        $products = Product::where('user_id', $id)->where('publish', 1)
                    ->get();

        $products_discount = DiscountProduct::where('user_id', $id)
                    ->get();

        $brandInfo = BrandInfo::where('user_id', $id)->where('key', 'brand_description')->select('txt')->first();

        foreach($products as $prod) {
            $image = ProductImages::where('user_id', $id)->where('product_id', $prod->id)->first();
            $prods[] = array(
                'id' => $prod->id,
                'name' => $prod->p_name,
                'price' => $prod->p_price,
                'puniq_id' => $prod->p_uniq,
                'publish' => $prod->publish,
                'image' => $image->url ?? '',
                'discount' => $prod->discount_percentage
            );
        }

        foreach($products_discount as $prod) {
            $image = DiscountImages::where('user_id', $id)->where('discount_prod_id', $prod->id)->first();
            $discountProds[] = array(
                'id' => $prod->id,
                'name' => $prod->name,
                'price' => $prod->price,
                // 'puniq_id' => $prod->p_uniq,
                // 'publish' => $prod->publish,
                'image' => $image->url ?? '',
                'discount' => $prod->discount,
                'discount_code' => $prod->discount_code,
                'how_many_discount_code' => $prod->how_many_discount_code,
                'exclusive_disc_good_yellow' => $prod->exclusive_disc_good_yellow,

            );
        }

        $brandPhotoCover = BrandPhoto::where('user_id', $id)
                        ->where('type', 'cover')
                        ->select('url', 'type')
                        ->first();


        $brandPhotoBrand = BrandPhoto::where('user_id', $id)
                        ->where('type', 'brand')
                        ->select('url', 'type')
                        ->first();

        $maxDiscountProduct = Product::where('user_id', $id)->max('discount_percentage');

        $maxDiscountCode = DiscountProduct::where('user_id', $id)->max('discount');

        $max = [ $maxDiscountProduct, $maxDiscountCode ];

        $maxDiscount = max($max);


        $now = date('Y-m-d H:i:s');

        $subscriptionChk = Subscriptions::where('buyer_id', $bid)->where('user_id', $user->id)->where('end_date', '>', $now)->get();

        $subscribersCount = Subscriptions::where('user_id', $user->id)->where('end_date', '>', $now)->get();

        $data = array(
            'brand' => $user->company ?? '',
            'slug' => $user->company_slug ?? '',
            'products' => $prods,
            'discount_products' => $discountProds ?? '',
            'cover_photo' => $brandPhotoCover->url ?? '',
            'brand_photo' => $brandPhotoBrand->url ?? '',
            'brand_description' => $brandInfo->txt ?? '',
            'max_discount' => number_format($maxDiscount, 2),
            'subscribed' => count($subscriptionChk) > 0 ? 'Yes' : 'No',
            'total_subscribers' => number_format(count($subscribersCount), 0),
        );

        return response()->json($data);
    });


    //level one
    Route::get('/level/one/{id}', function ($id) {
        $data = LbLevelOne::where('lb_category_id', $id)->get();

        return response()->json($data);
    });

    //level two
    Route::get('/level/two/{id}', function ($id) {
        $data = LbLevelTwo::where('level_one_id', $id)->get();

        return response()->json($data);
    });

    //level three
    Route::get('/level/three/{id}/{cate?}', function ($id, $cate = 0) {
        $data = LbLevelThree::where('lavel_two_id', $id)->groupBy('lb_three_name')->get();

        return response()->json($data);
    });

    //level three > Leaderboard List
    Route::get('/level/four/leaderboard/list/{id}', function ($id) {
        $data = DB::table('leaderboard_list')->join('leaderboards', 'leaderboard_list.leaderboard_slug', '=', 'leaderboards.slug')->where('leaderboard_list.level_three_id', $id)->groupBy('leaderboards.title')->get();

        return response()->json($data);
    });

    //add leaderboard
    Route::post('/level/leaderboard/post', function (Request $request) {

        if($request->status === 'three') {

            $slug = Str::slug($request->lb_name, '-');

            $levelThree = new LbLevelThree;

            $levelThree->lavel_two_id = $request->category_id;
            $levelThree->lb_three_name = $request->lb_name;
            $levelThree->user_id = $request->user_id;
            $levelThree->category_id = $request->category_id_main;
            $levelThree->slug = $slug;

            $levelThree->save();

            return response()->json(['id' => $levelThree->id]);
        } elseif($request->status === 'two') {
            $levelTwo = new LbLevelTwo;

            $levelTwo->level_one_id = $request->category_id;
            $levelTwo->lb_two_name = $request->lb_name;

            $levelTwo->save();

            return response()->json(['id' => $levelTwo->id]);
        } else {
            $lb = Leaderboard::where('slug', $request->slug)->get();

            if(count($lb) > 0) {
                $leaderboard = new Leaderboard;

                $leaderboard->user_id = $request->user_id;
                $leaderboard->category = $request->category;
                $leaderboard->title = $lb[0]->title;
                $leaderboard->slug = $lb[0]->slug;

                $leaderboard->save();
            } else {
                $slug = Str::slug($request->slug, '-');

                $leaderboard = new Leaderboard;

                $leaderboard->user_id = $request->user_id;
                $leaderboard->category = $request->category;
                $leaderboard->title = $request->slug;
                $leaderboard->slug = $slug;

                $leaderboard->save();

                $leaderboard_list = new LeaderboardList;

                $leaderboard_list->level_three_id = $request->three_id;
                $leaderboard_list->leaderboard_slug = $slug;

                $leaderboard_list->save();
            }

            return response()->json(['id' => '1']);
        }


    });


    Route::get('/storefront/private/{uid}', function ($uid) {
        User::where('id', $uid)->update(['private' => 1]);
        return response()->json(['msg' => 'Now your storefront is private.']);
    });

    Route::get('/storefront/public/{uid}', function ($uid) {
        User::where('id', $uid)->update(['private' => 0]);
        return response()->json(['msg' => 'Now your storefront is public.']);
    });



    // all brands

    Route::get('/all/brands', function () {

        $arr = [];
        $brandPhotos = [];

        $data = DB::select('SELECT brands.Id AS id, brands.name AS name, brands.company AS company, brands.company_slug AS company_slug, brand_subs.user_Id AS subs_user_Id, brand_subs.buyers_no  AS buyers_no
                                FROM (SELECT *
                                        FROM users
                                        WHERE dummy = 0 AND private = 0) AS brands
                                LEFT JOIN (SELECT user_Id AS user_Id , COUNT(buyer_Id) as buyers_no
                                            FROM subscriptions
                                            WHERE DATE(end_date) >= CURRENT_DATE
                                            GROUP BY user_Id) AS brand_subs
                                ON brands.Id = brand_subs.user_id
                                GROUP BY brands.name, brands.Id ,brands.company , brands.company_slug,brand_subs.user_Id, brand_subs.buyers_no
                                ORDER BY buyers_no DESC');

        foreach($data as $d) {

            $brandPhotos = BrandPhoto::where('user_id', $d->id)->where('type', 'photos_of_brand')->take(1)->get();

            $brandphoto_brand = BrandPhoto::where('user_id', $d->id)->where('type', 'brand')->take(1)->get();
            $brandphoto_main = BrandPhoto::where('user_id', $d->id)->where('type', 'main')->take(1)->get();

            //$maxDiscount = Product::where('user_id', $d->uid)->max('discount_percentage');



            if(count($brandPhotos) > 0) {
                $brandPhotos = $brandPhotos;
            } elseif(count($brandphoto_brand) > 0) {
                $brandPhotos = $brandphoto_brand;
            } elseif(count($brandphoto_main) > 0) {
                $brandPhotos = $brandphoto_main;
            }

            $arr[] = [
                'user_id' => $d->id,
                'company' => $d->company,
                'slug' => $d->company_slug,
                'brand_photos' => count($brandPhotos) > 0 ? $brandPhotos : ['null'],
            ];
        }

        return response()->json($arr);
    });




    Route::get('/vendor/preview/product/{pid}/{bid?}', function ($pid, $bid = 0) {

        $arr = [];
        $vari = [];

        $product_user_id = DB::table('products')
                    ->where('products.p_uniq', '=', $pid)
                    ->first();

        $user = User::where('id', $product_user_id->user_id)->select('id', 'company', 'company_slug')->first();

        $product = DB::table('products')
                    ->where('products.user_id', '=', $product_user_id->user_id)
                    ->where('products.p_uniq', '=', $pid)
                    ->where('products.discount_percentage', '>', '4')
                    ->first();


        $attributes = Attributes::where('user_id', $product_user_id->user_id)
                    ->where('product_id', $product->id)
                    ->get();

        foreach($attributes as $att) {
            $exp = explode(',', $att->value);
            $arr[] = array(
                'key' => $att->key,
                'value' => $exp
            );
        }

        $variations = Variations::where('user_id', $product_user_id->user_id)
                    ->where('product_id', $product->id)
                    ->select('price', 'inventory')
                    ->get();

        foreach($variations as $var) {
            $vari[] = $var->price;
        }

        // start algo----------------------------------

        if(count($variations) > 0) {
            $price_min = min($vari);
            $price_max = max($vari);
            $price = number_format($price_min, 2) . ' - ' . number_format($price_max, 2);
            $price_discount_min = $price_min - ($price_min*$product->discount_percentage)/100;
            $price_discount_max = $price_max - ($price_max*$product->discount_percentage)/100;
            $price_discount = number_format($price_discount_min, 2) . ' - ' . number_format($price_discount_max, 2);

            //$shippingCost = $price_discount + $product->shipping_cost;
        } else {
            $price = number_format($product->p_price, 2);
            $price_discount = number_format($product->p_price - ($product->p_price*$product->discount_percentage)/100, 2);

            //$shippingCost = $price_discount + $product->shipping_cost;
        }

        //dd($price_discount);

        //$shippingCost =  ($product->shipping_cost > 0 && $product->shipping_cost < 1000 ? $product->shipping_cost : 0);

        $now = date('Y-m-d H:i:s');

        $subscriptionChk = Subscriptions::where('buyer_id', $bid)->where('user_id', $product_user_id->user_id)->where('end_date', '>', $now)->get();


        $shipping = ShippingCost::where('product_id', $product->id)->get();

        $arrayProduct = array(
            'name' => $product->p_name,
            'price' => $price,
            'desc' => $product->p_desc,
            //'discount_price' => $price_discount + $product->shipping_cost,
            'discount_price' => $price_discount,
            'discount' => $product->discount_percentage,
            'exclu_discount' => $product->exclus_discount,
            'exclus_product' => $product->exclus_product,
            'important_details' => $product->important_details,
            'brand' => $user->company ?? '',
            'slug' => $user->company_slug,
            'shipping_cost' => $shipping,
            'subscribed' => count($subscriptionChk) > 0 ? 'Yes' : 'No',


        );

        // product images

        $productImages = ProductImages::where('user_id', $product_user_id->user_id
        )->where('product_id', $product->id)->get();


        //return response()->json(['product' => $product, 'array_product' => $arrayProduct, 'attributes' => $arr, 'variations' => $variations]);

        return response()->json(['product' => $arrayProduct, 'images' => $productImages, 'attributes' => $arr]);
    });



    Route::get('/discount/list', function () {

        $data = DiscountList::all();

        return response()->json($data);

    });

    Route::get('/discount/list/details/{id}', function ($id) {

        $discountListId = DiscountList::where('discount_uniq', $id)->select('id', 'title')->first();

        $data = DB::table('lb_level_one')->join('discount_list_levels', 'lb_level_one.id', '=', 'discount_list_levels.level_one_id')->where('discount_list_levels.discount_list_id', $discountListId->id)->select('discount_list_levels.level_one_id as id', 'lb_level_one.lb_name')->get();

        return response()->json([$data, $discountListId->title]);

    });

    Route::get('/discount/list/details/two/{id}/{oneid}', function ($id, $oneid) {

        $discountListId = DiscountList::where('discount_uniq', $id)->select('id')->first();

        $data = DB::table('lb_level_two')->join('discount_list_levels', 'lb_level_two.id', '=', 'discount_list_levels.level_two_id')->where('discount_list_levels.discount_list_id', $discountListId->id)->where('discount_list_levels.level_one_id', $oneid)->select('discount_list_levels.level_two_id as id', 'lb_level_two.lb_two_name')->get();

        return response()->json($data);

    });

    Route::get('/discount/list/details/three/{id}/{twoid}', function ($id, $twoid) {

        $discountListId = DiscountList::where('discount_uniq', $id)->select('id')->first();

        $data = DB::table('lb_lavel_three')->join('discount_list_levels', 'lb_lavel_three.id', '=', 'discount_list_levels.level_three_id')->where('discount_list_levels.discount_list_id', $discountListId->id)->where('discount_list_levels.level_two_id', $twoid)->select('discount_list_levels.level_three_id as id', 'lb_lavel_three.lb_three_name', 'lb_lavel_three.slug')->get();

        return response()->json($data);

    });

    Route::get('/current/subscriptions/{uid}', function ($uid) {

        $arr = [];

        $now = date('Y-m-d H:i:s');

        $data = DB::table('subscriptions')->join('users', 'subscriptions.user_id', '=', 'users.id')->where('subscriptions.buyer_id', $uid)->where('subscriptions.end_date', '>', $now)->get();

        foreach($data as $d) {

            $today      = new DateTime('now');
            $tomorrow   = new DateTime($d->end_date);
            $difference = $today->diff($tomorrow);

            $arr[] = [
                'brand' => $d->company,
                'slug' => $d->company_slug,
                'left' => $difference->format('%d days'),
            ];

        }

        return response()->json($arr);

    });

    Route::get('/past/subscriptions/{uid}', function ($uid) {

        $arr = [];

        $now = date('Y-m-d H:i:s');

        $data = DB::table('subscriptions')->join('users', 'subscriptions.user_id', '=', 'users.id')->where('subscriptions.buyer_id', $uid)->where('subscriptions.end_date', '<', $now)->get();

        foreach($data as $d) {

            $arr[] = [
                'brand' => $d->company,
                'slug' => $d->company_slug,
            ];

        }

        return response()->json($arr);

    });

    Route::get('/credits/left/{uid}', function ($uid) {

        $now = date('Y-m-d H:i:s');

        $data = Credits::where('buyer_id', $uid)->where('end_date', '>', $now)->select('credits')->get();

        return response()->json($data);

    });



    // Extras -------------------------------------

    Route::get('/extra/generate/slug', function () {

        $userCompany = LbLevelThree::all();
        foreach($userCompany as $comp) {
            $slug = Str::slug($comp->lb_three_name, '-');
            LbLevelThree::where('id', $comp->id)->update(['slug' => $slug]);
        }

    });

    Route::post('/buyer/signin', function (Request $request) {
        $user = BuyerUser::where('email', $request->email)
        ->where('password', md5($request->password))
        ->get();

        if(count($user) === 1) {
            return json_encode(['data' => $user]);
        } else {
            return json_encode('error');
        }
    });

    Route::post('/buyer/signup', function (Request $request) {

        try{

            $validator = Validator::make($request->all(), [
                'fname' => 'required',
                'lname' => 'required',
                'email' => 'required|email|unique:buyer_users,email',
                'password' => 'required|min:8|max:16',
                'street' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required|numeric',
                'phone' => 'required',

            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 200);
            } else {
                $start_date = date('Y-m-d H:i:s');
                $end_date = date('Y-m-d H:i:s', strtotime("+30 days"));
                $token = md5(uniqid(). time(). $request->email);

                $buyer = new BuyerUser;

                $buyer->fname = $request->fname;
                $buyer->lname = $request->lname;
                $buyer->password = md5($request->password);
                $buyer->email = $request->email;
                $buyer->street_addr = $request->street;
                $buyer->city = $request->city;
                $buyer->state = $request->state;
                $buyer->zip = $request->zip;
                $buyer->phone = $request->phone;
                $buyer->token = $token;
                $buyer->buyer_promo = $request->promo;

                $buyer->save();

                // add 30 credits and starting membership

                $credit = new Credits;

                $credit->buyer_id = $buyer->id;
                $credit->credits = 30;
                $credit->payment_buyer_id = 0; // 0 = trial
                $credit->start_date = $start_date;
                $credit->end_date = $end_date;

                $credit->save();

                return response()->json(['succ' => 'success', 'token' => $token, 'email' => $request->email, 'id' => $buyer->id]);
            }



        } catch(\Throwable $th) {
            return response()->json(['err' => $th->getMessage()]);
        }

    });


    Route::get('/buyer/subscribe/brand/{pid}/{bid}', function ($pid, $bid) {

        $start_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime("+30 days"));

        $credits = Credits::where('buyer_id', $bid)->where('end_date', '>', $start_date)->select('credits')->get();

        if(count($credits) > 0) {
            if($credits[0]->credits > 0) {
                $productUserId = Product::where('p_uniq', $pid)->select('user_id')->first();

                Subscriptions::updateOrCreate
                (
                    [
                        'buyer_id' => $bid,
                        'user_id' => $productUserId->user_id,
                    ],
                    [
                        'user_id' => $productUserId->user_id,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'buyer_id' => $bid,
                    ],
                );

                // minus credit 1
                $final_credit = Credits::where('buyer_id', $bid)->select('credits')->first();
                $final_credit = $final_credit->credits - 1;

                Credits::where('buyer_id', $bid)->update(['credits' => $final_credit]);


                return response()->json(['msg' => 'success']);
            } else {
                return response()->json(['msg' => 'Your have no credit left. Please wait till next billing cycle.']);
            }
        } else {
            return response()->json(['msg' => 'Your membership has been expired. Please re-new your membership.']);
        }



    });

    Route::get('/buyer/subscribe/brand/storefront/{pid}/{bid}', function ($pid, $bid) {

        $start_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime("+30 days"));

        $credits = Credits::where('buyer_id', $bid)->where('end_date', '>', $start_date)->select('credits')->get();

        if(count($credits) > 0) {
            if($credits[0]->credits > 0) {
                $user_id = User::where('company_slug', $pid)->select('id')->first();

                Subscriptions::updateOrCreate
                (
                    [
                        'buyer_id' => $bid,
                        'user_id' => $user_id->id,
                    ],
                    [
                        'user_id' => $user_id->id,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'buyer_id' => $bid,
                    ],
                );

                // minus credit 1
                $final_credit = Credits::where('buyer_id', $bid)->select('credits')->first();
                $final_credit = $final_credit->credits - 1;

                Credits::where('buyer_id', $bid)->update(['credits' => $final_credit]);


                return response()->json(['msg' => 'success']);
            } else {
                return response()->json(['msg' => 'Your have no credit left. Please wait till next billing cycle.']);
            }
        } else {
            return response()->json(['msg' => 'Your membership has been expired. Please re-new your membership.']);
        }



    });

    Route::get('/vendor/create/product/shipping/cost/get/data/{uid}/{pid}', function ($uid, $pid) {
        $costs = ShippingCost::where('user_id', $uid)->where('product_id', $pid)->get();

        return response()->json($costs);
    });

    Route::get('/vendor/create/product/shipping/cost/delete/{id}', function ($id) {

        ShippingCost::where('id', $id)->delete();

        return response()->json('success');
    });

















    // ------------------------------------- new Romanian developer --------------------------------------

    Route::get('/claudia/level/one/subscribers/brands/{id}', function ($id) {

        $arr = [];

        $maxDiscountProduct = DB::table('products')->select('user_id as user_id', DB::raw('MAX(discount_percentage) as max_discount_percentage'))->groupBy('user_id');

        $maxDiscountCode = DB::table('discount_products')->select('user_id as user_id', DB::raw('MAX(discount) as max_discount'))->groupBy('user_id');

        $brands = DB::table('users')->where('dummy', 0)->where('private',0);

        $brand_subs = DB::table('subscriptions')->select('user_Id as user_Id', DB::raw('COUNT(buyer_Id) as buyers_no'))->whereDate('end_date', '>=',date('Y-m-d'))->groupBy('user_Id');

        $data= DB::table('lb_level_one', 'one')
                  ->select('one.Id as id', 'one.lb_category_id as lb_category_id', 'one.lb_name as lb_name','one.lb_order_no as lb_order_no', 'one.created_at as created_at','one.updated_at as updated_at', 'one.discount as discount',DB::raw('MAX(brand_subs.buyers_no) as buyers_no'), DB::raw('MAX(max_discount_product.max_discount_percentage) as max_discount_percentage'),  DB::raw('MAX(max_discount_code.max_discount ) as max_discount'))
                  ->leftjoin('lb_level_two as two', 'one.Id','=','two.level_one_id')
                  ->leftjoin('lb_lavel_three as three', 'two.Id','=','three.lavel_two_Id')
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
                  ->where('one.lb_category_id', $id)
                  ->groupBy('one.Id')
                  ->orderBy('buyers_no','desc')
                  ->get();

        foreach($data as $d) {

            $arr[] = [
                'Id' => $d->id,
                'lb_category_id' => $d->lb_category_id,
                'lb_name' => $d->lb_name,
                'lb_order_no' => $d->lb_order_no,
                'created_at' => $d->created_at,
                'updated_at' => $d->updated_at,
                //'discount' => $d->discount,
                'discount' => max([$d->max_discount_percentage, $d->max_discount ]),
                'buyers_no' => $d->buyers_no
            ];
        }

        return response()->json($arr);

    });

    //level two brands ordered by subscribers number
Route::get('/claudia/level/two/subscribers/brands/{id}', function ($id) {
        $arr = [];

        $maxDiscountProduct = DB::table('products')->select('user_id as user_id', DB::raw('MAX(discount_percentage) as max_discount_percentage'))->groupBy('user_id');

        $maxDiscountCode = DB::table('discount_products')->select('user_id as user_id', DB::raw('MAX(discount) as max_discount'))->groupBy('user_id');

        $brands = DB::table('users')->where('dummy', 0)->where('private',0);

        $brand_subs = DB::table('subscriptions')->select('user_Id as user_Id', DB::raw('COUNT(buyer_Id) as buyers_no'))->whereDate('end_date', '>=',date('Y-m-d'))->groupBy('user_Id');

        $data= DB::table('lb_level_two', 'two')
                  ->select('two.Id as id', 'two.level_one_Id as level_one_Id', 'two.lb_two_name as lb_two_name','two.lb_two_order_no as lb_two_order_no','two.created_at as created_at', 'two.updated_at as updated_at', 'two.discount as discount', DB::raw('MAX(brand_subs.buyers_no) as buyers_no'), DB::raw('MAX(max_discount_product.max_discount_percentage) as max_discount_percentage'),  DB::raw('MAX(max_discount_code.max_discount ) as max_discount'))
                  ->leftjoin('lb_lavel_three as three', 'three.lavel_two_Id','=','two.Id')
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

        return response()->json($arr);

    });
//level three brands ordered by subscribers number
Route::get('/claudia/level/three/subscribers/brands/{id}', function ($id) {
        $arr = [];
        $maxDiscountProduct = DB::table('products')->select('user_id as user_id', DB::raw('MAX(discount_percentage) as max_discount_percentage'))->groupBy('user_id');

        $maxDiscountCode = DB::table('discount_products')->select('user_id as user_id', DB::raw('MAX(discount) as max_discount'))->groupBy('user_id');
        $brands = DB::table('users')->where('dummy', 0)->where('private',0);

        $brand_subs = DB::table('subscriptions')->select('user_Id as user_Id', DB::raw('COUNT(buyer_Id) as buyers_no'))->whereDate('end_date', '>=',date('Y-m-d'))->groupBy('user_Id');

         $data= DB::table('lb_lavel_three','three')
                  ->select('three.Id as id', 'three.lavel_two_Id as lavel_two_Id', 'three.lb_three_name as lb_three_name','three.lb_three_order_no as lb_three_order_no','three.created_at as created_at', 'three.updated_at as updated_at', 'three.user_id as user_id', 'three.category_id as category_id','three.slug as slug',  DB::raw('MAX(brand_subs.buyers_no) as buyers_no'), DB::raw('MAX(max_discount_product.max_discount_percentage) as max_discount_percentage'),  DB::raw('MAX(max_discount_code.max_discount ) as max_discount'))
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

        return response()->json($arr);

    });
















    //level one  that sells products with min-max% discounts
Route::get('/test/level/one/discount/{id}/{min}/{max}', function ($id, $min, $max) {
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

                                  ->where('one.lb_category_id', $id);



       $data = DB::table($brand_list_products,'brand_list_products')
                  ->select('id', 'lb_category_id', 'lb_name','lb_order_no','created_at','updated_at','discount', DB::raw('AVG(total_discount) as ave_discount'))
                  ->where('total_discount','>=',$min)
                  ->where('total_discount','<=',$max)
                  ->groupBy('id')
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
                'ave_discount' =>  $d->ave_discount,

            ];
        }

        return response()->json($arr);

    });