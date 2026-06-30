function stripHtml(message) {
  return String(message ?? '').replace(/<br\s*\/?>/gi, '\n').replace(/<[^>]*>/g, '')
}

function showAdminFlashToast({ library = 'swal', title, text, type = 'success', html = false }) {
  if (!text) {
    return
  }

  if (library === 'toastr' && window.toastr) {
    const method = type === 'error' ? 'error' : 'success'
    toastr[method](html ? stripHtml(text) : text, title || (method === 'success' ? 'Success' : 'Error'))
    return
  }

  if (window.Swal) {
    Swal.fire({
      title: title || (type === 'success' ? 'Success!' : 'Error!'),
      icon: type,
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      ...(html ? { html: stripHtml(text).replace(/\n/g, '<br>') } : { text }),
    })
    return
  }

  if (window.toastr) {
    const method = type === 'error' ? 'error' : 'success'
    toastr[method](html ? stripHtml(text) : text, title || (method === 'success' ? 'Success' : 'Error'))
  }
}

function initAdminFlashToasts() {
  document.querySelectorAll('[data-admin-flash]').forEach((el) => {
    if (el.dataset.adminFlashShown === '1') {
      return
    }

    el.dataset.adminFlashShown = '1'
    showAdminFlashToast({
      library: el.dataset.adminFlashLibrary || 'swal',
      title: el.dataset.adminFlashTitle || '',
      text: el.dataset.adminFlashMessage || '',
      type: el.dataset.adminFlashType || 'success',
      html: el.dataset.adminFlashHtml === '1',
    })
  })
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAdminFlashToasts)
} else {
  initAdminFlashToasts()
}
