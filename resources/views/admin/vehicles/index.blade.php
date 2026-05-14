@extends('layouts.app')
@section('title', 'Araç Yönetimi')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Araç Yönetimi</h1>
        <p class="text-gray-600">{{ $message ?? 'Yakında eklenecek.' }}</p>
    </div>
</div>
@endsection
