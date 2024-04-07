<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TemporaryImage;
use Illuminate\Support\Facades\Storage;

class TemporaryImageController extends Controller
{
    public function uploadTemporary(Request $request)
    {
        // return response()->json('test');
        if ($request->hasFile('photos')) {
            $images = $request->file('photos');
            $folders = [];

            foreach ($images as $image) {
                $filename = $image->getClientOriginalName();
                $folder = uniqid('image-', true);
                $image->storeAs('/images/tmp/' . $folder, $filename);
                TemporaryImage::create([
                    'folder' => $folder,
                    'file' => $filename,
                ]);
                $folders[] = $folder;
            }

            return $folders;
        }

        return '';
    }

    public function deleteTemporary(Request $request)
    {

        $payload = json_decode($request->getContent(), true);
        $folder = $payload[0];
        $temporaryImage = TemporaryImage::where('folder', $folder)->first();

        if ($temporaryImage) {
            try {
                // Delete files from storage
                Storage::deleteDirectory('/images/tmp/' . $temporaryImage->folder);

                // Delete record from the database
                $temporaryImage->delete();

                // Return success response
                return response()->noContent();
            } catch (\Exception $e) {
                // Log the error
                // \Log::error('Error deleting temporary image: ' . $e->getMessage());

                // Return error response
                return response()->json(['message' => 'Failed to delete temporary image.'], 500);
            }
        }

        // If no temporary image found with the given folder, return 404
        return response()->json(['message' => 'Temporary image not found.'], 404);
    }


    public function uploadImageDirectlyToDB(Request $request, $id)
    {
        if ($request->hasFile('photos')) {
            $images = $request->file('photos');
            $fileNameProduct = [];

            foreach ($images as $image) {
                $extensionTemp = $image->getClientOriginalExtension();
                $fileNameProductImage =  Str::random(20) . '.' . $extensionTemp;

                // Store the image in the public/images directory
                $image->move(public_path('storage/images'), $fileNameProductImage);

                // Store the image path in the database
                Image::create([
                    'path' => '/storage/images/' . $fileNameProductImage,
                    'product_id' => $id,
                ]);

                $fileNameProduct[] = $fileNameProductImage;
            }

            return $fileNameProduct;
        }

        return '';
    }
    // public function deleteImageDirectlyToDB(Request $request, $id){
    //     $img=Image::find($id);
        
    //     // Check if file exists in the storage and then delete it from there.
    //     if (Storage::exists('/images'.$img->path)) {
    //         Storage::delete('/images'.$img->path);
    //     }

    //     // Delete the record associated with this image from the images table.
    //     $img->delete();

    //     return response()->json(['success'=>true]);

    // }
}
