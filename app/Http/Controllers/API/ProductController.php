<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('active', true)->orderBy('id','desc')->get();
        return response()->json([
            'success' =>true,
            'data' => $products
        ] , 200,[],JSON_PRETTY_PRINT);
    }


    public function getFromCategory(Request $request)
    {
        $category_id = $request->category_id;
        $products = Product::where('category_id', $category_id)->orderBy('id','desc')->get();
        return response()->json([
            'success' =>true,
            'data' => $products
        ] , 200,[],JSON_PRETTY_PRINT);
    }

    public function getFromId(Request $request)
    {
        $id = $request->id;

        $product = Product::find($id);
        if(!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $product
        ], 200, [], JSON_PRETTY_PRINT);
    }


}
