function showBookingSettingsToast({ title, text, icon = 'success' }) {
  if (window.Swal) {
    Swal.fire({
      title,
      text,
      icon,
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
    })
    return
  }

  if (window.toastr) {
    toastr[icon === 'error' ? 'error' : 'success'](text, title)
  }
}

function initBookingSettingsForm() {
  const form = document.getElementById('bookingSettingsForm')
  if (!form || form.dataset.adminBookingSettingsBound === '1') {
    return
  }

  form.dataset.adminBookingSettingsBound = '1'

  const mattressInput = document.getElementById('mattress_price_per_night')
  const petInput = document.getElementById('pet_price')
  const maxPetsInput = document.getElementById('max_pets_per_room')
  const saveBtn = document.getElementById('saveBtn')

  const fail = (message) => {
    showBookingSettingsToast({
      title: 'Validation Error',
      text: message,
      icon: 'error',
    })
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault()

    const mattressPrice = Number(mattressInput?.value ?? 0)
    const petPrice = Number(petInput?.value ?? 0)
    const maxPets = Number(maxPetsInput?.value ?? 0)

    if (!Number.isFinite(mattressPrice) || mattressPrice < 0) {
      fail('Extra Mattress Fee must be a positive number')
      return
    }

    if (!Number.isFinite(petPrice) || petPrice < 0) {
      fail('Pet Fee must be a positive number')
      return
    }

    if (!Number.isFinite(maxPets) || maxPets < 1 || maxPets > 10) {
      fail('Maximum Pets must be between 1 and 10')
      return
    }

    if (saveBtn) {
      saveBtn.disabled = true
      saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...'
    }

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      })

      if (!response.ok) {
        throw await response.json()
      }

      showBookingSettingsToast({
        title: 'Success!',
        text: 'Booking settings updated successfully.',
        icon: 'success',
      })

      window.location.reload()
    } catch (error) {
      if (saveBtn) {
        saveBtn.disabled = false
        saveBtn.innerHTML = 'Save Settings'
      }

      const errorMessage = error?.message || 'Failed to update settings'
      showBookingSettingsToast({
        title: 'Error!',
        text: errorMessage,
        icon: 'error',
      })
    }
  })
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initBookingSettingsForm)
} else {
  initBookingSettingsForm()
}
