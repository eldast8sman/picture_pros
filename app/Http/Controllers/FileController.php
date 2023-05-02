<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class FileController extends Controller
{
    public static function uploadFile($filepath, $destination){
        if($filepath instanceof UploadedFile){
            $time = time();
            $filename = Str::random().$time;
            $extension = $filepath->getClientOriginalExtension();
            $name = $filename.'.'.$extension;
            if(($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'gif') || ($extension == 'png')){
                $directory = public_path('img/'.$destination.'/');
                if(!File::exists($directory)){
                    File::makeDirectory($directory, 0777, true);
                }
                $filepath->move(public_path('img/'.$destination.'/'), $name);
                return $name;
            } elseif(($extension == 'mp3') || ($extension == 'mpeg3') || ($extension == "mpeg")){
                $filepath->move(public_path('audio/'.$destination.'/'), $name);
                return $name;
            } elseif($extension == 'pdf'){
                $filepath->move(public_path('document/'.$destination.'/'), $name);
                return $name;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Checks if a given file exists
     */
    public static function check_file($filepath){
        if(File::exists($filepath)){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Deletes a given file after it confirms the existence of the file
     */
    public static function delete_file($filepath){
        if(self::check_file($filepath)){
            if(File::delete($filepath)){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
