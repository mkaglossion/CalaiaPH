import Alpine from 'alpinejs'

Alpine.data('bookingConfirmationCms', (initialTab) => ({
  activeTab: initialTab,
  campWaiverModal: false,
  previewOpen: false,
  previewTitle: '',
  previewUrl: '',
  previewForm: null,
  previewTab: '',
  previewRefreshTimer: null,

  extractPreviewTab(form) {
    try {
      const path = new URL(form.action, window.location.origin).pathname.split('/').filter(Boolean)
      return path[path.length - 1] || 'staycation'
    } catch (error) {
      return 'staycation'
    }
  },

  buildPreviewData(form) {
    const formData = new FormData(form)
    const tab = this.previewTab || this.extractPreviewTab(form)
    const prefix = `contents[${tab}]`
    const read = (field) => {
      const value = formData.get(`${prefix}[${field}]`)
      return typeof value === 'string' ? value.trim() : ''
    }
    const splitLines = (value) => value
      .split(/\r?\n/)
      .map((line) => line.trim())
      .filter(Boolean)

    return {
      tab,
      page: {
        eyebrow: read('page_eyebrow'),
        title: read('page_title'),
        description: read('page_description'),
      },
      nextSteps: {
        title: read('next_steps_title'),
        items: splitLines(read('next_steps')),
      },
      support: {
        title: read('support_title'),
        description: read('support_description'),
        email: read('support_email'),
        phone: read('support_phone'),
      },
      buttons: {
        home: read('home_button_label'),
        print: read('print_button_label'),
        payment: read('payment_button_label'),
      },
      campWaiver: {
        title: read('camp_waiver_title'),
        body: read('camp_waiver_body'),
        formUrl: read('camp_waiver_form_url'),
      },
    }
  },

  openPreview(url, title, form) {
    if (!form || !this.$refs.previewFrame) {
      return
    }

    this.previewUrl = url
    this.previewTitle = title
    this.previewForm = form
    this.previewTab = this.extractPreviewTab(form)
    this.previewOpen = true
    this.loadPreview()
  },

  refreshPreview(form) {
    if (!this.previewOpen || !this.previewUrl || !form) {
      return
    }

    this.previewForm = form
    this.previewTab = this.extractPreviewTab(form)
    this.loadPreview()
  },

  loadPreview() {
    if (!this.previewForm || !this.previewUrl || !this.$refs.previewFrame) {
      return
    }

    this.$refs.previewFrame.src = this.previewUrl
  },

  closePreview() {
    this.previewOpen = false
    this.previewTitle = ''
    this.previewUrl = ''
    this.previewForm = null
    this.previewTab = ''
  },
}))
