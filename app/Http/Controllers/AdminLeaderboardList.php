<?php

namespace App\Http\Controllers;

use App\Models\LbCategory;
use App\Models\LbLevelOne;
use App\Models\LbLevelThree;
use App\Models\LbLevelTwo;
use App\Models\Leaderboard;
use App\Models\LeaderboardList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminLeaderboardList extends Controller
{
    public function category()
    {
        $categories = LbCategory::all();

        return view('admin.leaderboards.category', ['title' => 'Leaderboard List', 'categories' => $categories]);
    }

    public function category_post(Request $request)
    {
        $request->validate(
            [
                // 'category' => 'required|unique:lb_category'
                'category' => 'required'
            ],
            [
                'category.required' => 'Select a category first',
                // 'category.unique' => 'This category is already exist'
            ]
        );

        //$request->session()->put('category', $request->category);

        return redirect(route('lb.level.one', [$request->category]));
    }

    public function level_one($id)
    {
        $leaderboards_level_one = LbLevelOne::where('lb_category_id', $id)->get();

        $category_name = LbCategory::where('id', $id)->first();

        return view('admin.leaderboards.level_one', ['title' => 'Level One - Leaderboard List', 'level_one' => $leaderboards_level_one, 'category' => $id, 'category_name' => $category_name]);
    }

    public function level_one_post(Request $request)
    {
        $request->validate(
            [
                // 'category' => 'required|unique:lb_category'
                'category' => 'required',
                //'lb_name' => 'string|max:50',
                //'lb_order_no' => 'required|numeric',
            ],
            [
                'category.required' => 'Select a category first',
                'lb_name.max' => 'The Leaderboard list name must not be greater than 50 characters.',
                'lb_order_no.required' => 'Order is required',
                'lb_order_no.numeric' => 'Order should be numeric.'
                // 'category.unique' => 'This category is already exist'
            ]
        );

        try {
            $levenoneExist = LbLevelOne::where('id', $request->lb_name_exist)->get();

            if(count($levenoneExist) > 0) {
                $lvloneID = $levenoneExist[0]->id;
            } else {
                $levelOne = new LbLevelOne;

                $levelOne->lb_category_id = $request->category;
                $levelOne->lb_name = $request->lb_name;
                $levelOne->lb_order_no = $request->lb_order_no;
                $levelOne->discount = $request->discount;

                $levelOne->save();

                $lvloneID = $levelOne->id;

                $msg = 'Successfully added.';
            }

        } catch(\Throwable $th) {
            $msg = $th->getMessage();
        }

        return redirect(route('lb.level.two', [$lvloneID]));
    }

    public function level_two($id)
    {
        $leaderboards_level_two = LbLevelTwo::where('level_one_id', $id)->get();

        $leveloneName = LbLevelOne::where('id', $id)->first();

        return view('admin.leaderboards.level_two', ['title' => 'Level Two - Leaderboard List', 'level_two' => $leaderboards_level_two, 'leveltwo' => $id, 'leveloneName' => $leveloneName]);
    }

    public function level_two_post(Request $request)
    {
        $request->validate(
            [
                // 'category' => 'required|unique:lb_category'
                'category' => 'required',
                //'lb_name' => 'string|max:50',
                //'lb_order_no' => 'required|numeric',
            ],
            [
                'category.required' => 'Select a category first',
                'lb_name.max' => 'The Leaderboard list name must not be greater than 50 characters.',
                'lb_order_no.required' => 'Order is required',
                'lb_order_no.numeric' => 'Order should be numeric.'
                // 'category.unique' => 'This category is already exist'
            ]
        );

        try {
            $leventwoExist = LbLevelTwo::where('id', $request->lb_name_exist)->select('id')->get();

            if(count($leventwoExist) > 0) {
                $lvlTwoID = $leventwoExist[0]->id;
            } else {
                $levelTwo = new LbLevelTwo;

                $levelTwo->level_one_id = $request->category;
                $levelTwo->lb_two_name = $request->lb_name;
                $levelTwo->lb_two_order_no = $request->lb_order_no;
                $levelTwo->discount = $request->discount;

                $levelTwo->save();

                $lvlTwoID = $levelTwo->id;

                $msg = 'Successfully added.';
            }

        } catch(\Throwable $th) {
            $msg = $th->getMessage();
        }

        return redirect(route('lb.level.three', [$lvlTwoID]));
    }

    public function level_three($id)
    {
        $leaderboards_level_three = LbLevelThree::where('lavel_two_id', $id)->get();

        $leveltwoName = LbLevelTwo::where('id', $id)->first();

        return view('admin.leaderboards.level_three', ['title' => 'Level Three - Leaderboard List', 'level_three' => $leaderboards_level_three, 'leveltwo' => $id, 'leveltwoName' => $leveltwoName]);
    }

    public function level_three_post(Request $request)
    {
        $request->validate(
            [
                // 'category' => 'required|unique:lb_category'
                'category' => 'required',
                //'lb_name' => 'string|max:50',
                //'lb_order_no' => 'required|numeric',
            ],
            [
                'category.required' => 'Select a category first',
                'lb_name.max' => 'The Leaderboard list name must not be greater than 50 characters.',
                'lb_order_no.required' => 'Order is required',
                'lb_order_no.numeric' => 'Order should be numeric.'
                // 'category.unique' => 'This category is already exist'
            ]
        );

        try {
            $leventhreeExist = LbLevelThree::where('id', $request->lb_name_exist)->select('id')->get();

            if(count($leventhreeExist) > 0) {
                $lvlThreeID = $leventhreeExist[0]->id;

            } else {
                $levelThree = new LbLevelThree;

                $levelThree->lavel_two_id = $request->category;
                $levelThree->lb_three_name = $request->lb_name;
                $levelThree->lb_three_order_no = $request->lb_order_no;

                $levelThree->save();

                $lvlThreeID = $levelThree->id;



            }



        } catch(\Throwable $th) {
            return $th->getMessage();
        }

        return redirect(route('lb.level.four', [$lvlThreeID]));


    }

    public function level_four($id)
    {
        $leaderboards = Leaderboard::groupBy('title')->get();

        $levelthreeName = LbLevelThree::where('id', $id)->first();

        return view('admin.leaderboards.level_four', ['title' => 'Choose Leaderboards - Leaderboard List', 'leaderboards' => $leaderboards, 'levelthree' => $id, 'levelthreeName' => $levelthreeName]);
    }

    public function level_four_post(Request $request)
    {
        try{

            if($request->slug != null) {
                foreach($request->slug as $slug) {
                    $lb_list = new LeaderboardList;

                    $lb_list->level_three_id = $request->category;
                    $lb_list->leaderboard_slug = $slug;

                    $lb_list->save();
                }
            } else {
                $slug = Str::slug($request->lb_name_new, '-'); // create slug from title
                // get category name
                $level_three = LbLevelThree::where('id', $request->category)->first();
                $level_two = LbLevelTwo::where('id', $level_three->lavel_two_id)->first();
                $level_one = LbLevelOne::where('id', $level_two->level_one_id)->first();
                $category_id = LbCategory::where('id', $level_one->lb_category_id)->first();

                //save leaderboard name
                $lbname = new Leaderboard;

                $lbname->user_id = 0;
                $lbname->category = $category_id->name;
                $lbname->title = $request->lb_name_new;
                $lbname->slug = $slug;
                $lbname->dummy_lb = 1;

                $lbname->save();

                // save leaderboard list
                $lb_list = new LeaderboardList;

                $lb_list->level_three_id = $request->category;
                $lb_list->leaderboard_slug = $slug;

                $lb_list->save();
            }

            return redirect(route('lb.category'));

        } catch(\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function lb_list()
    {
        $categories = LbCategory::all();

        return view('admin.leaderboards.display.category', ['title' => 'Categories','categories' => $categories]);
    }

    public function lb_list_one($id)
    {
        $one = LbLevelOne::where('lb_category_id', $id)->get();

        return view('admin.leaderboards.display.lavel_one', ['title' => 'Lavel One','data' => $one]);
    }

    public function lb_list_two($id)
    {
        $two = LbLevelTwo::where('level_one_id', $id)->get();

        return view('admin.leaderboards.display.lavel_two', ['title' => 'Lavel Two','data' => $two]);
    }

    public function lb_list_three($id)
    {
        $three = LbLevelThree::where('lavel_two_id', $id)->get();

        return view('admin.leaderboards.display.lavel_three', ['title' => 'Lavel Three','data' => $three]);
    }

    public function lb_list_all($id)
    {
        $data = DB::table('leaderboard_list')->join('leaderboards', 'leaderboard_list.leaderboard_slug', '=', 'leaderboards.slug')->where('leaderboard_list.level_three_id', $id)->groupBy('leaderboards.title')->select('leaderboards.title', 'leaderboards.slug', 'leaderboards.id', 'leaderboards.category', 'leaderboard_list.level_three_id', 'leaderboards.user_id')->get();

        return view('admin.leaderboards.display.lavel_all', ['title' => 'Leaderboards','data' => $data]);
    }

    public function lb_list_brands($id, $slug)
    {
        $data = DB::table('leaderboards')->join('users', 'leaderboards.user_id', '=', 'users.id')->where('leaderboards.slug', $slug)->get();

        return view('admin.leaderboards.display.brands', ['title' => 'Brands','data' => $data]);
    }

    // public function delete_level(int $id, string $status)
    // {
    //     if($status == 'one')
    //     {
    //         $lb_list_one = LbLevelTwo::where('id', $id)->select('id')->first();
    //         $lb_list_two = LbLevelTwo::where('level_one_id', $lb_list_one->id)->select('id')->first();
    //         $lb_list_three = LbLevelThree::where('lavel_two_id', $lb_list_two->id)->select('id')->first();
    //         $lb_list = LeaderboardList::where('level_three_id', $lb_list_three->id)->get();
    //         if(count($lb_list) == 0) {
    //             LbLevelOne::where('id', $id)->delete();
    //             $two = LbLevelTwo::where('level_one_id', $id)->delete();
    //             LbLevelThree::where('lavel_two_id', $two)->delete();

    //             return redirect(route('leaderboard.list.one', [$id]));
    //         } else {
    //             return redirect(route('leaderboard.list.one', [$id]))->with('err', 'You can not delete this level');
    //         }


    //     }

    //     if($status == 'two')
    //     {
    //         $lb_list_two = LbLevelTwo::where('level_one_id', $id)->select('id')->first();
    //         $lb_list_three = LbLevelThree::where('lavel_two_id', $lb_list_two->id)->select('id')->first();
    //         $lb_list = LeaderboardList::where('level_three_id', $lb_list_three->id)->get();
    //         if(count($lb_list) == 0) {
    //             LbLevelTwo::where('id', $id)->delete();
    //             LbLevelThree::where('lavel_two_id', $id)->delete();

    //             return redirect(route('leaderboard.list.two', [$id]));
    //         } else {
    //             return redirect(route('leaderboard.list.two', [$id]))->with('err', 'You can not delete this level');
    //         }

    //     }

    //     if($status == 'three')
    //     {
    //         $lb_list = LeaderboardList::where('level_three_id', $id)->get();
    //         if(count($lb_list) == 0) {
    //             LbLevelThree::where('id', $id)->delete();

    //             return redirect(route('leaderboard.list.three', [$id]));
    //         } else {
    //             return redirect(route('leaderboard.list.three', [$id]))->with('err', 'You can not delete this level');
    //         }

    //     }


    // }

    public function edit_level($id, $idd, $status)
    {
        if($status === 'one') {
            $one = LbLevelOne::where('id', $id)->first();
        }

        if($status === 'two') {
            $one = LbLevelTwo::where('id', $id)->first();
        }

        if($status === 'three') {
            $one = LbLevelThree::where('id', $id)->first();
        }

        return view('admin.leaderboards.edit_level', ['data' => $one, 'id' => $id, 'status' => $status, 'idd' => $idd]);
    }

    public function edit_level_post(Request $request)
    {
        if($request->hd_status === 'one') {
            LbLevelOne::where('id', $request->hd_id)->update(['lb_name' => $request->lb_name, 'lb_order_no' => $request->lb_order_no, 'discount' => $request->discount]);
        }

        if($request->hd_status === 'two') {
            LbLevelTwo::where('id', $request->hd_id)->update(['lb_two_name' => $request->lb_name, 'lb_two_order_no' => $request->lb_order_no, 'discount' => $request->discount]);
        }

        if($request->hd_status === 'three') {
            LbLevelThree::where('id', $request->hd_id)->update(['lb_three_name' => $request->lb_name, 'lb_three_order_no' => $request->lb_order_no]);
        }


        return redirect()->back();
    }

    public function delete_level($id, $idd, $status)
    {
        if($status === 'one') {
            $one = LbLevelOne::where('id', $id)->delete();
            // $two = LbLevelTwo::where('level_one_id', $one)->delete();
            // $three = LbLevelThree::where('lavel_two_id', $two)->delete();
            // LeaderboardList::where('level_three_id', $three)->delete();


        }

        if($status === 'two') {
            $two = LbLevelTwo::where('id', $id)->delete();
            // $three = LbLevelThree::where('lavel_two_id', $two)->delete();
            // LeaderboardList::where('level_three_id', $three)->delete();
        }

        if($status === 'three') {
            $three = LbLevelThree::where('id', $id)->delete();
            // LeaderboardList::where('level_three_id', $three)->delete();
        }

        return redirect()->back();
    }
}
