<?php

namespace App\Http\Controllers;

use App\Models\MetaSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
}
