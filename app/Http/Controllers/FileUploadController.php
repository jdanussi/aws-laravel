<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class FileUploadController extends Controller
{
    public function getFileUploadForm()
    {
        return view('file-upload');
    }
 
    public function store(Request $request)
    {
        $request->validate([
            #'file' => 'required|mimes:doc,csv,txt|max:2048',
            'file' => 'required|file|mimes:pdf,jpeg,png,jpg,gif,svg|max:2048',
        ]);
 
        $fileName = $request->file->getClientOriginalName();
        $white_label = 'white_label2';
        $filePath = $white_label. '/' . $fileName;
 
        $path = Storage::disk('s3')->put($filePath, file_get_contents($request->file));
        $path = Storage::disk('s3')->url($path);
 
        // Perform the database operation here
       
        $url = Storage::disk('s3')->url($filePath);
        return back()
            ->with('success', $url);
    }
}
