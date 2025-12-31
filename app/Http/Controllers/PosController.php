<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Addon;

class PosController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get();

        $customers = Customer::orderBy('name', 'asc')->get();
        
        // Get active addons
        $addons = Addon::where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get();

        return view('pos', compact('services', 'customers', 'addons'));
    }
}