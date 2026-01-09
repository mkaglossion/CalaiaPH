@extends('layouts.admin')

@section('title', 'Events')

@section('content')
<div
    x-data="eventComponent()"

>
    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Events</h2>
        <button 
            @click="showEventModal = true; resetForm()" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Event
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
                @forelse($events as $index => $act)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $act->name ?? 'Untitled' }}</td>
                    <td class="px-4 py-2">{{ $act->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button class="text-blue-600 hover:underline" @click="editEvent({{ $act->id }})">
                            Edit
                        </button>
                        <form action="{{ route('event-list.destroy', $act->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="text-red-600 hover:underline"
                                onclick="return confirm('Delete this event?')"
                            >
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">No events added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Event Modal -->
    <div 
        x-show="showEventModal" 
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        x-cloak
    >
        <div 
            @click.away="showEventModal = false" 
            class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6"
        >
            <h3 class="text-lg font-semibold mb-4" x-text="editModeEvent ? 'Edit Event' : 'Add Event'"></h3>

            <form
                id="eventForm"
                class="space-y-5"
                @submit.prevent="saveEvent()"
            >
                @csrf
                <template x-if="editModeEvent">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="form.name" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Event Name"
                        required
                    >
                </div>

                <div class="flex justify-end space-x-2">
                    <button 
                        type="button" 
                        @click="showEventModal = false; resetForm()" 
                        class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        <span x-text="editModeEvent ? 'Update' : 'Add'"></span>
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
                    <th class="px-4 py-2">Event Name</th>
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
                        <td class="px-4 py-2">{{ $img->event->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right">
                            <form action="{{ route('event-image.destroy', $img->id) }}" method="POST" class="inline">
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
                    const eventId = $refs.event.value;

                    if (!fileInput) {
                        alert('Please select an image before uploading.');
                        return;
                    }

                    if (!eventId) {
                        alert('Please select an event.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('image', fileInput);
                    formData.append('title', title);
                    formData.append('event_id', eventId);

                    const btn = $el.querySelector('button[type=submit]');
                    btn.innerText = 'Uploading...';
                    btn.disabled = true;

                    fetch('{{ route('event-image.store') }}', {
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
                            $refs.event.value = '';
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
                <div class="mb-3">
                    <label class="block font-semibold mb-1">Event</label>
                    <select x-ref="event" name="event_id" class="border rounded p-2 w-full" required>
                        <option value="">Select Event</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Title (optional)</label>
                    <input type="text" x-ref="title" name="title" class="border rounded p-2 w-full">
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Upload Image/Video</label>
                    <input type="file" x-ref="image" name="image" accept="image/*,video/*" class="border rounded p-2 w-full" required>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload</button>
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

    {{-- Text Table --}}
    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Event Name</th>
                    <th class="px-4 py-2">Label</th>
                    <th class="px-4 py-2">Text</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($texts as $index => $text)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $text->event->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->content ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ Str::limit($text->text, 60) }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <form action="{{ route('event-text.destroy', $text->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this text content?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No text content added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Text Content Modal -->
    <div 
        x-show="showTextModal" 
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        x-cloak
    >
        <div 
            @click.away="showTextModal = false" 
            class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6"
        >
            <h3 class="text-lg font-semibold mb-4" x-text="editMode ? 'Edit Text Content' : 'Add Text Content'"></h3>

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
                    <label class="block font-semibold mb-1">Event</label>
                    <select 
                        name="event_id" 
                        x-model="form.event_id" 
                        class="border rounded p-2 w-full" 
                        required
                    >
                        <option value="">Select Event</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                        @endforeach
                    </select>
                </div>

            
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Label</label>
                    <input 
                        type="text" 
                        name="label" 
                        x-model="form.label" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Optional"
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Text</label>
                    <textarea 
                        name="text" 
                        x-model="form.text" 
                        rows="4" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Enter text text..."
                        required
                    ></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button 
                        type="button" 
                        @click="showTextModal = false" 
                        class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
function eventComponent() {
    return {
        showModal: false,
        showEventModal: false,
        showTextModal: false,
        editMode: false,
        editModeEvent: false,
        texts: @js($texts),
        events: @js($events),
        form: { id: null, name: '', label: '', text: '' },

        resetForm() {
            this.form = { id: null, name: '', label: '', text: '' };
            this.editMode = false;
            this.editModeEvent = false;
        },

        editEvent(id) {
            const event = this.events.find(a => a.id === id);
            if (event) {
                this.form.id = event.id;
                this.form.name = event.name;
                this.editModeEvent = true;
                this.showEventModal = true;
            }
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (text) {
                this.form.id = text.id;
                this.form.label = text.label;
                this.form.text = text.text;
                this.form.event = text.event;
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        saveText: async function() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();

            formData.append('title', this.form.label);
            formData.append('content', this.form.text);
            formData.append('event_id', this.form.event_id); // make sure this is populated

            let url = '{{ route('event-text.store') }}';

            if (this.editMode && this.form.id) {
                url = `/admin/event-text/${this.form.id}`;
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


        saveEvent: async function() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('name', this.form.name);

            let url = '{{ route('event-list.store') }}';
            if (this.editModeEvent && this.form.id) {
                url = `/admin/event-list/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#eventForm button[type=submit]');
            btn.innerText = this.editModeEvent ? 'Updating...' : 'Adding...';
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
                    alert(data.message || (this.editModeEvent ? 'Event updated!' : 'Event added!'));
                    this.resetForm();
                    this.showEventModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch(err) {
                console.error(err);
                alert('Error submitting event.');
            } finally {
                btn.innerText = this.editModeEvent ? 'Update' : 'Add';
                btn.disabled = false;
            }
        }
    };
}
</script>
@endsection
