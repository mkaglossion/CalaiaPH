@extends('layouts.admin')

@section('title', 'Menus')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>
    .admin-carousel {
        height: auto;
        background: white;
    }
    .admin-carousel .swiper-slide {
        height: auto;
        display: flex;
    }
    .admin-carousel-prev, .admin-carousel-next {
        color: white;
        background: rgba(59, 130, 246, 0.8);
        border-radius: 50%;
        width: 40px !important;
        height: 40px !important;
        top: 50% !important;
        margin-top: -20px !important;
        z-index: 10;
    }
    .admin-carousel-prev:hover, .admin-carousel-next:hover {
        background: rgba(59, 130, 246, 1) !important;
    }
    .admin-carousel-prev::after, .admin-carousel-next::after {
        font-size: 14px !important;
    }
    .admin-carousel-pagination {
        position: relative !important;
        bottom: auto !important;
        margin-top: 0.75rem;
    }
    .swiper-pagination-bullet {
        background: #d1d5db;
        opacity: 1;
    }
    .swiper-pagination-bullet-active {
        background: #3b82f6 !important;
    }
    
    /* Toast notifications */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes slideOut { to { transform: translateX(400px); opacity: 0; } }
    .toast.hide { animation: slideOut 0.3s ease-out forwards; }
    .toast.success { background: #10b981; color: white; }
    .toast.error { background: #ef4444; color: white; }
    .toast.info { background: #3b82f6; color: white; }
    
    /* Undo notification */
    .undo-banner {
        position: fixed;
        bottom: 20px;
        left: 20px;
        padding: 16px 20px;
        background: #374151;
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        z-index: 9998;
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .undo-banner button {
        background: #fbbf24;
        color: #1f2937;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
    }
    .undo-banner button:hover { background: #fcd34d; }
</style>

<div
    x-data="menuComponent()"
    x-init="setupKeyboardShortcuts()"

>
    <hr class="my-6">

    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-xl font-semibold">Menus</h2>
            <p class="text-gray-500 text-sm">Manage your menus.</p>
        </div>
        <button 
            @click="showMenuModal = true; resetForm()" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Menu
        </button>
    </div>
    
    <div class="mb-6">
        <input 
            x-model="searchQuery"
            @input="filterMenus()"
            type="text" 
            placeholder="🔍 Search menus..." 
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition"
        >
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
                @forelse($menus as $index => $act)
                <tr 
                    class="border-b hover:bg-gray-50"
                    x-show="!searchQuery || '{{ ($act->name ?? '') }}'.toLowerCase().includes(searchQuery.toLowerCase())"
                    x-cloak
                >
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $act->name ?? 'Untitled' }}</td>
                    <td class="px-4 py-2">{{ $act->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button class="text-blue-600 hover:underline" @click="editMenu({{ $act->id }})">
                            Edit
                        </button>
                        <button
                            type="button"
                            class="text-red-600 hover:underline"
                            @click="deleteMenu({{ $act->id }}, '{{ $act->name ?? 'Menu' }}')"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">No menus added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Menu Modal -->
    <div 
        x-show="showMenuModal" 
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        x-cloak
    >
        <div 
            @click.away="showMenuModal = false" 
            class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6"
        >
            <h3 class="text-lg font-semibold mb-4" x-text="editModeMenu ? 'Edit Menu' : 'Add Menu'"></h3>

            <form
                id="menuForm"
                class="space-y-5"
                @submit.prevent="saveMenu()"
            >
                @csrf
                <template x-if="editModeMenu">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="form.name" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Menu Name"
                        required
                    >
                </div>

                <div class="flex justify-end space-x-2">
                    <button 
                        type="button" 
                        @click="showMenuModal = false; resetForm()" 
                        class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        <span x-text="editModeMenu ? 'Update' : 'Add'"></span>
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
        <script>
            toastr.success("{{ session('success') }}", 'Success');
        </script>
    @endif

    @if(session('error'))
        <script>
            toastr.error("{{ session('error') }}", 'Error');
        </script>
    @endif

    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Preview</th>
                    <th class="px-4 py-2">Menu Name</th>
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
                        <td class="px-4 py-2">{{ $img->menu->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <button
                                class="text-blue-600 hover:underline"
                                @click="editImage({{ $img->id }}, '{{ $img->menu_id }}', '{{ $img->title }}')"
                            >
                                Edit
                            </button>

                            <form action="{{ route('menu-image.destroy', $img->id) }}" method="POST" class="inline">
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
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">No images uploaded yet.</td>
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
                    const menuId = $refs.menu.value;

                    if (!fileInput) {
                        alert('Please select an image before uploading.');
                        return;
                    }

                    if (!menuId) {
                        alert('Please select an menu.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('image', fileInput);
                    formData.append('title', title);
                    formData.append('menu_id', menuId);

                    const btn = $el.querySelector('button[type=submit]');
                    btn.innerText = 'Uploading...';
                    btn.disabled = true;

                    fetch('{{ route('menu-image.store') }}', {
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
                            $refs.menu.value = '';
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
                    <label class="block font-semibold mb-1">Menu</label>
                    <select x-ref="menu" name="menu_id" class="border rounded p-2 w-full" required>
                        <option value="">Select Menu</option>
                        @foreach($menus as $menu)
                            <option value="{{ $menu->id }}">{{ $menu->name }}</option>
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
                    <th class="px-4 py-2">Menu Name</th>
                    <th class="px-4 py-2">Label</th>
                    <th class="px-4 py-2">Text</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($texts as $index => $text)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $text->menu->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->content ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ Str::limit($text->text, 60) }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <form action="{{ route('menu-text.destroy', $text->id) }}" method="POST" class="inline">
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
                    <label class="block font-semibold mb-1">Menu</label>
                    <select 
                        name="menu_id" 
                        x-model="form.menu_id" 
                        class="border rounded p-2 w-full" 
                        required
                    >
                        <option value="">Select Menu</option>
                        @foreach($menus as $menu)
                            <option value="{{ $menu->id }}">{{ $menu->name }}</option>
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
function menuComponent() {
    return {
        showModal: false,
        showMenuModal: false,
        showTextModal: false,
        editMode: false,
        editModeMenu: false,
        searchQuery: '',
        texts: [],
        menus: [],
        form: { id: null, name: '', label: '', text: '' },
        
        // Toast notifications
        showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <span>${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        },
        
        // Keyboard shortcuts  
        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.showMenuModal = false;
                    this.showModal = false;
                    this.showTextModal = false;
                } else if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    const activeForm = document.querySelector('form');
                    if (activeForm) activeForm.dispatchEvent(new Event('submit'));
                }
            });
        },
        
        // Search/Filter
        filterMenus() {
            // Filtering handled by x-show directives
        },
        
        // Undo for delete
        deleteMenu(id, name) {
            this.scheduleDeleteMenu(id, name);
        },
        
        scheduleDeleteMenu(id, name) {
            const banner = document.createElement('div');
            banner.className = 'undo-banner';
            let secondsLeft = 5;
            banner.innerHTML = `
                <span>Menu "${name}" marked for deletion</span>
                <button id="undo_menu_${id}">Undo (${secondsLeft}s)</button>
            `;
            document.body.appendChild(banner);
            
            const btn = banner.querySelector(`#undo_menu_${id}`);
            let timer = setInterval(() => {
                secondsLeft--;
                btn.textContent = `Undo (${secondsLeft}s)`;
                if (secondsLeft <= 0) {
                    clearInterval(timer);
                    banner.remove();
                    this.confirmDeleteMenu(id);
                }
            }, 1000);

            btn.addEventListener('click', () => {
                clearInterval(timer);
                banner.remove();
            });
        },
        
        confirmDeleteMenu(id) {
            this.submitDeleteForm('menu-list', id);
        },
        
        submitDeleteForm(route, id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/admin') }}/${route}/${id}`;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        },

        resetForm() {
            this.form = { id: null, name: '', label: '', text: '' };
            this.editMode = false;
            this.editModeMenu = false;
        },

        async editMenu(id) {
            try {
                const response = await fetch(`/admin/menu-list/${id}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!response.ok) {
                    this.showToast('Failed to load menu', 'error');
                    return;
                }
                const menu = await response.json();
                this.form.id = menu.id;
                this.form.name = menu.name;
                this.editModeMenu = true;
                this.showMenuModal = true;
            } catch (error) {
                this.showToast('Error loading menu', 'error');
            }
        },

        editText(id) {
            const text = this.texts.find(t => t.id === id);
            if (text) {
                this.form.id = text.id;
                this.form.label = text.label;
                this.form.text = text.text;
                this.form.menu = text.menu;
                this.editMode = true;
                this.showTextModal = true;
            }
        },

        saveText: async function() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();

            formData.append('title', this.form.label);
            formData.append('content', this.form.text);
            formData.append('menu_id', this.form.menu_id);

            let url = '{{ route('menu-text.store') }}';

            if (this.editMode && this.form.id) {
                url = `/admin/menu-text/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#textUploadForm button[type=submit]');
            const origText = btn.innerText;
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
                    this.showToast(data.message || (this.editMode ? 'Text updated!' : 'Text added!'), 'success');
                    this.resetForm();
                    this.showTextModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    const errorMsg = Object.values(data.errors).flat().join(', ');
                    this.showToast(errorMsg, 'error');
                } else {
                    this.showToast(data.message || 'Operation failed.', 'error');
                }
            } catch(err) {
                console.error(err);
                this.showToast('Error submitting text.', 'error');
            } finally {
                btn.innerText = origText;
                btn.disabled = false;
            }
        },


        saveMenu: async function() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('name', this.form.name);

            let url = '{{ route('menu-list.store') }}';
            if (this.editModeMenu && this.form.id) {
                url = `/admin/menu-list/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#menuForm button[type=submit]');
            const origText = btn.innerText;
            btn.innerText = this.editModeMenu ? 'Updating...' : 'Adding...';
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
                    this.showToast(data.message || (this.editModeMenu ? 'Menu updated!' : 'Menu added!'), 'success');
                    this.resetForm();
                    this.showMenuModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    const errorMsg = Object.values(data.errors).flat().join(', ');
                    this.showToast(errorMsg, 'error');
                } else {
                    this.showToast(data.message || 'Operation failed.', 'error');
                }
            } catch(err) {
                console.error(err);
                this.showToast('Error submitting menu.', 'error');
            } finally {
                btn.innerText = origText;
                btn.disabled = false;
            }
        },
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch(err) {
                console.error(err);
                alert('Error submitting menu.');
            } finally {
                btn.innerText = this.editModeMenu ? 'Update' : 'Add';
                btn.disabled = false;
            }
        }
    };
}
</script>

<script>
// Initialize carousels for swiper images
document.addEventListener('DOMContentLoaded', function() {
    @php
        $imagesByMenu = $images->groupBy('menu_id');
    @endphp
    
    @foreach($imagesByMenu as $menuId => $menuImages)
        @if($menuImages->count() > 0)
            new Swiper('.admin-carousel-menu-{{ $menuId }}', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                pagination: {
                    el: '.admin-carousel-pagination-{{ $menuId }}',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.admin-carousel-next-{{ $menuId }}',
                    prevEl: '.admin-carousel-prev-{{ $menuId }}',
                },
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                    },
                    1024: {
                        slidesPerView: 3,
                    }
                }
            });
        @endif
    @endforeach
});
</script>
@endsection
