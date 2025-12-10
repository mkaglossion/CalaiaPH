@extends('layouts.admin')

@section('title', 'Activities')

@section('content')
<div
    x-data="activityComponent(@js($texts), @js($activities))"
    class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl"
>

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Activities
    </h1>
    <p class="text-gray-600 mb-10">Manage the activities, image slider and content displayed on your Activities page.</p>

    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Activities</h2>
        <button 
            @click="showActivityModal = true; resetForm()" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Activity
        </button>
    </div>

    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Uploaded</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $index => $act)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $act->name ?? 'Untitled' }}</td>
                    <td class="px-4 py-2">{{ $act->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button class="text-blue-600 hover:underline" @click="editActivity({{ $act->id }})">
                            Edit
                        </button>
                        <form action="{{ route('activity-list.destroy', $act->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="text-red-600 hover:underline"
                                onclick="return confirm('Delete this activity?')"
                            >
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">No activities added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Activity Modal --}}
    <div
        x-show="showActivityModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition.opacity
    >
        <div
            @click.away="showActivityModal = false"
            class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-6 border border-gray-200"
        >
            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editModeActivity ? 'Edit Activity' : 'Add Activity'"></h3>

            <form
                id="activityForm"
                class="space-y-5"
                @submit.prevent="saveActivity()"
            >
                @csrf
                <template x-if="editModeActivity">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="form.name" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Activity Name"
                        required
                    >
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button
                        type="button"
                        @click="showActivityModal = false; resetForm()"
                        class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition"
                    >
                        <span x-text="editModeActivity ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                    <th class="px-4 py-2">Activity Name</th>
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
                        <td class="px-4 py-2">{{ $img->activity->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right">
                            <form action="{{ route('activity-image.destroy', $img->id) }}" method="POST" class="inline">
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

    {{-- Swiper Modal --}}
    <div
        x-show="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        x-transition
    >
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-md p-6 relative border border-gray-200">
            <button
                @click="showModal = false"
                class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-3xl leading-none"
            >&times;</button>

            <h3 class="text-xl font-bold mb-4 text-gray-900">Upload Swiper Image</h3>

            <form
                id="swiperUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                @submit.prevent="
                    const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                    const fileInput = $refs.image.files[0];
                    const title = $refs.title.value;
                    const activityId = $refs.activity.value;

                    if (!fileInput) {
                        alert('Please select an image before uploading.');
                        return;
                    }

                    if (!activityId) {
                        alert('Please select an activity.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('image', fileInput);
                    formData.append('title', title);
                    formData.append('activity_id', activityId);

                    const btn = $el.querySelector('button[type=submit]');
                    btn.innerText = 'Uploading...';
                    btn.disabled = true;

                    fetch('{{ route('activity-image.store') }}', {
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
                        try { data = await res.json(); } catch { throw new Error('Invalid JSON'); }

                        if (res.ok) {
                            alert(data.message || 'Image uploaded successfully!');
                            $refs.image.value = '';
                            $refs.title.value = '';
                            $refs.activity.value = '';
                            showModal = false;
                            window.location.reload();
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

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Activity</label>
                    <select x-ref="activity" name="activity_id" class="border rounded-xl p-3 w-full" required>
                        <option value="">Select Activity</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Title (optional)</label>
                    <input type="text" x-ref="title" name="title" class="border rounded-xl p-3 w-full">
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Upload Image</label>
                    <input type="file" x-ref="image" name="image" accept="image/*" class="border rounded-xl p-3 w-full" required>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Text Content Management</h2>
        <button 
            @click="showTextModal = true" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Text Content
        </button>
    </div>

    {{-- ============================================================= --}}
    {{-- TEXT CONTENT SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Text Content</h2>
            <p class="text-gray-500 text-sm">Paragraphs and captions displayed on the Activities page.</p>
        </div>

        <button
            @click="showTextModal = true"
            class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            + Add Text
        </button>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <table class="w-full text-left">
            <thead class="bg-gray-100/70 backdrop-blur">
                <tr>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Activity Name</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Label</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Text</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($texts as $text)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $text->activity->name ?? 'No Activity' }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $text->title ?: 'No Label' }}
                        </td>

                        <td class="px-6 py-4 text-gray-600 text-sm">
                            {{ Str::limit($text->text, 150) }}
                        </td>

                        <td class="px-6 py-4 text-right space-x-4">
                            <button
                                class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition"
                                @click="editText({{ $text->id }})">
                                Edit
                            </button>

                            <form action="{{ route('activity-text.destroy', $text->id) }}"
                                method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button
                                    onclick="return confirm('Delete this text?')"
                                    class="text-red-600 font-semibold hover:text-red-700 hover:underline transition">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                            No text content yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- TEXT Modal --}}
    <div x-show="showTextModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" x-transition.opacity>
        <div @click.away="showTextModal = false" class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-xl w-full max-w-lg p-7 border border-gray-200">

            <h3 class="text-xl font-bold mb-4 text-gray-900" x-text="editMode ? 'Edit Text Content' : 'Add Text Content'"></h3>

            <form
                id="textUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                @submit.prevent="saveText()"
            >
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Activity</label>
                    <select 
                        name="activity_id" 
                        x-model="form.activity_id" 
                        class="border rounded p-2 w-full" 
                        required
                    >
                        <option value="">Select Activity</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </div>

            
                <div>
                    <label class="block font-medium mb-1 text-gray-700">Label</label>
                    <input
                        type="text"
                        name="label"
                        x-model="form.label"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Optional"
                    >
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700">Text</label>
                    <textarea
                        name="text"
                        x-model="form.text"
                        rows="4"
                        class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Enter text..."
                        required
                    ></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showTextModal = false" class="px-4 py-2 border rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>

                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow transition">
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
function activityComponent(texts, activities) {
    return {
        isLoading: false,
        showModal: false,
        showActivityModal: false,
        showTextModal: false,
        editMode: false,
        editModeActivity: false,
        texts,
        activities,
        form: { id: null, name: '', label: '', text: '' },

        resetForm() {
            this.form = { id: null, name: '', label: '', text: '' };
            this.editMode = false;
            this.editModeActivity = false;
        },

        editActivity(id) {
            const activity = this.activities.find(a => a.id === id);
            if (activity) {
                this.form.id = activity.id;
                this.form.name = activity.name;
                this.editModeActivity = true;
                this.showActivityModal = true;
            }
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (text) {
                this.form.id = text.id;
                this.form.label = text.label;
                this.form.text = text.text;
                this.form.activity = text.activity;
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        saveText: async function() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();

            formData.append('title', this.form.label);
            formData.append('content', this.form.text);
            formData.append('activity_id', this.form.activity_id); // make sure this is populated

            let url = "{{ route('activity-text.store') }}";

            if (this.editMode && this.form.id) {
                url = `/admin/activity-text/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#textUploadForm button[type=submit]');
            btn.innerText = this.editMode ? 'Updating...' : 'Uploading...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await res.json();

                if (res.ok) {
                    alert(data.message || (this.editMode ? 'Text updated successfully!' : 'Text added successfully!'));
                    this.resetForm();
                    this.showTextModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch(err) {
                console.error(err);
                alert('Error submitting text.');
            } finally {
                btn.innerText = this.editMode ? 'Update' : 'Add';
                btn.disabled = false;
            }
        },


        saveActivity: async function() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('name', this.form.name);

            let url = "{{ route('activity-list.store') }}";
            if (this.editModeActivity && this.form.id) {
                url = `/admin/activity-list/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#activityForm button[type=submit]');
            btn.innerText = this.editModeActivity ? 'Updating...' : 'Adding...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok) {
                    alert(data.message || (this.editModeActivity ? 'Activity updated!' : 'Activity added!'));
                    this.resetForm();
                    this.showActivityModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch(err) {
                console.error(err);
                alert('Error submitting activity.');
            } finally {
                btn.innerText = this.editModeActivity ? 'Update' : 'Add';
                btn.disabled = false;
            }
        }
    };
}
</script>
@endsection
