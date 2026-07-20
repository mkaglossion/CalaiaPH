@props([
    'fields' => [],
    'meta' => [],
    'storeRoute' => '',
])

@php
    $transportMap = [
        'label' => 'page_label',
        'title' => 'page_title',
        'intro' => 'page_intro',
    ];
    $sectionLabels = [
        'page' => 'Page Copy',
        'cta' => 'CTA Copy',
        'messhall' => 'Mess Hall Copy',
        'cook' => 'Cook Your Own Copy',
    ];
    $sectionDescriptions = [
        'page' => 'Main heading, intro, and hero-facing copy shown at the top of the public page.',
        'cta' => 'Call-to-action heading, supporting message, button/link labels, and notes.',
        'messhall' => 'Content used on the Mess Hall dining card and related highlights.',
        'cook' => 'Content used on the Cook Your Own dining card and related highlights.',
    ];
    $resolvedCopy = $fields;
    $entries = collect($resolvedCopy)->map(function ($value, $key) use ($meta, $transportMap) {
        $fieldMeta = $meta[$key] ?? [];
        $defaultLabel = str($key)->replace('_', ' ')->title()->toString();
        $defaultType = str_contains($key, 'url') ? 'url' : 'text';
        $type = $fieldMeta['type'] ?? $defaultType;
        $section = $fieldMeta['section'] ?? match (true) {
            str_starts_with($key, 'cta_') => 'cta',
            str_starts_with($key, 'messhall_') => 'messhall',
            str_starts_with($key, 'cook_') => 'cook',
            default => 'page',
        };
        $columnClass = $fieldMeta['columnClass'] ?? match (true) {
            $section === 'cta' => 'lg:col-span-2',
            $type === 'textarea' => 'lg:col-span-2',
            $type === 'url' => 'lg:col-span-2',
            in_array($key, ['intro'], true) => 'lg:col-span-2',
            default => '',
        };

        return [
            'key' => $key,
            'transportKey' => $transportMap[$key] ?? $key,
            'value' => $value,
            'label' => $fieldMeta['label'] ?? $defaultLabel,
            'type' => $type,
            'rows' => $fieldMeta['rows'] ?? 4,
            'placeholder' => $fieldMeta['placeholder'] ?? '',
            'section' => $section,
            'columnClass' => $columnClass,
        ];
    })->values();
    $sections = $entries
        ->groupBy('section')
        ->map(function ($sectionEntries, $section) use ($sectionLabels, $sectionDescriptions) {
            return [
                'key' => $section,
                'label' => $sectionLabels[$section] ?? str($section)->replace('_', ' ')->title()->toString(),
                'description' => $sectionDescriptions[$section] ?? 'Update the copy used for this section of the public page.',
                'entries' => $sectionEntries->values(),
            ];
        })
        ->values();
@endphp

<div
    x-data="{
        pageCopy: @js($resolvedCopy),
        pageCopyEntries: @js($entries),
        pageCopySections: @js($sections),
        loading: false,
        async save() {
            if (this.loading) return;
            this.loading = true;

            try {
                const formData = new FormData();
                this.pageCopyEntries.forEach((field) => {
                    formData.append(field.transportKey, this.pageCopy[field.key] ?? '');
                });

                const response = await fetch(@js($storeRoute), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
                    throw new Error(validationMessage || data.message || 'Failed to save page copy.');
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: data.message || 'Page copy updated.',
                    confirmButtonColor: '#2563eb',
                });
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Something went wrong.' });
            } finally {
                this.loading = false;
            }
        },
    }"
    data-page-copy-manager
    class="mb-8 rounded-2xl border border-gray-200 bg-white/70 p-4 shadow-sm backdrop-blur md:p-5"
>
    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900 md:text-2xl">Page Copy</h2>
            <p class="text-sm text-gray-500">Controls the section headings and CTA copy on the public page.</p>
        </div>

        <button
            type="button"
            @click="save()"
            :disabled="loading"
            class="inline-flex items-center justify-center rounded-xl bg-[#8f5d54] px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-[#7a4d46] hover:shadow-lg disabled:opacity-60"
            x-text="loading ? 'Saving...' : 'Save Page Copy'"
        ></button>
    </div>

    <div class="space-y-4">
        <template x-for="section in pageCopySections" :key="section.key">
            <section class="rounded-xl border border-gray-200/80 bg-white/80 p-3 md:p-4" :data-page-copy-section="section.key">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-gray-500" x-text="section.label"></h3>
                    <p class="mt-1 text-xs text-gray-500" x-text="section.description"></p>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <template x-for="field in section.entries" :key="field.key">
                        <div :class="field.columnClass || ''">
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700" :for="'page-copy-' + field.key" x-text="field.label"></label>
                            <template x-if="field.type === 'textarea'">
                                <textarea
                                    :id="'page-copy-' + field.key"
                                    x-model="pageCopy[field.key]"
                                    :rows="field.rows || 4"
                                    :placeholder="field.placeholder || ''"
                                    class="w-full rounded-xl border border-gray-400 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20"
                                ></textarea>
                            </template>
                            <template x-if="field.type !== 'textarea'">
                                <input
                                    :id="'page-copy-' + field.key"
                                    :type="field.type || 'text'"
                                    x-model="pageCopy[field.key]"
                                    :placeholder="field.placeholder || ''"
                                    class="w-full rounded-xl border border-gray-400 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20"
                                >
                            </template>
                        </div>
                    </template>
                </div>
            </section>
        </template>
    </div>
</div>
