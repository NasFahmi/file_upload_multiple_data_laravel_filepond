@extends('layout')
@section('title', 'Edit Product')
@section('content')
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-4">Edit Product</h1>
        <form method="POST" action="{{ route('product.update', $data->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="mb-4">
                <label for="name" class="block mb-1">Name</label>
                <input type="text" class="border border-gray-300 px-4 py-2 w-full" id="name" name="name"
                    value="{{ $data->name }}" required>
            </div>
            <div class="mb-4">
                <label for="slug" class="block mb-1">Slug</label>
                <input type="text" class="border border-gray-300 px-4 py-2 w-full" id="slug" name="slug"
                    value="{{ $data->slug }}" required>
            </div>
            <div class="mb-4">
                <label for="price" class="block mb-1">Price</label>
                <input type="number" class="border border-gray-300 px-4 py-2 w-full" id="price" name="price"
                    value="{{ $data->price }}" required>
            </div>
            <div class="mb-4">
                <label for="description" class="block mb-1">Description</label>
                <textarea class="border border-gray-300 px-4 py-2 w-full" id="description" name="description" rows="3" required>{{ $data->description }}</textarea>
            </div>
            <div class="mb-4">
                <label for="photos" class="block mb-1">Photos</label>
                <input type="file" class="border border-gray-300 px-4 py-2 w-full" id="photos" name="photos[]"
                    multiple required>
            </div>
            <div id="previewContainer" class="flex flex-wrap mb-4">
                <!-- Image preview will be appended here -->
            </div>
            <button type="submit" id="submitbtn" disabled
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit</button>
        </form>
        <a href="{{ route('product.test') }}">test copy</a>
        {{-- <p>{{ Storage::disk('public')->url($images) }}</p> --}}
        <p>{{ $data }}</p>

        {{-- <p>{{ $images }}</p> --}}
    </div>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script>
        let submitbtn = document.getElementById('submitbtn');
        FilePond.registerPlugin(FilePondPluginImagePreview);
        FilePond.registerPlugin(FilePondPluginFileValidateType);
        FilePond.registerPlugin(FilePondPluginFileValidateSize);
        const inputElement = document.getElementById("photos");

        const pond = FilePond.create(inputElement, {
            acceptedFileTypes: ['image/png', 'image/jpeg', 'image/jpg'],
            allowImagePreview: true,
            maxFileSize: '2MB',
            allowMultiple: true,
            // files: [{
            //         source: 'http://127.0.0.1:8000/storage/images/RTfaZNir7B6AHKJmKL8S.jpeg',
            //         options: {
            //             type: 'local',
            //         },
            //     },
            //     {
            //         source: 'http://127.0.0.1:8000/storage/images/RTfaZNir7B6AHKJmKL8S.jpeg',
            //         options: {
            //             type: 'local',
            //         },
            //     }
            // ],
            load: (source, load, error, progress, abort, headers) => {
                const myRequest = new Request(source);
                console.log(myRequest);
                fetch(myRequest).then((res) => {
                    return res.blob();
                }).then(load);
            },
        });

        FilePond.setOptions({
            required: true,
            onprocessfile: (error, file) => {
                if (!error) {
                    submitbtn.removeAttribute("disabled");
                    // Tambahan untuk update file di server (contoh)
                    const formData = new FormData();
                    formData.append('file', file.file);
                    // Implementasikan upload ke server Anda
                    fetch('{{ route('product.update', $data->id) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            },
                            body: formData,
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            // Handle response (misalnya update status file)
                        });
                }
            },
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
                },
            },
            files: [{
                    source: 'http://127.0.0.1:8000/storage/images/RTfaZNir7B6AHKJmKL8S.jpeg',
                    options: {
                        type: 'local',
                    },
                },
                {
                    source: 'http://127.0.0.1:8000/storage/images/RTfaZNir7B6AHKJmKL8S.jpeg',
                    options: {
                        type: 'local',
                    },
                }
            ],
        });
    </script>
@endsection
