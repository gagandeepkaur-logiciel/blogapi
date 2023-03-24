<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;

class FolderTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */

    public $fn, $path;

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
        $this->fn = $folder->name;
        $files = Storage::allDirectories('/public/directoryManager');
        array_walk($files, function ($v) {
            $e = explode('/', $v);
            if (end($e) == $this->fn) {
                $this->path = implode('/', $e);
            }
        });
        
        return [
            'id' => $folder->id,
            'parent_id' => $folder->folder_id,
            'name' => $folder->name,
            'path' => $this->path,
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
