<?php

namespace App\Http\Controllers;

use App\Models\BuyerUser;
use Illuminate\Http\Request;

class BuyerAccountController extends Controller
{
    public function change_password(Request $request)
    {
        try {
            $buyerUserPass = BuyerUser::where('id', $request->bid)->select('password')->get();

            if(count($buyerUserPass) > 0) {
                if($buyerUserPass[0]->password == md5($request->old)) {

                    $update = BuyerUser::where('id', $request->bid)->update(['password' => md5($request->new)]);

                    if($update) {
                        return response()->json(['succ' => 'Your password has been changed successfully.']);
                    } else {
                        return response()->json(['err' => 'Something is wrong please try again later.']);
                    }
                } else {
                    return response()->json(['err' => 'Your old password not matched. Please correct your old password and try again.']);
                }
            }
        } catch(\Throwable $th) {
            return response()->json(['err' => $th->getMessage()]);
        }

    }
}