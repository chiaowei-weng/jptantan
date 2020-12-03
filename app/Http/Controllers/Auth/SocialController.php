<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * 將用戶重定向到GitHub身份驗證頁面。
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGitHub()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * 從GitHub獲取用戶信息。
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGitHubCallback()
    {
        //
        $response = Socialite::driver('github')->user();
        $token    = $response->token;
        $email    = $response->email;
        $name     = $response->name;
        $name     = $name ? $name : $response->nickname;
        $name     = $name ? $name : strstr($email, '@', true);
        //

        $user = User::where('email', $response['email'])->first();

        DB::beginTransaction();
        try {
            // 不存在則新增
            if (! $user) {
                $user = $this->userService->createUserFromSocialLogin([
                    'name'   => $name,
                    'email'  => $email,
                    'status' => '未確認個人資料'
                ]);
            }
            // 新增 token 紀錄
            $token = $this->userService->createToken($user, 'github', $token);
            //
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
        DB::commit();

        // 登入
        Auth::guard()->login($user);

        if ($user->status === '未確認個人資料') {
            return redirect('/profile')->with('token', $token);
        }

        // if ($user->status === '未驗證信箱') {
        //     return redirect('/profile')->with('token', $token);
        // }

        return redirect('/')->with('token', $token);
    }
}
