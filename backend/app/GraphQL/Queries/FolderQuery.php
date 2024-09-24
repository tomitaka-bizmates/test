<?php

namespace App\GraphQL\Queries;

use App\Models\Folder;

class FolderQuery
{
    public function folders()
    {
        return Folder::all();
    }

    public function folder($root, array $args)
    {
        return Folder::findOrFail($args['id']);
    }
}
