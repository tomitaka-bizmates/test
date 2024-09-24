<?php

namespace App\GraphQL\Mutations;

use App\Models\Folder;

class FolderMutation
{
    public function createFolder($root, array $args)
    {
        return Folder::create([
            'name' => $args['name'],
            'description' => $args['description'] ?? null,
        ]);
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
