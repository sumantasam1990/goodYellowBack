<?php

namespace App\Http\Controllers;
use App\Models\BuyerUser;

use Illuminate\Http\Request;
use App\Traits\CustomPagination;
use Illuminate\Pagination\CursorPaginator;

class AdminBuyersController extends Controller
{
    use CustomPagination;

    public function buyers_list(Request $request)
    {
        if (!$request->session()->has('loggedIn')) {
            return redirect(route('admin.login'));
        }

        $users = BuyerUser::orderBy('created_at', 'DESC')->get();

        foreach($users as $u) {

            $data[] = [
                'fname' => $u->fname,
                'lname' => $u->lname,
                'email' => $u->email,
                'street_addr' => $u->street_addr,
                'city' => $u->city,
                'state' => $u->state,
                'zip' => $u->zip,
                'phone' => $u->phone,
                'verification_email' => $u->email_verified,
                'buyer_promo' => $u->buyer_promo,
            ];
        }

        $users_pagi = $this->paginate($data, 50);

        // here you can set your pagination path/route.
        $users_pagi->withPath('');

        return view('admin.buyers_list', ['users' => $users_pagi]);
    }
}
