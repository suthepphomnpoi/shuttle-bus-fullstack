<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReserveController extends Controller
{
    public function searchListPage()
    {
        if (!Auth::check() && !Auth::guard('employee')->check()) {
            return redirect('/auth/users/login');
        }

        return view('reserve.search-list');
    }
}
