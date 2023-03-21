<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Validator,
    Storage,
    Log,
    File
};
use App\Models\Folder;
use App\Transformers\FolderTransformer;

class FolderController extends Controller
{
    public $dir_name, $name, $parent_dir;

    public function insert(Request $request)
    {
        $input = $request->all();
        $this->name = $input['name'];
        $user_id = auth()->user()->id;

        $validator = Validator::make($input, [
            'name' => 'required', 'unique',
            'parent_id' => 'required',
        ]);

        if ($validator->fails())
            return response()->json([
                'success' => false,
                'message' => $validator
            ]);

        try {
            $data = Folder::where('id', $input['parent_id'])->first();
            $this->dir_name = $data['name'];
            $files = Storage::allDirectories('/public');

            if (!empty($files)) {
                array_walk($files, function ($v) {
                    $e = explode('/', $v);
                    if (end($e) == $this->dir_name) {
                        $path = implode('/', $e);
                        Storage::makeDirectory('/' . $path . '/' . $this->name);
                    }
                });
            }

            if (!empty($data)) {
                $folder = Folder::create([
                    'userid' => auth()->user()->id,
                    'name' => $input['name'],
                    'folder_id' => $input['parent_id'],
                    'created_by' => auth()->user()->id,
                ]);
            }

            return  fractal($folder, new FolderTransformer())->toArray();
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
            return response()->json($e->getMessage());
        }
    }

    public function list()
    {
        try {
            $folder = Folder::whereNull('folder_id')
                ->with('subfolders')
                ->get();

            return  fractal($folder, new FolderTransformer())->toArray();
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()]);
        }
    }

    public function rename(Request $request, $id)
    {
        $input = $request->all();
        $this->name = $input['name'];

        $validator = Validator::make($input, [
            'name' => 'required',
        ]);

        if ($validator->fails())
            return response()->json([
                'success' => false,
                'message' => $validator,
            ]);

        try {
            $data = Folder::where('id', $id)->first();
            $this->dir_name = $data['name'];
            $parent_path = Folder::where('id', $data['folder_id'])->first();
            $this->parent_dir = $parent_path['name'];
            $files = Storage::allDirectories('/public');
            
            if (!empty($files)) {
                array_walk($files, function ($v) {
                    $e = explode('/', $v);
                    if (end($e) == $this->parent_dir) {
                        $path = implode('/', $e);
                        Storage::move($path.'/'.$this->dir_name, $path . '/' . $this->name);
                    }
                });
            }
            
            $folder = Folder::where('id', $id)
                ->update([
                    'name' => $input['name'],
                ]);

            return response()->json([
                'success' => 'Rename successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $data = Folder::where('id', $id)->first();
            $this->dir_name = $data['name'];
            $files = Storage::allDirectories('/public');
            
            if (!empty($files)) {
                array_walk($files, function ($v) {
                    $e = explode('/', $v);
                    if (end($e) == $this->dir_name) {
                        $path = implode('/', $e);
                        Storage::deleteDirectory($path);
                    }
                });
            }
            $data->delete();

            return response()->json([
                'success' => 'Deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function permanent_delete($id)
    {
        try {
            Folder::where('id', $id)->withTrashed()->forceDelete();

            return response()->json([
                'success' => 'Permanent deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            Folder::where('id', $id)->withTrashed()->restore();

            return response()->json([
                'success' => 'Restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function restore_all()
    {
        try {
            Folder::onlyTrashed()->restore();

            return response()->json([
                'success' => 'All restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }
}
