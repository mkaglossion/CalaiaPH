@php
    $farmInputClass = 'w-full rounded-xl border border-gray-400 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20';
    $farmLabelClass = 'mb-1.5 block text-sm font-semibold text-gray-800';
@endphp

@push('styles')
<style>
    [data-farm-section-item].sortable-ghost { opacity: .35; }
    [data-farm-section-item].sortable-chosen { box-shadow: 0 18px 40px rgb(15 23 42 / .18); }
    [data-farm-section-item].sortable-drag { transform: rotate(1deg); }
</style>
@endpush

<section
    x-data="farmSectionsManager({
        sections: @js($sections),
        uploadUrl: @js(route('admin.farm.sections.upload')),
        updateUrlTemplate: @js(route('admin.farm.sections.update', ['farmPageSection' => '__KEY__'])),
        placeholder: @js(\App\Helpers\ImageHelper::placeholder()),
    })"
    x-init="init()"
    class="mt-8 min-h-screen rounded-xl bg-linear-to-b from-gray-50 to-gray-100 p-4 md:p-8"
>
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#8f5d54]">Our Farm</p>
            <h2 class="mt-1 text-3xl font-extrabold tracking-tight text-gray-900 md:text-4xl">Farm Page Sections Manager</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600">
                Edit section settings, manage content cards, and drag cards to change their public display order.
            </p>
        </div>
        <a href="{{ route('admin.farm.experiences') }}"
           class="inline-flex items-center justify-center rounded-xl bg-[#8f5d54] px-4 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-[#7a4d46] hover:shadow-lg">
            Manage Experience Cards
        </a>
    </div>

    <div class="space-y-8">
        <template x-for="section in sections" :key="section.key">
            <article class="overflow-hidden rounded-2xl border border-gray-300 bg-white/80 shadow-md backdrop-blur">
                <header class="flex flex-col gap-4 border-b border-gray-200 bg-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-xl font-bold text-gray-900" x-text="labels[section.key] || section.key"></h3>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="section.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600'"
                                x-text="section.is_active ? 'Visible' : 'Hidden'"
                            ></span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500" x-text="helpText[section.key]"></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-gray-700" x-text="section.is_active ? 'Enabled' : 'Disabled'"></span>
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="section.is_active"
                            :aria-label="`${section.is_active ? 'Disable' : 'Enable'} ${labels[section.key] || section.key}`"
                            @click="toggleSectionVisibility(section)"
                            :disabled="savingKey !== null"
                            class="relative inline-flex h-7 w-12 shrink-0 rounded-full border p-0.5 shadow-sm outline-none transition focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            :class="section.is_active ? 'border-emerald-700 bg-emerald-600' : 'border-gray-500 bg-gray-500'"
                        >
                            <span class="pointer-events-none block h-5 w-5 rounded-full bg-white shadow transition-transform duration-200" :class="section.is_active ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </header>

                <div class="space-y-6 p-5 md:p-6">
                    <template x-if="['ethical_livestock', 'growing_nature', 'farm_experiences'].includes(section.key)">
                        <div class="grid gap-5 lg:grid-cols-2">
                            <label class="block lg:col-span-2">
                                <span class="{{ $farmLabelClass }}">Section title</span>
                                <input type="text" x-model="section.title" maxlength="160" class="{{ $farmInputClass }}">
                            </label>
                            <label class="block lg:col-span-2">
                                <span class="{{ $farmLabelClass }}">Description</span>
                                <textarea x-model="section.description" rows="3" maxlength="2000" class="{{ $farmInputClass }}"></textarea>
                            </label>
                        </div>
                    </template>

                    <template x-if="section.key === 'ethical_livestock'">
                        <div class="grid gap-5 lg:grid-cols-2">
                            <div class="rounded-xl border border-gray-300 bg-gray-50 p-4">
                                <p class="{{ $farmLabelClass }}">Banner image <span class="font-normal text-gray-500">(1600 × 900)</span></p>
                                <img :src="section.image_preview_url || placeholder" alt="Banner preview" class="h-48 w-full rounded-xl border border-gray-300 bg-white object-cover">
                                <div class="mt-4 flex justify-end gap-4 border-t border-gray-200 pt-3">
                                    <label class="cursor-pointer font-semibold text-[#8f5d54] transition hover:text-[#7a4d46] hover:underline">
                                        <span x-text="uploadingFor === `${section.key}:main` ? 'Uploading...' : (section.image_url ? 'Edit' : 'Add image')"></span>
                                        <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" :disabled="uploadingFor !== null" @change="uploadSectionImage($event, section, 'main')">
                                    </label>
                                    <button x-show="section.image_url" type="button" @click="deleteSectionImage(section, 'main')" class="font-semibold text-red-600 transition hover:text-red-700 hover:underline">Delete</button>
                                </div>
                            </div>
                            <div class="rounded-xl border border-gray-300 bg-gray-50 p-4">
                                <p class="{{ $farmLabelClass }}">Checklist image <span class="font-normal text-gray-500">(1000 × 1000)</span></p>
                                <img :src="section.secondary_image_preview_url || placeholder" alt="Checklist preview" class="h-48 w-full rounded-xl border border-gray-300 bg-white object-cover">
                                <div class="mt-4 flex justify-end gap-4 border-t border-gray-200 pt-3">
                                    <label class="cursor-pointer font-semibold text-[#8f5d54] transition hover:text-[#7a4d46] hover:underline">
                                        <span x-text="uploadingFor === `${section.key}:secondary` ? 'Uploading...' : (section.secondary_image_url ? 'Edit' : 'Add image')"></span>
                                        <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" :disabled="uploadingFor !== null" @change="uploadSectionImage($event, section, 'secondary')">
                                    </label>
                                    <button x-show="section.secondary_image_url" type="button" @click="deleteSectionImage(section, 'secondary')" class="font-semibold text-red-600 transition hover:text-red-700 hover:underline">Delete</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="['ethical_livestock', 'growing_nature'].includes(section.key)">
                        <label class="block max-w-xs">
                            <span class="{{ $farmLabelClass }}" x-text="section.key === 'ethical_livestock' ? 'Checklist background' : 'Section background'"></span>
                            <div class="flex items-center gap-3">
                                <input type="color" x-model="section.settings.background_color" class="h-12 w-16 rounded-lg border border-gray-400 bg-white p-1 shadow-sm">
                                <input type="text" x-model="section.settings.background_color" maxlength="7" class="{{ $farmInputClass }} uppercase">
                            </div>
                        </label>
                    </template>

                    <template x-if="section.key === 'ethical_livestock'">
                        <div>
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="font-bold text-gray-900">Checklist items</h4>
                                    <p class="mt-1 text-xs text-gray-500">Simple checklist editor for the livestock section.</p>
                                </div>
                                <button type="button" @click="addChecklistItem(section)" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">+ Add item</button>
                            </div>
                            <div class="space-y-3">
                                <template x-for="(item, index) in section.items" :key="item.key">
                                    <div class="flex gap-2 rounded-xl border border-gray-200 bg-gray-50 p-3">
                                        <input type="text" x-model="item.text" maxlength="500" class="{{ $farmInputClass }} min-w-0 flex-1" placeholder="Checklist text">
                                        <div class="flex shrink-0 gap-1">
                                            <button type="button" @click="moveChecklistItem(section, index, -1)" :disabled="index === 0" class="h-10 w-10 rounded-lg border border-gray-300 bg-white disabled:opacity-30" title="Move up">↑</button>
                                            <button type="button" @click="moveChecklistItem(section, index, 1)" :disabled="index === section.items.length - 1" class="h-10 w-10 rounded-lg border border-gray-300 bg-white disabled:opacity-30" title="Move down">↓</button>
                                            <button type="button" @click="removeChecklistItem(section, index)" class="h-10 w-10 rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50" title="Remove">×</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="!['farm_experiences', 'ethical_livestock'].includes(section.key)">
                        <div>
                            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900" x-text="itemHeading(section.key)"></h4>
                                    <p class="text-xs text-gray-500">Drag using the handle to reorder. The new order saves automatically.</p>
                                </div>
                                <button type="button" @click="openItemModal(section)" class="inline-flex items-center justify-center rounded-xl bg-[#8f5d54] px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-[#7a4d46]">
                                    <span x-text="`+ Add ${itemSingular(section.key)}`"></span>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3" :data-farm-section-grid="section.key">
                                <template x-for="(item, index) in section.items" :key="item.key">
                                    <article data-farm-section-item :data-item-key="item.key" class="relative flex h-full flex-col rounded-2xl border border-gray-300 bg-white p-5 shadow-md transition hover:shadow-xl">
                                        <div class="absolute top-4 right-4 z-10 flex items-center gap-2">
                                            <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-full border border-gray-200 bg-white/95 px-2 text-xs font-bold text-gray-600 shadow-sm" x-text="`#${index + 1}`"></span>
                                            <button type="button" data-drag-handle class="inline-flex h-12 w-12 cursor-grab items-center justify-center rounded-full border border-gray-300 bg-white/95 text-gray-600 shadow-md transition hover:border-[#8f5d54] hover:text-[#8f5d54] active:cursor-grabbing" title="Drag to reorder" aria-label="Drag to reorder">
                                                <i class="fas fa-grip-vertical text-sm" aria-hidden="true"></i>
                                            </button>
                                        </div>

                                        <template x-if="section.key !== 'ethical_livestock'">
                                            <div class="mb-4 overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                                                <img :src="item.image_preview_url || placeholder" :alt="item.title || 'Farm content card'" class="h-44 w-full" :class="section.key === 'growing_nature' ? 'object-contain p-5' : 'object-cover'">
                                            </div>
                                        </template>
                                        <template x-if="section.key === 'ethical_livestock'">
                                            <div class="mb-4 flex h-20 items-center justify-center rounded-xl bg-[#6b4a2f] text-white">
                                                <span class="flex h-11 w-11 items-center justify-center rounded-full border-2 border-white text-lg font-bold" x-text="index + 1"></span>
                                            </div>
                                        </template>

                                        <div class="mb-4">
                                            <h5 class="text-lg font-semibold text-gray-900" x-text="itemCardTitle(section, item, index)"></h5>
                                            <p class="mt-1 line-clamp-3 text-sm leading-6 text-gray-500" x-text="itemCardDescription(section, item)"></p>
                                            <template x-if="section.key === 'product_cards' && item.meta">
                                                <p class="mt-3 text-xs font-semibold text-[#8f5d54]" x-text="item.meta"></p>
                                            </template>
                                        </div>

                                        <div class="mt-auto flex justify-end gap-4 border-t border-gray-200 pt-4">
                                            <button type="button" @click="openItemModal(section, index)" class="font-semibold text-[#8f5d54] transition hover:text-[#7a4d46] hover:underline">Edit</button>
                                            <button type="button" @click="deleteItem(section, index)" class="font-semibold text-red-600 transition hover:text-red-700 hover:underline">Delete</button>
                                        </div>
                                    </article>
                                </template>
                            </div>

                            <template x-if="section.items.length === 0">
                                <div class="rounded-2xl border-2 border-dashed border-gray-300 bg-white py-10 text-center text-sm text-gray-500">No cards yet. Add the first item above.</div>
                            </template>
                        </div>
                    </template>

                    <div class="flex items-center justify-end border-t border-gray-200 pt-5">
                        <button type="button" @click="saveSection(section)" :disabled="savingKey !== null || uploadingFor !== null"
                                class="inline-flex min-w-36 items-center justify-center rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white shadow transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50">
                            <span x-text="savingKey === section.key ? 'Saving...' : 'Save section settings'"></span>
                        </button>
                    </div>
                </div>
            </article>
        </template>
    </div>

    <div x-show="showItemModal" x-cloak x-transition.opacity class="fixed inset-0 z-70 flex items-center justify-center bg-black/55 p-4" @keydown.escape.window="closeItemModal()">
        <div class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-gray-300 bg-white shadow-2xl" @click.outside="closeItemModal()">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 md:px-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900" x-text="editingIndex === null ? `Add ${itemSingular(activeSection()?.key)}` : `Edit ${itemSingular(activeSection()?.key)}`"></h3>
                    <p class="mt-1 text-xs text-gray-500">Fields with darker borders are editable.</p>
                </div>
                <button type="button" @click="closeItemModal()" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-gray-300 text-xl text-gray-600 hover:bg-gray-100" aria-label="Close">×</button>
            </div>

            <div class="space-y-5 p-5 md:p-6">
                <template x-if="activeSection()?.key !== 'ethical_livestock'">
                    <div>
                        <p class="{{ $farmLabelClass }}">Card image</p>
                        <img :src="itemForm.image_preview_url || placeholder" alt="Card image preview" class="h-56 w-full rounded-xl border-2 border-gray-300 bg-gray-100 object-contain">
                        <p class="mt-2 text-xs text-gray-500" x-text="sizeHint(activeSection()?.key)"></p>
                        <div class="mt-4 flex justify-end gap-4 border-t border-gray-200 pt-3">
                            <label class="cursor-pointer font-semibold text-[#8f5d54] transition hover:text-[#7a4d46] hover:underline">
                                <span x-text="uploadingFor === 'item-modal' ? 'Uploading...' : (itemForm.image_url ? 'Edit image' : 'Add image')"></span>
                                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" :disabled="uploadingFor !== null" @change="uploadItemImage($event)">
                            </label>
                            <button x-show="itemForm.image_url" type="button" @click="removeItemImage()" class="font-semibold text-red-600 transition hover:text-red-700 hover:underline">Delete image</button>
                        </div>
                    </div>
                </template>

                <template x-if="activeSection()?.key === 'ethical_livestock'">
                    <label class="block">
                        <span class="{{ $farmLabelClass }}">Checklist text</span>
                        <textarea x-model="itemForm.text" rows="4" maxlength="500" class="{{ $farmInputClass }}" placeholder="Example: Pasture-raised with daily access to open fields."></textarea>
                    </label>
                </template>

                <template x-if="activeSection()?.key !== 'ethical_livestock'">
                    <div class="space-y-5">
                        <label class="block">
                            <span class="{{ $farmLabelClass }}">Title</span>
                            <input type="text" x-model="itemForm.title" maxlength="160" class="{{ $farmInputClass }}">
                        </label>
                        <label class="block">
                            <span class="{{ $farmLabelClass }}">Description</span>
                            <textarea x-model="itemForm.description" rows="4" maxlength="2000" class="{{ $farmInputClass }}"></textarea>
                        </label>
                    </div>
                </template>

                <template x-if="activeSection()?.key === 'product_cards'">
                    <label class="block">
                        <span class="{{ $farmLabelClass }}">Availability / note</span>
                        <input type="text" x-model="itemForm.meta" maxlength="160" class="{{ $farmInputClass }}" placeholder="Available seasonally">
                    </label>
                </template>

                <template x-if="activeSection()?.key === 'field_banners'">
                    <label class="block">
                        <span class="{{ $farmLabelClass }}">Text alignment</span>
                        <select x-model="itemForm.alignment" class="{{ $farmInputClass }}">
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                        </select>
                    </label>
                </template>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-5 py-4 sm:flex-row sm:justify-end md:px-6">
                <button type="button" @click="closeItemModal()" class="rounded-xl border border-gray-400 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100">Cancel</button>
                <button type="button" @click="saveItem()" :disabled="savingKey !== null || uploadingFor !== null" class="rounded-xl bg-[#8f5d54] px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-[#7a4d46] disabled:opacity-50">
                    <span x-text="savingKey ? 'Saving...' : 'Save card'"></span>
                </button>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
function farmSectionsManager(config) {
    const normalizeItem = (item = {}) => ({
        key: item.key || `item-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
        title: '', text: '', description: '', meta: '', alignment: 'left',
        image_url: null, image_public_id: null, image_preview_url: config.placeholder,
        ...item,
    });
    const normalizeSection = (section = {}) => ({
        ...section,
        items: (section.items || []).map(normalizeItem),
        settings: { background_color: '#3f6668', ...(section.settings || {}) },
        is_active: Boolean(section.is_active),
    });

    return {
        sections: (config.sections || []).map(normalizeSection),
        uploadUrl: config.uploadUrl,
        updateUrlTemplate: config.updateUrlTemplate,
        placeholder: config.placeholder,
        uploadingFor: null,
        savingKey: null,
        showItemModal: false,
        modalSectionKey: null,
        editingIndex: null,
        itemForm: normalizeItem(),
        sortables: [],
        labels: {
            ethical_livestock: 'Ethical Livestock',
            product_cards: 'Farm Product Cards',
            field_banners: 'Garden & Orchard Banners',
            growing_nature: 'Growing With Nature',
            farm_experiences: 'Farm Experiences Header',
        },
        helpText: {
            ethical_livestock: 'Banner copy, checklist cards, colors, and livestock images.',
            product_cards: 'Repeatable cards for beef, pork, lamb, and other farm products.',
            field_banners: 'Full-width image banners with left or right aligned copy.',
            growing_nature: 'Section title, background color, icons, and nature principles.',
            farm_experiences: 'Heading displayed above the separately managed experience cards.',
        },

        init() {
            this.$nextTick(() => this.initSortables());
        },

        initSortables() {
            this.sortables.forEach(instance => instance.destroy());
            this.sortables = [];
            if (typeof Sortable === 'undefined') return;

            this.$root.querySelectorAll('[data-farm-section-grid]').forEach(grid => {
                const sectionKey = grid.dataset.farmSectionGrid;
                const instance = Sortable.create(grid, {
                    animation: 180,
                    handle: '[data-drag-handle]',
                    draggable: '[data-farm-section-item]',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: async (event) => {
                        if (event.oldIndex === event.newIndex) return;
                        const section = this.sections.find(item => item.key === sectionKey);
                        if (!section) return;
                        const [movedItem] = section.items.splice(event.oldIndex, 1);
                        section.items.splice(event.newIndex, 0, movedItem);
                        await this.saveSection(section, true);
                    },
                });
                this.sortables.push(instance);
            });
        },

        activeSection() {
            return this.sections.find(section => section.key === this.modalSectionKey) || null;
        },

        itemHeading(key) {
            if (key === 'field_banners') return 'Banner Cards';
            if (key === 'growing_nature') return 'Nature Principle Cards';
            return 'Product Cards';
        },

        itemSingular(key) {
            if (key === 'ethical_livestock') return 'Checklist Item';
            if (key === 'field_banners') return 'Banner';
            if (key === 'growing_nature') return 'Nature Principle';
            return 'Product Card';
        },

        itemCardTitle(section, item, index) {
            return section.key === 'ethical_livestock' ? `Checklist Item ${index + 1}` : (item.title || `Untitled ${this.itemSingular(section.key)}`);
        },

        itemCardDescription(section, item) {
            return section.key === 'ethical_livestock' ? (item.text || 'No checklist text yet.') : (item.description || 'No description yet.');
        },

        sizeHint(key) {
            if (key === 'field_banners') return 'Recommended: 1920 × 900 px, up to 5 MB.';
            if (key === 'growing_nature') return 'Recommended: 512 × 512 px transparent PNG/WebP, up to 5 MB.';
            return 'Recommended: 1200 × 800 px, up to 5 MB.';
        },

        addChecklistItem(section) {
            section.items.push(normalizeItem({ text: '' }));
        },

        moveChecklistItem(section, index, direction) {
            const target = index + direction;
            if (target < 0 || target >= section.items.length) return;
            const [item] = section.items.splice(index, 1);
            section.items.splice(target, 0, item);
        },

        removeChecklistItem(section, index) {
            section.items.splice(index, 1);
        },

        async toggleSectionVisibility(section) {
            if (this.savingKey) return;
            const nextState = !section.is_active;
            section.is_active = nextState;
            const saved = await this.saveSection(section, true);
            if (!saved) {
                section.is_active = !nextState;
                return;
            }

            Swal.fire({
                icon: 'success',
                title: nextState ? 'Section enabled' : 'Section disabled',
                timer: 1100,
                showConfirmButton: false,
            });
        },

        openItemModal(section, index = null) {
            this.modalSectionKey = section.key;
            this.editingIndex = index;
            this.itemForm = normalizeItem(index === null ? {} : JSON.parse(JSON.stringify(section.items[index])));
            this.showItemModal = true;
        },

        closeItemModal() {
            if (this.uploadingFor) return;
            this.showItemModal = false;
            this.modalSectionKey = null;
            this.editingIndex = null;
            this.itemForm = normalizeItem();
        },

        async saveItem() {
            const section = this.activeSection();
            if (!section) return;
            const requiredValue = section.key === 'ethical_livestock' ? this.itemForm.text : this.itemForm.title;
            if (!String(requiredValue || '').trim()) {
                return Swal.fire({ icon: 'warning', title: 'Missing content', text: section.key === 'ethical_livestock' ? 'Checklist text is required.' : 'Card title is required.' });
            }

            if (this.editingIndex === null) {
                section.items.push(normalizeItem(this.itemForm));
            } else {
                section.items.splice(this.editingIndex, 1, normalizeItem(this.itemForm));
            }
            this.showItemModal = false;
            await this.saveSection(section);
            this.modalSectionKey = null;
            this.editingIndex = null;
            this.itemForm = normalizeItem();
        },

        async deleteItem(section, index) {
            const result = await Swal.fire({
                title: `Delete ${this.itemSingular(section.key)}?`,
                text: 'This removes the card from the public farm page.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
            });
            if (!result.isConfirmed) return;
            section.items.splice(index, 1);
            await this.saveSection(section);
        },

        async uploadSectionImage(event, section, target) {
            const uploaded = await this.uploadFile(event, `${section.key}:${target}`);
            if (!uploaded) return;
            if (target === 'main') {
                section.image_url = uploaded.url;
                section.image_public_id = uploaded.public_id;
                section.image_preview_url = uploaded.url;
            } else {
                section.secondary_image_url = uploaded.url;
                section.secondary_image_public_id = uploaded.public_id;
                section.secondary_image_preview_url = uploaded.url;
            }
            await this.saveSection(section);
        },

        async deleteSectionImage(section, target) {
            const result = await Swal.fire({
                title: 'Delete image?',
                text: 'The image will be removed from this public farm section.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
            });
            if (!result.isConfirmed) return;

            if (target === 'main') {
                section.image_url = null;
                section.image_public_id = null;
                section.image_preview_url = this.placeholder;
            } else {
                section.secondary_image_url = null;
                section.secondary_image_public_id = null;
                section.secondary_image_preview_url = this.placeholder;
            }
            await this.saveSection(section);
        },

        async uploadItemImage(event) {
            const uploaded = await this.uploadFile(event, 'item-modal');
            if (!uploaded) return;
            this.itemForm.image_url = uploaded.url;
            this.itemForm.image_public_id = uploaded.public_id;
            this.itemForm.image_preview_url = uploaded.url;
        },

        async removeItemImage() {
            const result = await Swal.fire({
                title: 'Delete card image?',
                text: 'The card will remain, but its image will be removed after you save.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete image',
            });
            if (!result.isConfirmed) return;
            this.itemForm.image_url = null;
            this.itemForm.image_public_id = null;
            this.itemForm.image_preview_url = this.placeholder;
        },

        async uploadFile(event, uploadKey) {
            const input = event.target;
            const file = input.files?.[0];
            if (!file) return null;
            if (file.size > 5 * 1024 * 1024) {
                input.value = '';
                await Swal.fire({ icon: 'error', title: 'Image too large', text: 'Please upload an image up to 5 MB.' });
                return null;
            }

            this.uploadingFor = uploadKey;
            const formData = new FormData();
            formData.append('image', file);
            try {
                const response = await fetch(this.uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(this.errorMessage(data, 'Image upload failed.'));
                return data.image;
            } catch (error) {
                await Swal.fire({ icon: 'error', title: 'Upload failed', text: error.message });
                return null;
            } finally {
                this.uploadingFor = null;
                input.value = '';
            }
        },

        sectionPayload(section) {
            return {
                title: section.title || null,
                description: section.description || null,
                image_url: section.image_url || null,
                image_public_id: section.image_public_id || null,
                secondary_image_url: section.secondary_image_url || null,
                secondary_image_public_id: section.secondary_image_public_id || null,
                items: section.items.map(({ image_preview_url, ...item }) => item),
                settings: section.settings || {},
                is_active: Boolean(section.is_active),
            };
        },

        async saveSection(section, quiet = false) {
            if (this.savingKey) return false;
            this.savingKey = section.key;
            try {
                const response = await fetch(this.updateUrlTemplate.replace('__KEY__', encodeURIComponent(section.key)), {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf(),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(this.sectionPayload(section)),
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(this.errorMessage(data, 'Unable to save this section.'));

                const sectionIndex = this.sections.findIndex(item => item.key === section.key);
                this.sections.splice(sectionIndex, 1, normalizeSection(data.section));
                this.$nextTick(() => this.initSortables());
                if (!quiet) {
                    await Swal.fire({ icon: 'success', title: 'Saved', text: data.message, timer: 1400, showConfirmButton: false });
                }
                return true;
            } catch (error) {
                await Swal.fire({ icon: 'error', title: 'Save failed', text: error.message });
                return false;
            } finally {
                this.savingKey = null;
            }
        },

        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },

        errorMessage(data, fallback) {
            if (data?.errors) return Object.values(data.errors).flat()[0] || fallback;
            return data?.message || fallback;
        },
    };
}
</script>
@endpush
