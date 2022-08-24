<?php

namespace App\Http\Controllers;

use App\Models\Credits;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function index($bid)
    {
        $today = date('Y-m-d H:i:s');
        $arr = [];

        $membership = Credits::where('buyer_id', $bid)->first();

        $arr = [
            'exp_date' => date('F d, Y', strtotime($membership->end_date)),
            'expiry' => $membership->end_date > $today ? 'No' : 'Yes',
        ];

        return response()->json(['data' => $arr]);
    }
}