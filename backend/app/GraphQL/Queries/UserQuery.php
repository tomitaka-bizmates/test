<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UserQuery
{
    /**
     * 現在認証されているユーザーを返す
     */
    
    public function me($root, array $args, GraphQLContext $context, $resolveInfo): ?User
    {
        return $context->user();
    }
}
