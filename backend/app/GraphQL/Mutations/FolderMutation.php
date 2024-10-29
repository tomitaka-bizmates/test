<?php

namespace App\GraphQL\Mutations;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FolderMutation
{
    public function createFolder($root, array $args)
    {
     $user = Auth::guard("sanctum")->user();
     if(!$user){
        throw new \Exception("ユーザーが認証されていません");
     }
     $folder = Folder::create([
        'title'=>$args["title"],
        'user_id'=>$user->id
     ]);
     return $folder;
    }

    public function updateFolder($root, array $args)
    {
        $folder = Folder::findOrFail($args['id']);
        $folder->update([
            'title' => $args['name'] ?? $folder->title,
        ]);
        return $folder;
    }

    public function deleteFolder($root, array $args)
    {
        $folder = Folder::findOrFail($args['id']);
        return $folder->delete();
    }
}
