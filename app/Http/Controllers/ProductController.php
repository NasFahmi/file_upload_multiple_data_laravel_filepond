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
            // 'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048' // Max 2MB per image
        ]);

        $dataAllImage = $request->photos; // Mendapatkan array dari request
        $decodedImages = [];
        foreach ($dataAllImage as $image) {
            $decodedImages[] = json_decode($image, true); // Mendekodekan string JSON menjadi array PHP dan menambahkannya ke dalam array $decodedImages
        }
        $dataImages = call_user_func_array('array_merge', $decodedImages);
        // dd($dataImages); //array berisi temp gambar yang diupload
        // Validate the form data
        DB::beginTransaction();
        try {
            if ($validator->fails()) {
                // Delete temporary images
                foreach ($dataImages as $temporaryImage) {
                    $tempImage = TemporaryImage::where('folder', $temporaryImage)->first(); //get single data temp image
                    // Delete files from storage
                    Storage::deleteDirectory('public/images/tmp/' . $tempImage->folder);

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

            // Store the product images
            if ($dataImages) {
                foreach ($dataImages as $image) {
                    $imageTemp = TemporaryImage::where('folder', $image)->first(); //get single data temp image
                    $extensionTemp = pathinfo($imageTemp->file, PATHINFO_EXTENSION); // Mendapatkan ekstensi file
                    $folderNameTemp = $imageTemp->folder;
                    $fileNameTemp = $imageTemp->file;
                    $fileNameProductImage =  Str::random(20) . '.' . $extensionTemp;
                    // dd($fileNameProductImage); //GdomcXRDdftRq30MjJPz.jpeg
                    // copy file image from storage\app\public\images\tmp\image-660a77aaf10368.27307606\WhatsApp Image 2024-03-18 at 9.29.38 PM.jpeg to storage\app\public\images\GdomcXRDdftRq30MjJPz.jpeg
                    $sourcesPath = 'public/images/tmp/' . $folderNameTemp . '/' . $fileNameTemp;
                    $destinationPath = 'public/images/'.$fileNameProductImage;
                    // dd($sourcesPath);
                    // dd($destinationPath);
                    Storage::copy($sourcesPath,$destinationPath);
                    Image::create([
                        'path' => 'storage/images/' . $fileNameProductImage,
                        'product_id' => $productId,
                    ]);
                    $imageTemp->delete();
                    Storage::deleteDirectory('public/images/tmp/' . $folderNameTemp);
                }
            }
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
        $data = Product::findOrFail($product->id);

        // Mengirimkan data produk ke view untuk diedit
        return view('pages.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        dd($request->all());
        $validatedData = $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products',
            'price' => 'required|numeric',
            'description' => 'required',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048' // Max 2MB per image
        ]);
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
        Storage::copy('public/test.txt','public/images/bukantest.txt');
        dd('succes');
    }
}
