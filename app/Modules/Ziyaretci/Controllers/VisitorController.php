<?php

namespace App\Modules\Ziyaretci\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorController extends Controller
{
    public function index(): View
    {
        return view('admin.visitors.index', ['message' => 'Ziyaretçi modülü yakında eklenecek.']);
    }

    public function create(): View
    {
        return view('admin.visitors.index', ['message' => 'Yakında eklenecek.']);
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.visitors.index')->with('info', 'Yakında.');
    }

    public function show(string $id): View
    {
        return view('admin.visitors.index', ['message' => 'Yakında eklenecek.']);
    }

    public function edit(string $id): View
    {
        return view('admin.visitors.index', ['message' => 'Yakında eklenecek.']);
    }

    public function update(Request $request, string $id)
    {
        return redirect()->route('admin.visitors.index');
    }

    public function destroy(string $id)
    {
        return redirect()->route('admin.visitors.index');
    }

    public function badge(string $visitor)
    {
        return view('admin.visitors.index', ['message' => 'Yakında eklenecek.']);
    }
}
