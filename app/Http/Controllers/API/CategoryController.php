<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('active', true)->orderBy('id','desc')->get();
        return response()->json([
            'success' =>true,
            'data' => $categories
        ] , 200,[],JSON_PRETTY_PRINT);
        
    }

    public function getFromId(Request $request)
    {
        $id = $request->id;
        $category = Category::find($id);
        if(!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $category
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function getFromSlug(Request $request)
    {
        $slug = $request->slug;
        $category = Category::where('slug', $slug)->first();
        if(!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $category
        ], 200, [], JSON_PRETTY_PRINT);
    }

}
