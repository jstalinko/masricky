<?php

namespace App\Http\Controllers;

use App\Models\Info;
use Inertia\Inertia;
use App\Models\MetaSetting;
use Illuminate\Http\Request;

class JustOrangeController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('justorange-default');
    }
    public function blankPage(Request $request)
    {
        $data['meta'] = MetaSetting::find($request->id);
        return view('blank',$data);
    }

    public function getInfo()
    {
        $info = Info::orderBy('created_at', 'desc')->get();
        if (!$info) {
            return response()->json([
                'success' => false,
                'message' => 'No info available'
            ], 404);
        }else{
            return response()->json([
                'success' => true,
                'data' => $info
            ], 200);
        }
    }
}
