/**
 * Shared Alpine.js CMS Manager Mixin
 *
 * Reusable for admin CMS pages that manage:
 * - Swiper images (upload, edit, delete, bulk delete, reorder)
 * - Text content (add, edit, delete)
 */

window.cmsManager = function (config = {}) {
  return {
    isLoading: false,
    showModal: false,
    showTextModal: false,
    editMode: false,
    editImageMode: false,
    currentImageId: null,
    currentImageTitle: '',
    selectedImages: [],
    images: Array.isArray(config.images) ? config.images : [],
    texts: Array.isArray(config.texts) ? config.texts : [],

    imageStoreRoute: config.imageStoreRoute || '',
    imageDestroyRoute: config.imageDestroyRoute || '',
    imageReorderRoute: config.imageReorderRoute || '',
    textStoreRoute: config.textStoreRoute || '',
    textDestroyRoute: config.textDestroyRoute || '',

    sortableInstance: null,
    isReordering: false,
    canReorder: Boolean(config.imageReorderRoute),

    form: { id: null, title: '', content: '' },

    initialize() {
      this.images = this.sortImages(this.normalizeImages(this.images));
      this.texts = this.normalizeTexts(this.texts);
      this.$nextTick(() => this.initSortable());
    },

    normalizeImages(images) {
      return (Array.isArray(images) ? images : []).map((image) => ({
        ...image,
        id: Number(image?.id ?? 0),
        sort_order: Number(image?.sort_order ?? 0),
      })).filter((image) => image.id > 0);
    },

    normalizeTexts(texts) {
      return (Array.isArray(texts) ? texts : []).map((text) => ({
        ...text,
        id: Number(text?.id ?? 0),
      })).filter((text) => text.id > 0);
    },

    sortImages(images) {
      return [...images].sort((a, b) => {
        const aOrder = Number(a?.sort_order ?? 0);
        const bOrder = Number(b?.sort_order ?? 0);
        if (aOrder !== bOrder) {
          return aOrder - bOrder;
        }

        const aDate = new Date(a?.created_at ?? 0).getTime();
        const bDate = new Date(b?.created_at ?? 0).getTime();
        if (aDate !== bDate) {
          return aDate - bDate;
        }

        return Number(a?.id ?? 0) - Number(b?.id ?? 0);
      });
    },

    currentImageIds() {
      return this.images.map((image) => Number(image.id)).filter((id) => Number.isInteger(id) && id > 0);
    },

    selectAll() {
      this.selectedImages = this.currentImageIds();
    },

    deselectAll() {
      this.selectedImages = [];
    },

    resetForm() {
      this.form = { id: null, title: '', content: '' };
      this.editMode = false;
    },

    resetImageForm() {
      this.editImageMode = false;
      this.currentImageId = null;
      this.currentImageTitle = '';
      if (this.$refs.title) {
        this.$refs.title.value = '';
      }
      if (this.$refs.image) {
        this.$refs.image.value = '';
      }
    },

    editImage(id, title) {
      this.currentImageId = Number(id);
      this.currentImageTitle = title || '';
      this.editImageMode = true;
      this.showModal = true;
      this.$nextTick(() => {
        if (this.$refs.title) {
          this.$refs.title.value = this.currentImageTitle;
        }
      });
    },

    removeImageById(id) {
      const normalizedId = Number(id);
      this.images = this.images.filter((image) => Number(image.id) !== normalizedId);
      this.selectedImages = this.selectedImages.filter((selectedId) => Number(selectedId) !== normalizedId);
      this.$nextTick(() => this.initSortable());
    },

    removeImagesByIds(ids) {
      const idSet = new Set((Array.isArray(ids) ? ids : []).map((value) => Number(value)));
      this.images = this.images.filter((image) => !idSet.has(Number(image.id)));
      this.selectedImages = this.selectedImages.filter((selectedId) => !idSet.has(Number(selectedId)));
      this.$nextTick(() => this.initSortable());
    },

    upsertImage(image) {
      if (!image || image.id === undefined || image.id === null) {
        return;
      }

      const normalized = this.normalizeImages([image])[0];
      if (!normalized) {
        return;
      }

      const current = Array.isArray(this.images) ? [...this.images] : [];
      const index = current.findIndex((item) => Number(item.id) === Number(normalized.id));

      if (index === -1) {
        current.push(normalized);
      } else {
        current.splice(index, 1, {
          ...current[index],
          ...normalized,
        });
      }

      this.images = this.sortImages(current);
      this.$nextTick(() => this.initSortable());
    },

    upsertText(text) {
      if (!text || text.id === undefined || text.id === null) {
        return;
      }

      const normalized = this.normalizeTexts([text])[0];
      if (!normalized) {
        return;
      }

      const current = Array.isArray(this.texts) ? [...this.texts] : [];
      const index = current.findIndex((item) => Number(item.id) === Number(normalized.id));

      if (index === -1) {
        current.push(normalized);
      } else {
        current.splice(index, 1, {
          ...current[index],
          ...normalized,
        });
      }

      this.texts = current.sort((a, b) => Number(a.id) - Number(b.id));
    },

    removeTextById(id) {
      const normalizedId = Number(id);
      this.texts = this.texts.filter((text) => Number(text.id) !== normalizedId);
    },

    isVideoPath(path) {
      if (!path) {
        return false;
      }

      const normalized = String(path).trim();
      if (!normalized) {
        return false;
      }

      return /\/video\//i.test(normalized) || /\.(mp4|webm|ogg|mov|avi|wmv)$/i.test(normalized);
    },

    resolveImageSrc(path) {
      const normalized = String(path || '').trim();
      if (!normalized) {
        return '';
      }

      if (normalized.startsWith('data:')) {
        return normalized;
      }

      if (normalized.startsWith('//')) {
        return `https:${normalized}`;
      }

      if (/^https?:\/\//i.test(normalized)) {
        return normalized;
      }

      return normalized.startsWith('/') ? normalized : `/${normalized}`;
    },

    videoThumbnailSrc(path) {
      const normalized = String(path || '').trim();
      if (!normalized) {
        return '';
      }

      if (/res\.cloudinary\.com/i.test(normalized) && /\/video\/upload\//i.test(normalized)) {
        return normalized
          .replace('/video/upload/', '/video/upload/so_0,w_1280/')
          .replace(/\.(mp4|webm|ogg|mov|avi|wmv)(\?.*)?$/i, '.jpg');
      }

      return normalized;
    },

    formatDate(value) {
      if (!value) {
        return 'N/A';
      }

      const date = new Date(value);
      if (Number.isNaN(date.getTime())) {
        return 'N/A';
      }

      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      });
    },

    truncateText(value, limit = 150) {
      const text = String(value || '');
      if (text.length <= limit) {
        return text;
      }

      return `${text.slice(0, Math.max(0, limit - 3)).trimEnd()}...`;
    },

    async deleteImage(id) {
      const route = `${this.imageDestroyRoute}/${id}`;
      const result = await Swal.fire({
        title: 'Delete Image?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
      });

      if (!result.isConfirmed) {
        return;
      }

      try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        const response = await fetch(route, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
          },
          body: new URLSearchParams({ _method: 'DELETE' }),
        });

        if (!response.ok) {
          throw new Error('Failed to delete image.');
        }

        this.removeImageById(id);
        await Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'Image deleted successfully.',
          confirmButtonColor: '#2563eb',
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to delete image.',
        });
      }
    },

    async bulkDelete() {
      if (this.selectedImages.length === 0) {
        return;
      }

      const result = await Swal.fire({
        title: `Delete ${this.selectedImages.length} selected image${this.selectedImages.length !== 1 ? 's' : ''}?`,
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
      });

      if (!result.isConfirmed) {
        return;
      }

      this.isLoading = true;

      try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        const responses = await Promise.all(this.selectedImages.map((id) => fetch(`${this.imageDestroyRoute}/${id}`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
          },
          body: new URLSearchParams({ _method: 'DELETE' }),
        })));

        if (responses.some((response) => !response.ok)) {
          throw new Error('Failed to delete one or more images.');
        }

        this.removeImagesByIds(this.selectedImages);
        this.selectedImages = [];
        await Swal.fire({
          icon: 'success',
          title: 'Delete Complete',
          text: 'Images deleted successfully!',
          confirmButtonColor: '#2563eb',
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error deleting images',
        });
      } finally {
        this.isLoading = false;
      }
    },

    async uploadImage() {
      this.isLoading = true;

      try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        const formData = new FormData();
        const titleInput = this.$refs.title;
        const imageInput = this.$refs.image;
        const wasEditing = this.editImageMode;

        if ((!imageInput || !imageInput.files || imageInput.files.length === 0) && !this.editImageMode) {
          await Swal.fire({
            icon: 'warning',
            title: 'No Image Selected',
            text: 'Please select an image before uploading.',
          });
          return;
        }

        formData.append('title', titleInput?.value || '');
        if (imageInput && imageInput.files && imageInput.files.length > 0) {
          formData.append('image', imageInput.files[0]);
        }

        let url = this.imageStoreRoute;
        if (this.editImageMode && this.currentImageId) {
          url = `${this.imageDestroyRoute}/${this.currentImageId}`;
          formData.append('_method', 'PUT');
        }

        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
          },
          body: formData,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
          if (data.errors) {
            await Swal.fire({
              icon: 'error',
              title: 'Failed',
              html: Object.values(data.errors).flat().join('<br>'),
            });
            return;
          }

          throw new Error(data.message || 'Upload failed.');
        }

        const image = data.image || data.data?.image || data.item || null;
        if (image) {
          this.upsertImage(image);
        }

        this.showModal = false;
        this.resetImageForm();
        await Swal.fire({
          icon: 'success',
          title: wasEditing ? 'Update Complete' : 'Upload Complete',
          text: data.message || (wasEditing ? 'Image updated successfully!' : 'Image uploaded successfully!'),
          confirmButtonColor: '#2563eb',
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Unexpected Error',
          text: error.message || 'Something went wrong.',
        });
      } finally {
        this.isLoading = false;
      }
    },

    editText(id) {
      const text = this.texts.find((item) => Number(item.id) === Number(id));
      if (!text) {
        return;
      }

      this.form = {
        id: text.id,
        title: text.label ?? text.title ?? '',
        content: text.text ?? text.content ?? '',
      };
      this.editMode = true;
      this.showTextModal = true;
    },

    async deleteText(id) {
      const route = `${this.textDestroyRoute}/${id}`;
      const result = await Swal.fire({
        title: 'Delete Text?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
      });

      if (!result.isConfirmed) {
        return;
      }

      try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        const response = await fetch(route, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
          },
          body: new URLSearchParams({ _method: 'DELETE' }),
        });

        if (!response.ok) {
          throw new Error('Failed to delete text.');
        }

        this.removeTextById(id);
        await Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'Text deleted successfully.',
          confirmButtonColor: '#2563eb',
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Failed to delete text.',
        });
      }
    },

    async saveText() {
      this.isLoading = true;

      try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        const formData = new FormData();
        formData.append('title', this.form.title);
        formData.append('content', this.form.content);
        const wasEditing = this.editMode;

        let url = this.textStoreRoute;
        if (this.editMode && this.form.id) {
          url = `${this.textDestroyRoute}/${this.form.id}`;
          formData.append('_method', 'PUT');
        }

        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
          },
          body: formData,
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          if (data.errors) {
            await Swal.fire({
              icon: 'warning',
              title: 'Validation Error',
              html: Object.values(data.errors).flat().join('<br>'),
            });
            return;
          }

          throw new Error(data.message || 'Failed to save.');
        }

        const text = data.text || data.data?.text || data.item || null;
        if (text) {
          this.upsertText(text);
        }

        this.resetForm();
        this.showTextModal = false;
        await Swal.fire({
          icon: 'success',
          title: wasEditing ? 'Updated!' : 'Added!',
          text: data.message || 'Saved successfully!',
          confirmButtonColor: '#2563eb',
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Unexpected Error',
          text: error.message || 'Something went wrong.',
        });
      } finally {
        this.isLoading = false;
      }
    },

    initSortable() {
      if (this.sortableInstance) {
        this.sortableInstance.destroy();
        this.sortableInstance = null;
      }

      if (!this.canReorder || typeof Sortable === 'undefined' || !this.$refs.imageGrid) {
        return;
      }

      this.sortableInstance = Sortable.create(this.$refs.imageGrid, {
        animation: 180,
        handle: '[data-drag-handle]',
        draggable: '[data-cms-image-card]',
        ghostClass: 'cms-image-ghost',
        chosenClass: 'cms-image-chosen',
        dragClass: 'cms-image-drag',
        onEnd: async () => {
          const orderedIds = Array.from(
            this.$refs.imageGrid.querySelectorAll('[data-cms-image-id]')
          )
            .map((node) => Number(node.dataset.cmsImageId))
            .filter((id) => Number.isInteger(id) && id > 0);

          if (orderedIds.length !== this.images.length) {
            return;
          }

          this.applyImageOrder(orderedIds);
          await this.persistImageOrder(orderedIds);
        },
      });
    },

    applyImageOrder(orderedIds) {
      const imageMap = new Map(this.images.map((image) => [Number(image.id), image]));
      this.images = orderedIds
        .map((id, index) => {
          const image = imageMap.get(Number(id));
          if (!image) {
            return null;
          }

          return {
            ...image,
            sort_order: index + 1,
          };
        })
        .filter(Boolean);
    },

    async persistImageOrder(orderedIds) {
      if (this.isReordering) {
        return;
      }

      this.isReordering = true;
      this.sortableInstance?.option('disabled', true);

      try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        const response = await fetch(this.imageReorderRoute, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
          },
          credentials: 'same-origin',
          body: JSON.stringify({ ordered_ids: orderedIds }),
        });
        const payload = await response.json().catch(() => ({}));

        if (!response.ok || payload.success === false) {
          throw new Error(payload.message || 'Failed to save image order.');
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Order Not Saved',
          text: error.message || 'Failed to save image order.',
          confirmButtonColor: '#ef4444',
        }).then(() => {
          window.location.reload();
        });
      } finally {
        this.isReordering = false;
        this.sortableInstance?.option('disabled', false);
      }
    },
  };
};
