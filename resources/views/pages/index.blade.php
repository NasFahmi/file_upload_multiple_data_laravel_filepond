@extends('layout')
@section('title', "Index")
@section('content')
<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-4">Product List</h1>
    <a href="{{ route('product.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-block mb-4">Create Product</a>
    <table class="table-auto w-full">
        <thead>
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Slug</th>
                <th class="px-4 py-2">Price</th>
                <th class="px-4 py-2">Description</th>
                <th class="px-4 py-2">Photos</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td class="border px-4 py-2">{{ $product->name }}</td>
                <td class="border px-4 py-2">{{ $product->slug }}</td>
                <td class="border px-4 py-2">{{ $product->price }}</td>
                <td class="border px-4 py-2">{{ $product->description }}</td>
                <td class="border px-4 py-2">
                    <div class="flex">
                        {{-- <p>{{$product->images}}</p> --}}
                        @foreach($product->images as $photo)
                        <div class="w-1/4">
                            <img src="{{ asset($photo->path) }}" alt="Product Photo" class="w-full">
                        </div>
                        @endforeach
                    </div>
                </td>
                <td class="border px-4 py-2">
                    <a href="{{ route('product.edit',$product->id) }}" class="text-blue-500 hover:text-blue-700">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
