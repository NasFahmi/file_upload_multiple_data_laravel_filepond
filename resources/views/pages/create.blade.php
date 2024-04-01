@extends('layout')
@section('title', 'Create Product')
@section('content')
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-4">Create Product</h1>
        <form method="POST" action="{{ route('product.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="name" class="block mb-1">Name</label>
                <input type="text" class="border border-gray-300 px-4 py-2 w-full" id="name" name="name" required>
            </div>
            <div class="mb-4">
                <label for="slug" class="block mb-1">Slug</label>
                <input type="text" class="border border-gray-300 px-4 py-2 w-full" id="slug" name="slug"
                    required>
            </div>
            <div class="mb-4">
                <label for="price" class="block mb-1">Price</label>
                <input type="number" class="border border-gray-300 px-4 py-2 w-full" id="price" name="price"
                    required>
            </div>
            <div class="mb-4">
                <label for="description" class="block mb-1">Description</label>
                <textarea class="border border-gray-300 px-4 py-2 w-full" id="description" name="description" rows="3" required></textarea>
            </div>
            <div class="mb-4">
                <label for="photos" class="block mb-1">Photos</label>
                <input type="file" class="border border-gray-300 px-4 py-2 w-full" id="photos" name="photos[]"
                    multiple required>
            </div>
            <div id="previewContainer" class="flex flex-wrap mb-4">
                <!-- Image preview will be appended here -->
            </div>
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit</button>
        </form>
        <a href="{{route('product.test')}}">test copy</a>
    </div>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script>
        FilePond.registerPlugin(FilePondPluginImagePreview);
        // Register the plugin
        FilePond.registerPlugin(FilePondPluginFileValidateType);
        // Get a reference to the file input element
        FilePond.registerPlugin(FilePondPluginFileValidateSize);
        const inputElement = document.getElementById("photos");

        // Create a FilePond instance
        const pond = FilePond.create(inputElement, {
            acceptedFileTypes: ['image/png', 'image/jpeg','image/jpg'],
            allowImagePreview: true,
            maxFileSize : '2MB',
            allowMultiple: true,
        });

        FilePond.setOptions({
            server: {
                process: {
                    url: '{{ route('upload.temporary') }}',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    }
                },
                revert: {
                    url: '{{ route('delete.temporary') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    }
                }
            },
        });
    </script>
@endsection
