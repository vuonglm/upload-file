<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SoareCostin\FileVault\Facades\FileVault;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $files = Storage::files('files/' .auth()->user()->id);

        return view('home', compact('files'));
    }

    /**
     * Store a upload file
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if($request->hasFile('userFile') && $request->file('userFile')->isValid())
        {
            $fileName = Storage::putFile('files/' .auth()->user()->id , $request->file('userFile'));
        }      

        if($fileName){
            FileVault::encrypt($fileName);
        }

        return redirect()->route('home')->with('message', 'Upload complete!');
    }

    /**
     * Download a file
     * 
     * @param string $fileName
     * @return \Illuminate\Http\Response
     */
    public function downloadFile($fileName)
    {
        if(!Storage::has('files/' .\auth()->user()->id .'/' .$fileName)){
            \abort(404);
        }

        return response()->streamDownload(function () use ($fileName){
            FileVault::streamDecrypt('files/' .auth()->user()->id .'/' .$fileName);
        }, Str::replaceLast('.enc', '', $fileName));
    }

}
