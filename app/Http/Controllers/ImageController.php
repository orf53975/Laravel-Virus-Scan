<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Image;
use Storage;
use DB;

class ImageController extends Controller
{
    /**
     * The system specific directory separator constant
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('images');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'images.*' => 'required|image'
        ]);

        // Create images directory
        $images_dir = 'public'.self::DS.'images';
        if (!Storage::has($images_dir)) {
            Storage::makeDirectory($images_dir);
        }

        $images = [];
        $errors = [];

        foreach ($request->file('images') as $file) {
            $file_size = $file->getClientSize();
            $names = $this->generateFileNames($file);
            $display_name = $names['display_name'];
            $file_name = $names['file_name'];

            if (Storage::putFileAs($images_dir, $file, $file_name)) {
                // Check the file for viruses
                $file_location = $images_dir.self::DS.$file_name;
                $has_viruses = $this->hasViruses($file_location);

                // Delete the infested file
                if ($has_viruses) {
                    Storage::delete($file_location);
                    $errors[] = "The image called '$display_name' was not uploaded because it has viruses";
                    continue;
                }

                $image = [
                    'display_name' => $display_name,
                    'file_name' => $file_name,
                    'file_size' => $file_size,
                    'created_at' =>  \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];

                $images[] = $image;
            } else {
                $errors[] = "The image called '$display_name' could not be uploaded";
            }
        }

        if (count($images) === 0) {
            array_unshift($errors, 'The images were not uploaded');
            return redirect('/images')->withErrors($errors);
        }

        if (Image::insert($images)) {
            return redirect('/images')
                ->with('message', 'The images were uploaded')
                ->withErrors($errors);
        } else {
            array_unshift($errors, 'The images were not uploaded');
            return redirect('/images')->withErrors($errors);
        }
    }

    /**
     * Scan a file with ClamAV to see if it has viruses
     *
     * @param  string  $file_location
     * @return boolean
     */
    private function hasViruses($file_location)
    {
        // Create the absolute path to the file
        $storage_path = storage_path('app');
        $file_location = $storage_path.self::DS.$file_location;
        try {
            // Create a new socket instance
            $socket = (new \Socket\Raw\Factory())->createClient('unix:///var/run/clamav/clamd.ctl');
            // Create a new instance of the Client
            $quahog = new \Xenolope\Quahog\Client($socket, 30, PHP_NORMAL_READ);
            // Scan a file
            $result = $quahog->scanFile($file_location);
        } catch (\Exception $e) {
            // This is here for people that dont have the ClamAV app installed
            return false;
        }

        return $result['status'] == 'OK' ? false : true;
    }

    /**
     * Generate names for a file
     *
     * @param  uploadedfile $file
     * @return array
     */
    private function generateFileNames($file)
    {
        $names = [];
        $full_name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $name = explode('.'.$extension, $full_name)[0];
        $name = str_slug($name);
        $names['display_name'] = $name;
        $name = substr($name, 0, 20);
        $name_length = strlen($name);
        $random_string_length = 100 - $name_length;
        $name = $name.'-'.str_random($random_string_length);
        $lower_case_name = strtolower($name.'.'.$extension);
        $names['file_name'] = $lower_case_name;
        return $names;
    }
}
