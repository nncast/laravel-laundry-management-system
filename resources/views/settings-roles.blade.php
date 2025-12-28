@extends('layouts.app')

@section('title', 'File Tools')
@section('page-title', 'File Tools')
@section('active-file-tools', 'active') <!-- change your sidebar/menu key -->

@section('content')
<div class="container mx-auto py-6">
    <div class="bg-white shadow rounded-lg p-6 max-w-lg mx-auto">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Upload Files</h2>

        <form action="{{ route('filetools.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="file" class="block text-gray-700 font-medium mb-2">Select File</label>
                <input 
                    type="file" 
                    name="file" 
                    id="file"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
                @error('file')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 text-white font-medium py-2 px-4 rounded hover:bg-blue-700 transition">
                Upload
            </button>
        </form>
    </div>
</div>
@endsection
