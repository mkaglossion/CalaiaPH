@extends('layouts.admin')

@section('title', 'Activities')

@section('content')
<h1 class="text-2xl font-bold mb-4">Day Tour Page</h1>

    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Swiper Images Management</h2>
        <button 
            @click="showModal = true" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Image
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Preview</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Uploaded</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($images as $index => $img)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">
                            <img src="{{ $img->image_path }}" alt="{{ $img->title }}" class="w-20 h-12 object-cover rounded">
                        </td>
                        <td class="px-4 py-2">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right">
                            <form action="{{ route('day-tour-swiper-images.destroy', $img->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this image?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No images uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    <div 
        x-show="showModal"
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
        x-transition
    >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <button 
                @click="showModal = false"
                class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-2xl leading-none"
            >&times;</button>

            <h3 class="text-lg font-semibold mb-4">Upload New Swiper Image</h3>

            <form
                id="swiperUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                @submit.prevent="
                    const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                    const fileInput = $refs.image.files[0];
                    const title = $refs.title.value;

                    if (!fileInput) {
                        alert('Please select an image before uploading.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('image', fileInput);
                    formData.append('title', title);

                    const btn = $el.querySelector('button[type=submit]');
                    btn.innerText = 'Uploading...';
                    btn.disabled = true;

                    fetch('{{ route('day-tour-swiper-images.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(async res => {
                        let data;
                        try {
                            data = await res.json();
                        } catch {
                            throw new Error('Invalid JSON');
                        }

                        if (res.ok) {
                            alert(data.message || 'Image uploaded successfully!');
                            $refs.image.value = '';
                            $refs.title.value = '';
                            showModal = false;
                            window.location.reload(); // Refresh to show new image
                        } else if (data.errors) {
                            const errorMessages = Object.values(data.errors).flat().join('\\n');
                            alert(errorMessages);
                        } else {
                            alert(data.message || 'Upload failed.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error uploading image.');
                    })
                    .finally(() => {
                        btn.innerText = 'Upload';
                        btn.disabled = false;
                    });
                "
            >
                @csrf
                <div class="mb-3">
                    <label class="block font-semibold mb-1">Title (optional)</label>
                    <input type="text" x-ref="title" name="title" class="border rounded p-2 w-full">
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Upload Image</label>
                    <input type="file" x-ref="image" name="image" accept="image/*" class="border rounded p-2 w-full" required>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload</button>
                </div>
            </form>
        </div>
    </div>
@endsection
