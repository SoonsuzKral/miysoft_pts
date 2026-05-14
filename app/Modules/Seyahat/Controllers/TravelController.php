<?php

namespace App\Modules\Seyahat\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TravelController extends Controller
{
    public function index(): View
    {
        return view('admin.travel.index', ['message' => 'Seyahat modülü yakında eklenecek.']);
    }

    public function create(): View
    {
        return view('admin.travel.index', ['message' => 'Yakında eklenecek.']);
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.travel.index')->with('info', 'Yakında.');
    }

    public function show(string $id): View
    {
        return view('admin.travel.index', ['message' => 'Yakında eklenecek.']);
    }

    public function edit(string $id): View
    {
        return view('admin.travel.index', ['message' => 'Yakında eklenecek.']);
    }

    public function update(Request $request, string $id)
    {
        return redirect()->route('admin.travel.index');
    }

    public function destroy(string $id)
    {
        return redirect()->route('admin.travel.index');
    }
}
