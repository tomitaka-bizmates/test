<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthMutation
{
    /**
     * ユーザー登録
     */
    public function register($root, array $args, $context, $resolveInfo)
    {
        // バリデーションは Lighthouse の @rules ディレクティブで行われます

        // ユーザーの作成
        $user = User::create([
            'name' => $args['name'],
            'email' => $args['email'],
            'password' => Hash::make($args['password']),
        ]);

        // トークンの生成
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * ログイン
     */
    public function login($root, array $args, $context, $resolveInfo)
    {
        $user = User::where('email', $args['email'])->first();

        if (!$user || !Hash::check($args['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['提供された認証情報が正しくありません。'],
            ]);
        }

        // トークンの生成
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * ログアウト
     */
    public function logout($root, array $args, $context, $resolveInfo)
    {
        $user = $context->user();
        if ($user) {
            $user->currentAccessToken()->delete();
            return true;
        }
    
        throw new \Exception('ユーザーが認証されていません。');
    }
}
