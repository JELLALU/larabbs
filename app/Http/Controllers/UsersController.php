<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UsersController extends Controller
{
    public function show (User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit (User $user)
    {
        if (Auth::id() != $user->id) {
            $user->id = Auth::id();
            return  redirect()->route('users.show', $user->id)->with('danger', '非法操作！');
        }
        return view('users.edit', compact('user'));
    }

    public function update (UserRequest $request, User $user)
    {
        $user->update($request->all());
        return redirect()->route('users.show', $user->id)->with('success', '个人资料更新成功！');
    }
}
