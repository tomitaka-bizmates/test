<?php

namespace App\GraphQL\Queries;

class UserQuery
{
    /**
     * 現在認証されているユーザーを返す
     */
    public function me($root, array $args, $context, $resolveInfo)
    {
        return auth()->guard();
    }
}
