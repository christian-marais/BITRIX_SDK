<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function index()
    {   
       
        return view('upload.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $path = $request->file('file')->store('uploads', 'public');

        return back()->with('success', 'File uploaded successfully.');
    }
}
