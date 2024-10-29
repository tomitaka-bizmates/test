<?php

namespace App\GraphQL\Queries;

use App\Models\Folder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class FolderQuery
{
    public function folders($root, array $args, GraphQLContext $context, $resolveInfo)
    {
        // 現在認証されているユーザーを取得
        $user = $context->user();
        if(!$user){
            throw new \Exception('認証されていません');

        }

        // ユーザーのフォルダのみを取得
        return Folder::where('user_id', $user->id)->paginate($args['count'], ['*'], 'page', $args['page']);
    }
}