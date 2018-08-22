<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show']]);
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        //权限控制
        try {
            $this->authorize('update', $user);
        } catch (AuthorizationException $e) {
            $user->id = Auth::id();
            return redirect()->route('users.show', $user->id)->with('danger', '非法操作！');
        }

        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, ImageUploadHandler $uploader, User $user)
    {
        //权限控制
        try {
            $this->authorize('update', $user);
        } catch (AuthorizationException $e) {
            $user->id = Auth::id();
            return redirect()->route('users.show', $user->id)->with('danger', '非法操作！');
        }

        //获取请求的参数
        $data = $request->all();

        //如果上传了头像
        if ($request->avatar) {
            $result = $uploader->save($request->avatar, 'avatars', $user->id, 362);
            if ($result) {
                $data['avatar'] = $result['path'];
            }

            //删除原头像
            $avatar = $user->avatar;
            $avatar = public_path() . str_replace(config('app.url'), '', $avatar);
            @unlink($avatar);
        }

        //更新users表
        $user->update($data);
        return redirect()->route('users.show', $user->id)->with('success', '个人资料更新成功！');
    }
}
