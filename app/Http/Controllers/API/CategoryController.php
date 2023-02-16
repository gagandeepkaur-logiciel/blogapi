<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Transformers\RootCategoryTransformer;
use Spatie\Fractal\Fractal;

class CategoryController extends Controller
{
    public function insert(Request $request)
    {
        $userid = auth()->user()->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required', 'unique:name',
            'parent_category' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator]);
        }
        try {
            $categoryid = DB::table('categories')->where('name', $request->parent_category)->first('id');
            if (!empty($categoryid)) {
                DB::table('categories')->insert([
                    'userid' => $userid,
                    'name' => $request->name,
                    'category_id' => $categoryid->id,
                ]);
            } else {
                DB::table('categories')->insert([
                    'userid' => $userid,
                    'name' => $request->name,
                    'category_id' => NULL,
                ]);
            }
            return response()->json(['success' => 'Category inserted successfully']);
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }

    public function list(Request $request)
    {
        try {
            $categories = Category::whereNull('category_id')->with('subcategories')->get();
            return $categories->transformWith(new RootCategoryTransformer())->toArray();
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }

    public function edit(Request $request, $name)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required', 'unique:name',
        ]);
        if($validator->fails()){
            return response()->json(['success' => false, 'message' => $validator]);
        }
        try {
            Category::where('name', $name)->update([
                'name' => $request->name,
            ]);
            return response()->json(['success' => 'Updates successfully']);
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }

    public function delete($name){
        try {
            Category::where('name', $name)->delete();
            return response()->json(['success' => 'Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json($e);
        }
    }
}
