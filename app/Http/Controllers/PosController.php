<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Customer;

class PosController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', 1)
            ->orderBy('name', 'asc')   // Alphabetical
            ->get();

        $customers = Customer::orderBy('name', 'asc')->get(); // Fetch all customers alphabetically

        return view('pos', compact('services', 'customers'));
    }
}
