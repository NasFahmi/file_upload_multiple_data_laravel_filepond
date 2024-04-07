<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TemporaryImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Client\Request as ClientRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products  = Product::with(['images'])->get();
        return view('pages.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Create a validator instance
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:products',
            'price' => 'required|numeric',
            'description' => 'required',
          
        ]);

        $dataAllImage = $request->photos; // Mendapatkan array dari request
        $decodedImages = [];
        foreach ($dataAllImage as $image) {
            $decodedImages[] = json_decode($image, true); // Mendekodekan string JSON menjadi array PHP dan menambahkannya ke dalam array $decodedImages
        }
        $dataImages = call_user_func_array('array_merge', $decodedImages);
       

        DB::beginTransaction();
        try {
            if ($validator->fails()) {
                // Delete temporary images
                foreach ($dataImages as $temporaryImage) {
                    $tempImage = TemporaryImage::where('folder', $temporaryImage)->first(); //get single data temp image
                    // Delete files from storage
                    Storage::deleteDirectory('/images/tmp/' . $tempImage->folder);

                    // Delete record from the database
                    $tempImage->delete();
                }

                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Create the product   
            $validatedData = $request->all();
            $product = new Product();
            $product->name = $validatedData['name'];
            $product->slug = $validatedData['slug'];
            $product->price = $validatedData['price'];
            $product->description = $validatedData['description'];
            $product->save();
            $productId = $product->id;

            $this->uploadImage($dataImages, $productId);
            // Store the product images
            DB::commit();
            return redirect()->route('product.index')->with('success', 'Product created successfully.');
        } catch (\Throwable $th) {
            // Rollback database transaction on error
            DB::rollBack();
            throw $th;
            return redirect()->back()->with('error', 'Failed to create product: ' . $th->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $product)
    {
        // Mengambil data produk yang akan diedit
        $data = Product::with('images')->findOrFail($product->id);
        $images = Image::where('product_id', $product->id)->get()->map(function ($image) {
            return [
                'source' => $image->path,
                'options' => [
                    'type' => 'local'
                ]
            ];
        })->toArray();
       
        return view('pages.edit', compact('data', 'images'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    //! use case jika tidak ada perbuhan data image
    //! jika ada penambahan image
    //! jika ada pergantian image
    //! jika ada penghapusan image 
    {


        try {
            DB::beginTransaction();
            // Validasi data
            // dd($request->all());
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'slug' => 'required',
                'price' => 'required|numeric',
                'description' => 'required',
            ]);

            // Jika validasi gagal, kembalikan respon dengan pesan kesalahan
            if ($validator->fails()) {
                // dd($validator);
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Mendapatkan semua data gambar dari request
            $dataAllImage = $request->photos;
            // dd($dataAllImage);
            // Membagi gambar baru dan gambar lama berdasarkan format JSON
            $newPhotos = array_filter($dataAllImage, function ($item) {
                return preg_match('/^\[".*"\]$/', $item);
            }); //! new photos was upload direcly into database
            $oldPhotos = array_filter($dataAllImage, function ($item) {
                return !preg_match('/^\[".*"\]$/', $item);
            }); //!old photos
            // dd($newPhotos);
            $decodedImages = [];
            $newdataImages = [];
            if (isset($newPhotos)) {
                $decodedImages = [];
                foreach ($newPhotos as $image) {
                    $decodedImages[] = json_decode($image, true);
                }
                $newdataImages = call_user_func_array('array_merge', $decodedImages);

                // Ubah setiap nama file menjadi path yang diinginkan
                $newdataImages = array_map(function ($filename) {
                    return '/storage/images/' . ltrim($filename, '/');
                }, $newdataImages);
            }
            $combinedImage = array_merge($newdataImages, $oldPhotos);
        
            Product::findOrFail((int)$id)
                ->update([
                    'name' => $request->name,
                    'slug' => $request->slug,
                    'price' => $request->price,
                    'description' => $request->description,
                ]);

            if (isset($combinedImage)) {
                $allOldPhotos = Image::where('product_id', (int)$id)->pluck('path')->toArray();
               
                $photosToDelete = array_diff($allOldPhotos, $combinedImage); //array

                if (!empty($photosToDelete)) {
                    foreach ($photosToDelete as $photo) {
                        Image::where('path', $photo)->delete();
                        Storage::delete(str_replace('/storage', '/public', $photo));
                    }
                }
                // dd('end foreach');
            }

            DB::commit();
            return redirect()->back()->with('success', 'Product Edited successfully.');
            // dd($productData);
        } catch (\Throwable $th) {
            // dd($th);
            throw $th;
            return redirect()->back()->with('error', 'Failed to create product: ' . $th->getMessage());
            DB::rollBack();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
    public function test()
    {
        Storage::copy('public/images/tmp/image-66115093f11340.28351473\3fec67cc1c1b990c0643d7b231344aaf.jpg', 'public/images/testing.jpg');
        // dd('succes');
        // Storage::delete('public/images/O60fAYsOELhPQMMBvuc9.jpg');//works
        dd('success');
    }
    public function uploadImage($dataImage, $productId)
    {
        foreach ($dataImage as $image) {
            $imageTemp = TemporaryImage::where('folder', $image)->first(); //get single data temp image
            $extensionTemp = pathinfo($imageTemp->file, PATHINFO_EXTENSION); // Mendapatkan ekstensi file
            $folderNameTemp = $imageTemp->folder;
            $fileNameTemp = $imageTemp->file;
            $fileNameProductImage =  Str::random(20) . '.' . $extensionTemp;
            // dd($fileNameProductImage); //GdomcXRDdftRq30MjJPz.jpeg
            // copy file image from storage\app\public\images\tmp\image-660a77aaf10368.27307606\WhatsApp Image 2024-03-18 at 9.29.38 PM.jpeg to storage\app\public\images\GdomcXRDdftRq30MjJPz.jpeg
            $sourcesPath = '/images/tmp/' . $folderNameTemp . '/' . $fileNameTemp;
            $destinationPath = '/images/' . $fileNameProductImage;
            // dd($sourcesPath);
            // dd($destinationPath);
            Storage::copy($sourcesPath, $destinationPath);
            Image::updateOrInsert([ //! hanya bekerja di store, namun tidak bekerja di update
                'path' => '/storage/images/' . $fileNameProductImage,
                'product_id' => $productId,
            ]);
            // dd($isertimagedb);
            $imageTemp->delete();
            Storage::deleteDirectory('/images/tmp/' . $folderNameTemp);
        }
        // dd($dataImage,$productId,$isertimagedb);
    }
}
