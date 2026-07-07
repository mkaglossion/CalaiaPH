/**
 * Shared Alpine.js CMS Manager Mixin
 * 
 * Reusable for ALL admin CMS pages that manage:
 * - Swiper images (upload, edit, delete, bulk delete)
 * - Text content (add, edit, delete)
 * 
 * Usage in Blade:
 *   x-data="cmsManager({
 *     images: @js($images),
 *     texts: @js($texts),
 *     imageStoreRoute: '{{ route('home-swiper-images.store') }}',
 *     imageDestroyRoute: '/admin/home-swiper-images',
 *     textStoreRoute: '{{ route('home-texts.store') }}',
 *     textDestroyRoute: '/admin/home-texts',
 *     imageIds: @js($images->pluck('id')->toArray())
 *   })"
 */

window.cmsManager = function (config = {}) {
  return {
    // ── State ──
    isLoading: false,
    showModal: false,
    showTextModal: false,
    editMode: false,
    editImageMode: false,
    currentImageId: null,
    currentImageTitle: '',
    selectedImages: [],
    images: config.images || [],
    texts: config.texts || [],
    allImageIds: config.imageIds || [],

    // ── Routes ──
    imageStoreRoute: config.imageStoreRoute || '',
    imageDestroyRoute: config.imageDestroyRoute || '',
    textStoreRoute: config.textStoreRoute || '',
    textDestroyRoute: config.textDestroyRoute || '',

    form: { id: null, title: '', content: '' },

    // ── Image Selection ──
    selectAll() {
      this.selectedImages = [...this.allImageIds];
    },

    deselectAll() {
      this.selectedImages = [];
    },

    // ── Form Reset ──
    resetForm() {
      this.form = { id: null, title: '', content: '' };
      this.editMode = false;
    },

    resetImageForm() {
      this.editImageMode = false;
      this.currentImageId = null;
      this.currentImageTitle = '';
      if (this.$refs.title) this.$refs.title.value = '';
      if (this.$refs.image) this.$refs.image.value = '';
    },

    // ── Image CRUD ──
    editImage(id, title) {
      this.currentImageId = id;
      this.currentImageTitle = title || '';
      this.editImageMode = true;
      this.showModal = true;
      this.$nextTick(() => {
        if (this.$refs.title) {
          this.$refs.title.value = this.currentImageTitle;
        }
      });
    },

    deleteImage(id) {
      const route = `${this.imageDestroyRoute}/${id}`;
      Swal.fire({
        title: 'Delete Image?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          const csrf = document.querySelector('meta[name=csrf-token]').content;
          const formData = new FormData();
          formData.append('_method', 'DELETE');
          fetch(route, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: formData
          }).then(() => {
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Image deleted successfully.',
              confirmButtonColor: '#2563eb'
            }).then(() => { window.location.reload(); });
          }).catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete image.' });
          });
        }
      });
    },

    async bulkDelete() {
      if (this.selectedImages.length === 0) return;

      Swal.fire({
        title: `Delete ${this.selectedImages.length} selected image${this.selectedImages.length !== 1 ? 's' : ''}?`,
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
      }).then(async (result) => {
        if (!result.isConfirmed) return;

        this.isLoading = true;
        const csrf = document.querySelector('meta[name=csrf-token]').content;

        try {
          const promises = this.selectedImages.map(id =>
            fetch(`${this.imageDestroyRoute}/${id}`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: '_method=DELETE'
            })
          );

          await Promise.all(promises);
          this.selectedImages = [];
          Swal.fire({
            icon: "success",
            title: "Delete Complete",
            text: "Images deleted successfully!",
            confirmButtonColor: "#2563eb"
          }).then(() => { window.location.reload(); });
        } catch (err) {
          Swal.fire({ icon: "error", title: "Error", text: "Error deleting images" });
        } finally {
          this.isLoading = false;
        }
      });
    },

    async uploadImage() {
      this.isLoading = true;
      const csrf = document.querySelector('meta[name=csrf-token]').content;
      const formData = new FormData();
      const titleInput = this.$refs.title;
      const imageInput = this.$refs.image;

      if (!imageInput || !imageInput.files || imageInput.files.length === 0) {
        if (!this.editImageMode) {
          Swal.fire({
            icon: 'warning',
            title: 'No Image Selected',
            text: 'Please select an image before uploading.'
          });
          this.isLoading = false;
          return;
        }
      }

      formData.append('title', titleInput?.value || '');
      if (imageInput && imageInput.files.length > 0) {
        formData.append('image', imageInput.files[0]);
      }

      let url = this.imageStoreRoute;
      if (this.editImageMode && this.currentImageId) {
        url = `${this.imageDestroyRoute}/${this.currentImageId}`;
        formData.append('_method', 'PUT');
      }

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: formData
        });

        const data = await res.json();

        if (res.ok) {
          Swal.fire({
            icon: "success",
            title: this.editImageMode ? "Update Complete" : "Upload Complete",
            text: data.message || (this.editImageMode ? "Image updated successfully!" : "Image uploaded successfully!"),
            confirmButtonColor: "#2563eb"
          }).then(() => {
            this.showModal = false;
            this.resetImageForm();
            window.location.reload();
          });
        } else if (data.errors) {
          Swal.fire({ icon: "error", title: "Failed", html: Object.values(data.errors).flat().join("<br>") });
        } else {
          Swal.fire({ icon: "error", title: "Failed", text: data.message || "Upload failed." });
        }
      } catch (err) {
        Swal.fire({ icon: "error", title: "Unexpected Error", text: "Something went wrong." });
      } finally {
        this.isLoading = false;
      }
    },

    // ── Text CRUD ──
    editText(id) {
      const text = this.texts.find(t => t.id === id);
      if (text) {
        this.form = {
          id: text.id,
          title: text.label ?? text.title ?? '',
          content: text.text ?? text.content ?? ''
        };
        this.editMode = true;
        this.showTextModal = true;
      }
    },

    deleteText(id) {
      const route = `${this.textDestroyRoute}/${id}`;
      Swal.fire({
        title: 'Delete Text?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          const csrf = document.querySelector('meta[name=csrf-token]').content;
          const formData = new FormData();
          formData.append('_method', 'DELETE');
          fetch(route, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: formData
          }).then(() => {
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Text deleted successfully.',
              confirmButtonColor: '#2563eb'
            }).then(() => { window.location.reload(); });
          }).catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete text.' });
          });
        }
      });
    },

    async saveText() {
      this.isLoading = true;
      const csrf = document.querySelector('meta[name=csrf-token]').content;
      const formData = new FormData();

      formData.append('title', this.form.title);
      formData.append('content', this.form.content);

      let url = this.textStoreRoute;
      if (this.editMode && this.form.id) {
        url = `${this.textDestroyRoute}/${this.form.id}`;
        formData.append('_method', 'PUT');
      }

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: formData
        });

        const data = await res.json();

        if (res.ok) {
          Swal.fire({
            icon: "success",
            title: this.editMode ? "Updated!" : "Added!",
            text: data.message || "Saved successfully!",
            confirmButtonColor: "#2563eb"
          }).then(() => {
            this.resetForm();
            this.showTextModal = false;
            window.location.reload();
          });
        } else if (data.errors) {
          Swal.fire({ icon: "warning", title: "Validation Error", html: Object.values(data.errors).flat().join("<br>") });
        }
      } catch (err) {
        Swal.fire({ icon: "error", title: "Unexpected Error", text: "Something went wrong." });
      } finally {
        this.isLoading = false;
      }
    }
  };
};
