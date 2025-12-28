<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class PosController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', 1)->get(); // Only active services
        return view('pos', compact('services'));
    }
}
