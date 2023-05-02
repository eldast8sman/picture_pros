<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $testimonials = Testimonial::orderBy('id', 'asc');
        if($testimonials->count() > 0){
            $testimonials = $testimonials->get();
            foreach($testimonials as $testimonial){
                $testimonial->filename = url($testimonial->filename);
            }

            return response([
                'status' => 'success',
                'message' => 'Testimonials fetched',
                'data' => $testimonials
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Testimonial has been uploaded'
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTestimonialRequest $request)
    {
        $all = $request->except(['file']);
        $upload_image = FileController::uploadFile($request->file, 'testimonial');
        if($upload_image){
            $all['filename'] = 'img/testimonial/'.$upload_image;
        }
        if($testimonial = Testimonial::create($all)){
            return response([
                'status' => 'success',
                'message' => 'Testimonial added successfully',
                'data' => $testimonial
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Testimonial upload failed'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if(!empty($testimonial = Testimonial::find($id))){
            $testimonial->filename = url($testimonial->filename);

            return response([
                'status' => 'success',
                'message' => 'Testimonia fetched successfully',
                'data' => $testimonial
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Testimonial was fetched'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTestimonialRequest $request, $id)
    {
        $testimonial = Testimonial::find($id);
        if(!empty($testimonial)){
            $all = $request->except(['file']);
            $old_path = "";
            if(!empty($request->file)){
                if($request->file instanceof UploadedFile){
                    $uploaded_image = FileController::uploadFile($request->file, 'testimonial');
                    $all['filename'] = 'img/testimonial/'.$uploaded_image;
                    if(!empty($testimonial->filename)){
                        $old_path = $testimonial->filename;
                    }
                }
            }

            if($testimonial->update($all)){
                if(!empty($old_path)){
                    FileController::delete_file($old_path);
                }

                return response([
                    'status' => 'success',
                    'message' => 'Testimonial updated successfully',
                    'data' => $testimonial
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Testimonial Update Failed'
                ], 500);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Testimonial was fetched'
            ], 404); 
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);
        if(!empty($testimonial)){
            $testimonial->delete();
            if(!empty($testimonial->filename)){
                FileController::delete_file($testimonial->filename);
            }
            return response([
                'status' => 'success',
                'message' => 'Testimonial deleted successfully',
                'data' => $testimonial
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Testimonial was fetched'
            ], 404);
        }
    }
}
