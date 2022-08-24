<?php

namespace App\Http\Controllers;

use App\Models\LbCategory;
use App\Models\LbLevelOne;
use App\Models\LbLevelTwo;
use App\Models\DiscountList;
use App\Models\LbLevelThree;
use Illuminate\Http\Request;
use App\Models\DiscountListLevels;
use Illuminate\Support\Facades\Redirect;

class DiscountListController extends Controller
{
    public function index()
    {
        $data = DiscountList::all();

        return view('admin.discount_list.index', ['title' => 'Discount Leaderboards', 'data' => $data]);
    }

    public function index_post(Request $request)
    {
        if($request->title) {
            $discountSave = new DiscountList;

            $discountSave->title = $request->title;
            $discountSave->discount_uniq = md5(uniqid().time().$request->title);

            $discountSave->save();

            $id = $discountSave->id;
        }

        if($request->title_select) {
            $discountID = DiscountList::where('id', $request->title_select)->first();

            $id = $discountID;
        }

        return redirect(route('discount.list.category', [$id]));
    }

    public function category($did)
    {
        $categories = LbCategory::all();

        return view('admin.discount_list.category', ['title' => 'Leaderboard List Category', 'categories' => $categories, 'did' => $did]);
    }

    public function category_post(Request $request)
    {
        return redirect(route('discount.list.level.one', [$request->did, $request->category]));
    }

    public function level_one($did, $cate_id)
    {
        $leaderboards_level_two = [];
        $leaderboards_level_three = [];

        $leaderboards_level_one = LbLevelOne::where('lb_category_id', $cate_id)->groupBy('lb_name')->get();

        if(isset($_GET['two'])) {
            $leaderboards_level_two = LbLevelTwo::where('level_one_id', $_GET['two'])->groupBy('lb_two_name')->get();
        }

        if(isset($_GET['three'])) {
            $leaderboards_level_three = LbLevelThree::where('lavel_two_id', $_GET['three'])->groupBy('lb_three_name')->get();
        }


        $category_name = LbCategory::where('id', $cate_id)->first();

        return view('admin.discount_list.level_one', ['title' => 'Level One - Discount Leaderboard List', 'level_one' => $leaderboards_level_one, 'category' => $cate_id, 'category_name' => $category_name, 'did' => $did, 'level_two' => $leaderboards_level_two, 'level_three' => $leaderboards_level_three]);
    }

    public function level_one_post(Request $request)
    {
        if($request->three == '' || $request->three == '' || $request->three == '') {
            //return redirect()->back()->with('msg', 'Please select Level one, two, three.');
            return Redirect::back()->withErrors(['msg' => 'Please select Level one, two, three.']);
        } else {
            $save = new DiscountListLevels;

            $save->discount_list_id = $request->did;
            $save->level_one_id = $request->one;
            $save->level_two_id = $request->two;
            $save->level_three_id = $request->three;

            $save->save();

            return redirect(route('discount.list.add'));
        }



    }
}
