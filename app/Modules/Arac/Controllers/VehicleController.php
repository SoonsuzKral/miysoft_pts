<?php

namespace App\Modules\Arac\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(): View
    {
        return view('admin.vehicles.index', ['message' => 'Araç modülü yakında eklenecek.']);
    }

    public function create(): View
    {
        return view('admin.vehicles.index', ['message' => 'Yakında eklenecek.']);
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.vehicles.index')->with('info', 'Yakında.');
    }

    public function show(string $id): View
    {
        return view('admin.vehicles.index', ['message' => 'Yakında eklenecek.']);
    }

    public function edit(string $id): View
    {
        return view('admin.vehicles.index', ['message' => 'Yakında eklenecek.']);
    }

    public function update(Request $request, string $id)
    {
        return redirect()->route('admin.vehicles.index');
    }

    public function destroy(string $id)
    {
        return redirect()->route('admin.vehicles.index');
    }

    public function logs(string $vehicle)
    {
        return view('admin.vehicles.index', ['message' => 'Yakında eklenecek.']);
    }
}
