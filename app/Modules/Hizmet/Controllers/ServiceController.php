<?php

namespace App\Modules\Hizmet\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', ['message' => 'Hizmet modülü yakında eklenecek.']);
    }

    public function create(): View
    {
        return view('admin.services.index', ['message' => 'Yakında eklenecek.']);
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.services.index')->with('info', 'Yakında.');
    }

    public function show(string $id): View
    {
        return view('admin.services.index', ['message' => 'Yakında eklenecek.']);
    }

    public function edit(string $id): View
    {
        return view('admin.services.index', ['message' => 'Yakında eklenecek.']);
    }

    public function update(Request $request, string $id)
    {
        return redirect()->route('admin.services.index');
    }

    public function destroy(string $id)
    {
        return redirect()->route('admin.services.index');
    }
}
