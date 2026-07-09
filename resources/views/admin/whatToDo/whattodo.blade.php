@extends('layouts.admin')

@section('title', 'What to Do Page CMS')

@section('content')
<x-admin.cms-manager
    :title="$cmsConfig['title']"
    :description="$cmsConfig['description']"
    :images="$images"
    :texts="$filteredTexts"
    :imageStoreRoute="$cmsConfig['imageStoreRoute']"
    :imageDestroyRoute="$cmsConfig['imageDestroyRoute']"
    :textStoreRoute="$cmsConfig['textStoreRoute']"
    :textDestroyRoute="$cmsConfig['textDestroyRoute']"
    :pageCopyEnabled="$cmsConfig['pageCopyEnabled']"
    :pageCopyFields="$cmsConfig['pageCopyFields']"
    :pageCopyMeta="$cmsConfig['pageCopyMeta']"
    :pageCopyStoreRoute="$cmsConfig['pageCopyStoreRoute']"
    :extraSections="view('admin.whatToDo.partials.packages', ['packages' => $packages ?? collect()])->render()"
/>

{{-- Include the package manager script (needed for the packages partial) --}}
@push('scripts')
<script>
    function packageManager() {
        return {
            isLoading: false,
            showPackageModal: false,
            isPackageEditMode: false,
            errors: [],
            packages: @js(($packages ?? collect())->all()),
            form: {
                id: null, name: '', description: '', price: '',
                duration_value: '', duration_unit: 'hours', max_guests: '',
                max_groups_per_day: '', pricing_type: 'per_pax',
                imagePreview: null, inclusionsList: [], sessions: [],
                is_active: true
            },

            defaultSessions() {
                return [
                    { name: 'Morning', start: '08:00', end: '11:00', enabled: true },
                    { name: 'Afternoon', start: '12:00', end: '15:00', enabled: true },
                    { name: 'Late Session', start: '15:30', end: '18:30', enabled: true }
                ];
            },

            parseSessionsForForm(sourceSessions) {
                const defaults = this.defaultSessions();
                if (!Array.isArray(sourceSessions) || sourceSessions.length === 0) return defaults;
                const normalized = sourceSessions.filter(s => s && s.name).map(s => ({
                    name: String(s.name || '').trim(),
                    start: String(s.start || '').trim(),
                    end: String(s.end || '').trim(),
                }));
                return defaults.map(def => {
                    const match = normalized.find(s => s.name.toLowerCase() === def.name.toLowerCase());
                    return match ? { ...def, start: match.start || def.start, end: match.end || def.end, enabled: true } : { ...def, enabled: false };
                });
            },

            resetForm() {
                this.form = { id: null, name: '', description: '', price: '', duration_value: '', duration_unit: 'hours', max_guests: '', max_groups_per_day: '', pricing_type: 'per_pax', imagePreview: null, inclusionsList: [], sessions: this.defaultSessions(), is_active: true };
                this.isPackageEditMode = false;
                this.errors = [];
                if (this.$refs.imageFile) this.$refs.imageFile.value = '';
                const input = document.getElementById('newInclusion');
                if (input) input.value = '';
            },

            addInclusion() {
                const input = document.getElementById('newInclusion');
                if (!input) return;
                const value = input.value.trim();
                if (!value || this.form.inclusionsList.includes(value)) return;
                this.form.inclusionsList.push(value);
                input.value = '';
            },

            removeInclusion(idx) {
                this.form.inclusionsList.splice(idx, 1);
            },

            editPackage(id) {
                this.errors = [];
                fetch(`/admin/packages/${id}/edit`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => res.json())
                    .then(data => {
                        this.form.id = data.id;
                        this.form.name = data.name || '';
                        this.form.description = data.description || '';
                        this.form.price = String(parseFloat(data.price) || 0);
                        this.form.duration_value = String(parseInt(data.duration_value) || parseInt(data.duration_days) || 1);
                        this.form.duration_unit = data.duration_unit || 'days';
                        this.form.max_guests = String(parseInt(data.max_guests) || 1);
                        this.form.max_groups_per_day = String(parseInt(data.max_groups_per_day) || 1);
                        this.form.pricing_type = data.pricing_type || 'per_pax';
                        this.form.imagePreview = data.image || null;
                        this.form.inclusionsList = Array.isArray(data.inclusions) ? data.inclusions : [];
                        this.form.sessions = this.parseSessionsForForm(data.sessions);
                        this.form.is_active = Boolean(data.is_active);
                        if (this.$refs.imageFile) this.$refs.imageFile.value = '';
                        this.isPackageEditMode = true;
                        this.$nextTick(() => { this.showPackageModal = true; });
                    })
                    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load package' }));
            },

            deletePackage(id, route) {
                Swal.fire({
                    title: 'Delete Package?', text: 'This action cannot be undone.', icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Delete', cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    const csrf = document.querySelector('meta[name=csrf-token]').content;
                    this.isLoading = true;
                    fetch(route, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                        .then(res => res.json())
                        .then((data) => Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message || 'Package deleted.' }).then(() => window.location.reload()))
                        .catch(() => Swal.fire({ icon: 'error', title: 'Delete Failed', text: 'Failed to delete package' }))
                        .finally(() => { this.isLoading = false; });
                });
            },

            async savePackage() {
                this.isLoading = true;
                this.errors = [];
                const csrf = document.querySelector('meta[name=csrf-token]').content;
                const formData = new FormData();
                formData.append('name', this.form.name);
                formData.append('description', this.form.description);
                formData.append('price', Number(this.form.price || 0));
                formData.append('duration_value', Number(this.form.duration_value || 1));
                formData.append('duration_unit', this.form.duration_unit || 'hours');
                formData.append('max_guests', Number(this.form.max_guests || 1));
                formData.append('max_groups_per_day', Number(this.form.max_groups_per_day || 1));
                formData.append('pricing_type', this.form.pricing_type || 'per_pax');
                formData.append('inclusions', JSON.stringify(this.form.inclusionsList || []));
                const selectedSessions = (this.form.sessions || []).filter(s => s.enabled).map(s => ({ name: s.name, start: s.start, end: s.end }));
                if (selectedSessions.length === 0) { this.errors = ['Please enable at least one session.']; this.isLoading = false; return; }
                if (selectedSessions.some(s => !s.start || !s.end)) { this.errors = ['Complete start/end times for sessions.']; this.isLoading = false; return; }
                formData.append('sessions', JSON.stringify(selectedSessions));
                formData.append('is_active', this.form.is_active ? 1 : 0);
                if (this.$refs.imageFile?.files.length > 0) formData.append('image', this.$refs.imageFile.files[0]);
                let url = '{{ route('admin.packages.store') }}';
                if (this.isPackageEditMode && this.form.id) { url = `/admin/packages/${this.form.id}`; formData.append('_method', 'PUT'); }
                try {
                    const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: formData });
                    const raw = await res.text();
                    let data = {};
                    try { data = raw ? JSON.parse(raw) : {}; } catch (_) { data = {}; }
                    if (res.ok) {
                        this.showPackageModal = false;
                        await Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: this.isPackageEditMode ? 'Package updated' : 'Package created', showConfirmButton: false, timer: 1200 });
                        window.location.reload();
                    } else if (data.errors) { this.errors = Object.values(data.errors).flat(); }
                    else { this.errors = [data.message || 'Unable to save package.']; }
                } catch (e) { this.errors = ['An unexpected error occurred.']; }
                finally { this.isLoading = false; }
            }
        };
    }
</script>
@endpush
@endsection
