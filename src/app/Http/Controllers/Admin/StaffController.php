<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        // role = 'staff' のユーザーを取得
        $users = User::where('role', 'staff')->get();

        return view('admin.staff.index', compact('users'));
    }
}
