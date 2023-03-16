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
    public function insert(Request $request)
    {
        $input = $request->all();
        $user_id = auth()->user()->id;

        $validator = Validator::make($input, [
            'name' => 'required',
            'parent_id' => 'required',
        ]);

        if ($validator->fails())
            return response()->json([
                'success' => false,
                'message' => $validator
            ]);

        try {
            $data = Folder::where('id', $input['parent_id'])->first();

            if (!empty($data))
                $path = File::makeDirectory($data['path'] . '/' . $input['name'], 0777, true, true);

                $folder = Folder::create([
                    'userid' => auth()->user()->id,
                    'name' => $input['name'],
                    'folder_id' => $input['parent_id'],
                    'path' =>  $data['path']  . $input['name'] . '/',
                    'created_by' => auth()->user()->id,
                ]);

            return  fractal($folder, new FolderTransformer())->toArray();
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
            return response()->json($e->getMessage());
        }
    }

    public function list()
    {
        try {
            $folder = Folder::where('folder_id', 1)
                ->with('subfolders')->get();

            return  fractal($folder, new FolderTransformer())->toArray();
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

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
            $parent_path = Folder::where('id', $data['folder_id'])->first();
            File::moveDirectory($data['path'], $parent_path['path'] . '/' . $input['name'], true);

            $folder = Folder::where('id', $id)
                ->update([
                    'name' => $input['name'],
                    'path' => $parent_path['path'] . $input['name'] . '/',
                ]);

            return response()->json([
                'success' => 'Updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $folder = Folder::where('id', $id)->first();
            File::deleteDirectory($folder['path']);
            $folder->delete();

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
