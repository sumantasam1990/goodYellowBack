<?php

namespace App\Http\Controllers;

use App\Models\BrandPhoto;
use App\Models\DummyBrands as ModelsDummyBrands;
use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DummyBrands extends Controller
{
    public $data = [];

    public function index()
    {
        $dummy = User::where('dummy', '1')->get();

        foreach($dummy as $d) {
            $photo = BrandPhoto::where('user_id', $d->id)->where('type', 'brand')->first();

            $this->data[] = [
                'company' => $d->company,
                'dummy_customers' => $d->dummy_customers,
                'dummy_sales' => $d->dummy_sales,
                'dummy_discount' => $d->dummy_discount,
                'dummy' => $d->dummy,
                'url' => $photo->url,
                'id' => $d->id,
            ];
        }

        //return $this->data;

        return view('admin.dummy.index', ['title' => 'Dummy Brands', 'users' => $this->data]);
    }

    public function index_post(Request $request)
    {
        $request->validate([
            'brand_name' => 'required',
            'customers' => 'required',
            'sales'  => 'required',
            'discount'     => 'required',
            'image' => 'image|mimes:jpeg,jpg,webp|max:1024'
        ]);

        $dummybrand = new User;

        $dummybrand->name = uniqid().time();
        $dummybrand->email = uniqid().'@dummy.com';
        $dummybrand->password = md5(uniqid().time());
        $dummybrand->remember_token = md5(time().uniqid().time());
        $dummybrand->company_slug = Str::slug($request->brand_name . uniqid(), '-');
        $dummybrand->company = $request->brand_name;
        $dummybrand->private = 1;


        $dummybrand->dummy_customers = $request->customers;
        $dummybrand->dummy_sales = $request->sales;
        $dummybrand->dummy_discount = $request->discount;
        $dummybrand->dummy = '1';

        $dummybrand->save();

        if (!empty($request->image)) {

            $imageName = 'dummy_brand_' . uniqid() . time() . '.' . $request->image->extension();

            $request->image->move(public_path('uploads'), $imageName);

            $brandphoto = new BrandPhoto;

            $brandphoto->url = $imageName;
            $brandphoto->user_id = $dummybrand->id;
            $brandphoto->type = 'brand';

            $brandphoto->save();

        }

        return redirect(route('dummy.brands'));
    }

    public function brand_leaderboard(int $id)
    {
        $leaderboards = Leaderboard::groupBy('title')->get();

        $dummylb = Leaderboard::where('user_id', $id)->get();

        return view('admin.dummy.brand_leaderboard', ['title' => 'Brand Leaderboard', 'leaderboards' => $leaderboards, 'user_id' => $id, 'dummylb' => $dummylb]);
    }

    public function brand_leaderboard_post(Request $request)
    {
        $lb = Leaderboard::where('slug', $request->lb_exist)->get();

        if(count($lb) > 0) {
            $lbNew = new Leaderboard;

            $lbNew->user_id = $request->user_id;
            $lbNew->category = $lb[0]->category;
            $lbNew->title = $lb[0]->title;
            $lbNew->slug = $lb[0]->slug;
            $lbNew->dummy_lb = $lb[0]->dummy_lb;

            $lbNew->save();
        } else {
            $lbNew = new Leaderboard;

            $lbNew->user_id = $request->user_id;
            $lbNew->category = $request->category;
            $lbNew->title = $request->lb_new;
            $lbNew->slug = Str::slug($request->lb_new, '-');
            $lbNew->dummy_lb = '1';

            $lbNew->save();
        }

        return redirect(route('dummy.brands.leaderboard', [$request->user_id]));
    }
}
