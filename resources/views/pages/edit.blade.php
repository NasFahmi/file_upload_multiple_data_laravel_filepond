@extends('layout')
@section('title', 'Edit Product')
@section('content')
    <div class="container">
        <h1>Edit Product</h1>
        <form method="POST" action="{{ route('product.update', $data->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH') <!-- Method override for PATCH request -->
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $data->name }}" required>
            </div>
            <div class="mb-3">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" class="form-control" id="slug" name="slug" required
                    value="{{ $data->slug }}">
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" required
                    value="{{ $data->price }}">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required>{{ $data->description }}</textarea>
            </div>
            <div class="mb-3">
                <label for="photos" class="form-label">Photos</label>
                <input type="file" class="form-control" id="photos" name="photos[]" multiple>
            </div>

            <div id="previewContainer" class="row">
                <!-- Image preview will be appended here -->
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <script>
        var photos = @json($data->images); // Assuming there are existing images
        console.log(photos);
        var inputElement = document.getElementById('photos');
        var previewContainer = document.getElementById('previewContainer');
        var allPhotos = [];
        var filesToUpload = [];

        // Function to convert data URL to File object
        function dataURLtoFile(dataUrl, filename) {
            var arr = dataUrl.split(',');
            var mime = arr[0].match(/:(.*?);/)[1];
            var bstr = atob(arr[1]);
            var n = bstr.length;
            var u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }
            return new File([u8arr], filename, {
                type: mime
            });
        }

        // Function to add images to the file input
        function addImagesToInput(files) {
            // Populate the input element with the files
            var fileList = new DataTransfer();
            files.forEach(function(file) {
                fileList.items.add(file);
            });
            inputElement.files = fileList.files;
        }

        photos.forEach(function(photo) {
            const reader = new FileReader();

            // Menambahkan pratinjau gambar dari database
            const img = document.createElement('img');
            img.src = '{{ asset('storage/images/') }}/' + photo.image;
            imageUrl = '{{ asset('storage/images/') }}/' + photo.image;
            allPhotos.push(imageUrl);
            img.className = 'img-thumbnail col-md-3 mx-2 my-2';
            previewContainer.appendChild(img);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-danger mx-2 my-2';
            removeBtn.innerText = 'Remove';
            removeBtn.addEventListener('click', function() {
                var index = allPhotos.indexOf(imageUrl);
                if (index !== -1) {
                    allPhotos.splice(index, 1);
                    filesToUpload.splice(index, 1); // Remove corresponding file from filesToUpload array
                }
                previewContainer.removeChild(img);
                previewContainer.removeChild(removeBtn);
            });
            previewContainer.appendChild(removeBtn);
        });

        // Check if all files are fetched
        var loadedImages = 0;

        function checkAllFilesLoaded() {
            loadedImages++;
            if (loadedImages === allPhotos.length) {
                addImagesToInput(filesToUpload);
            }
        }

        // Fetch and process each image
        allPhotos.forEach(function(imageURL) {
            fetch(imageURL)
                .then(response => response.blob())
                .then(blob => {
                    // Convert the blob to a data URL
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        var dataUrl = event.target.result;
                        // Convert data URL to a File object
                        var file = dataURLtoFile(dataUrl, 'image product');
                        // Add the file to the array
                        filesToUpload.push(file);
                        checkAllFilesLoaded();
                    };
                    reader.readAsDataURL(blob);
                })
                .catch(error => console.error('Error fetching image:', error));
        });

        // Event listener for new file uploads
        inputElement.addEventListener('change', function(event) {
            const files = event.target.files;
            for (const file of files) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumbnail col-md-3 mx-2 my-2';
                    previewContainer.appendChild(img);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-danger mx-2 my-2';
                    removeBtn.innerText = 'Remove';
                    removeBtn.addEventListener('click', function() {
                        previewContainer.removeChild(img);
                        previewContainer.removeChild(removeBtn);
                        // Remove corresponding file from filesToUpload array
                        var index = filesToUpload.indexOf(file);
                        if (index !== -1) {
                            filesToUpload.splice(index, 1);
                        }
                    });
                    previewContainer.appendChild(removeBtn);

                    // Add the file to the array
                    filesToUpload.push(file);
                    addImagesToInput(filesToUpload);
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
