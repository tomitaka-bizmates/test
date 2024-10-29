<?php

namespace App\GraphQL\Mutations;

use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class FolderMutation
{
    public function createFolder($root, array $args, GraphQLContext $context)
    {
    //  $user = Auth::guard("sanctum")->user();
    $user = $context->user();
     if(!$user){
        throw new \Exception("ユーザーが認証されていません");
     }
     $folder = Folder::create([
        'title'=>$args["title"],
        'user_id'=>$user->id
     ]);
     return $folder;
    }

    public function updateFolder($root, array $args, GraphQLContext $context)
    {
        // $user = Auth::guard("sanctum")->user();
        $user = $context->user();
        if(!$user){
           throw new \Exception("ユーザーが認証されていません");
        }

        $folder = Folder::findOrFail($args['id']);
        $folder->update([
            'title' => $args['tile'] ?? $folder->title,
        ]);
        return $folder;
    }

    public function deleteFolder($root, array $args, GraphQLContext $context)

    {
        // $user = Auth::guard("sanctum")->user();
        $user = $context->user();
        if(!$user){
           throw new \Exception("ユーザーが認証されていません");
        }
        $folder = Folder::where('id',$args['id'])->where('user_id',$user->id)->firstOrFail();
        $folder->delete();
        return $folder;
    }
}
