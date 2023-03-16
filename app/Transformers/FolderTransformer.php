<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Folder;

class FolderTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        'subfolders'
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Folder $folder)
    {
        return [
            'id' => $folder->id,
            'parent_id' => $folder->folder_id,
            'name' => $folder->name,
            'path' => $folder->path,
            'created_by' => $folder->created_by,
        ];
    }

    public function includeSubfolders(Folder $folder)
    {
        if (!$folder->subfolders->isEmpty()) {
            $transform = FolderTransformer::setdefaultIncludes(['subfolders']);
            $subfolders = $folder->subfolders;
            return $this->collection($subfolders, $transform);
        }
    }
}
