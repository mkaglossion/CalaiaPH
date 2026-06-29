function queuePricingDelete(form, itemType, itemName) {
  const existing = document.querySelector('.undo-banner')
  if (existing) existing.remove()

  const banner = document.createElement('div')
  banner.className = 'undo-banner'

  let secondsLeft = 4
  const safeItemType = String(itemType || 'item')
  const safeItemName = String(itemName || '').trim() || 'Untitled'

  banner.innerHTML = `
    <span>${safeItemType.charAt(0).toUpperCase() + safeItemType.slice(1)} "${safeItemName}" marked for deletion</span>
    <button type="button">Undo (${secondsLeft}s)</button>
  `

  document.body.appendChild(banner)

  const undoButton = banner.querySelector('button')
  const timer = setInterval(() => {
    secondsLeft -= 1
    if (undoButton) {
      undoButton.textContent = `Undo (${secondsLeft}s)`
    }

    if (secondsLeft <= 0) {
      clearInterval(timer)
      banner.remove()
      form.submit()
    }
  }, 1000)

  undoButton?.addEventListener('click', () => {
    clearInterval(timer)
    banner.remove()
  })

  return false
}

function escapePricingToastText(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

function showPricingToast(title, message, icon = 'success') {
  if (window.Swal) {
    Swal.fire({
      title,
      html: escapePricingToastText(message).replace(/\n/g, '<br>'),
      icon,
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
    })
  }
}

window.queuePricingDelete = queuePricingDelete
window.showPricingToast = showPricingToast
window.durationManager = (predefinedTypes = [], routes = {}) => ({
  modalOpen: false,
  editMode: false,
  selectedType: '',
  formError: '',
  submitting: false,
  predefinedTypes,
  form: {
    id: null,
    peak_type_id: '',
    start_date: '',
    end_date: '',
    name: '',
  },

  openModal() {
    this.editMode = false
    this.selectedType = ''
    this.formError = ''
    this.submitting = false
    this.form = { id: null, peak_type_id: '', start_date: '', end_date: '', name: '' }
    this.modalOpen = true
  },

  selectType(typeId) {
    this.selectedType = typeId
    this.form.peak_type_id = typeId
  },

  editPeriod(id, peakTypeId, startDate, endDate, name) {
    this.editMode = true
    this.selectedType = String(peakTypeId)
    this.formError = ''
    this.submitting = false
    this.form = {
      id,
      peak_type_id: String(peakTypeId),
      start_date: startDate,
      end_date: endDate,
      name: name || '',
    }
    this.modalOpen = true
  },

  closeModal() {
    this.modalOpen = false
    this.editMode = false
    this.selectedType = ''
    this.formError = ''
    this.submitting = false
    this.form = { id: null, peak_type_id: '', start_date: '', end_date: '', name: '' }
  },

  async submitForm() {
    if (this.submitting) return

    const isUpdate = this.editMode && this.form.id
    const url = isUpdate
      ? String(routes.update || '').replace('__ID__', this.form.id)
      : String(routes.store || '')
    const method = isUpdate ? 'PUT' : 'POST'
    this.formError = ''
    this.submitting = true

    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      const res = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
          Accept: 'application/json',
        },
        body: JSON.stringify(this.form),
        credentials: 'same-origin',
      })

      const data = await res.json()

      if (res.ok) {
        this.closeModal()
        showPricingToast('Success!', data.message || (isUpdate ? 'Duration updated successfully!' : 'Duration added successfully!'), 'success')
        window.location.reload()
      } else if (data.errors) {
        const errors = Object.values(data.errors).flat().join('\n')
        this.formError = errors || 'Operation failed.'
        showPricingToast('Error!', errors, 'error')
      } else {
        this.formError = data.message || 'Operation failed.'
        showPricingToast('Error!', this.formError, 'error')
      }
    } catch (err) {
      console.error(err)
      this.formError = 'Error submitting duration.'
      showPricingToast('Error!', this.formError, 'error')
    } finally {
      this.submitting = false
    }
  },
})
