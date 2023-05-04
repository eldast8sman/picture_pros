<?php

namespace App\Http\Controllers;

use App\Models\PhotoGallery;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\StorePhotoGalleryRequest;
use App\Http\Requests\UpdatePhotoGalleryRequest;

class PhotoGalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $photos = PhotoGallery::orderBy('id', 'desc');
        if($photos->count() > 0){
            $photos = $photos->get();
            foreach($photos as $photo){
                $photo->filename = url($photo->filename);
            }

            return response([
                'status' => 'success',
                'message' => 'Photo Gallery Fetched',
                'data' => $photos
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Photo is in the Gallery'
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePhotoGalleryRequest $request)
    {
        if($upload_image = FileController::uploadFile($request->file, 'gallery')){
            if($photo = PhotoGallery::create([
                'caption' => $request->caption,
                'filename' => 'img/gallery/'.$upload_image
            ])){
                return response([
                    'status' => 'success',
                    'message' => 'Photo added to the Gallery',
                    'data' => $photo
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Photo upload failed'
                ], 500);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Photo Gallery Upload failed'
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if(!empty($photo = PhotoGallery::find($id))){
            return response([
                'status' => 'success',
                'message' => 'Photo fetched',
                'data' => $photo
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Photo not found in the Gallery'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePhotoGalleryRequest $request, $id)
    {
        $photo = PhotoGallery::find($id);
        if(!empty($photo)){
            $all = $request->except(['file']);
            $old_path = "";
            if(!empty($request->file)){
                if($request->file instanceof UploadedFile){
                    $uploaded_image = FileController::uploadFile($request->file, 'gallery');
                    $all['filename'] = 'img/gallery/'.$uploaded_image;
                    if(!empty($photo->filename)){
                        $old_path = $photo->filename;
                    }
                }
            }

            if($photo->update($all)){
                if(!empty($old_path)){
                    FileController::delete_file($old_path);
                }

                return response([
                    'status' => 'success',
                    'message' => 'Photo updated successfully',
                    'data' => $photo
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Photo Update Failed'
                ], 500);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Photo was fetched'
            ], 404); 
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $photo = PhotoGallery::find($id);
        if(!empty($photo)){
            $photo->delete();
            if(!empty($photo->filename)){
                FileController::delete_file($photo->filename);
            }
            return response([
                'status' => 'success',
                'message' => 'Photo deleted successfully',
                'data' => $photo
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Photo was fetched'
            ], 404);
        }
    }
}
