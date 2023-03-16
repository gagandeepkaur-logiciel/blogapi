<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Transformers\RootCategoryTransformer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Filesystem;

class CategoryController extends Controller
{
    /**
     * Insert category
     */
    public function insert(Request $request)
    {
        $userid = auth()->user()->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required', 'unique:name',
            'parent_category' => 'required',
        ]);
        if ($validator->fails())
            return response()->json(['success' => false, 'message' => $validator]);

        try {
            $categoryid = DB::table('categories')
                ->where('name', $request->parent_category)
                ->first('id');

            if (!empty($categoryid))
                Category::create([
                    'userid' => $userid,
                    'name' => $request->name,
                    'category_id' => $categoryid->id,
                ]);

            return response()->json(['success' => 'Category inserted successfully']);
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }

    /**
     * Retrieve category list including all sub-categories
     */
    public function list(Request $request)
    {
        try {
            $categories = Category::whereNull('category_id')
                ->with('subcategories')->get();

            return $categories->transformWith(new RootCategoryTransformer())->toArray();
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }

    /**
     * Edit category name 
     */
    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails())
            return response()->json(['success' => false, 'message' => $validator]);

        try {
            Category::where('id', $id)->update([
                'name' => $request->name,
            ]);

            return response()->json(['success' => 'Updated successfully']);
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }

    /**
     * Can delete category which has no children  
     */
    public function delete($name)
    {
        try {
            Category::where('name', $name)->delete();

            return response()->json(['success' => 'Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }
}
