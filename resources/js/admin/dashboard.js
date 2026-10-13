import Alpine from 'alpinejs'

Alpine.data('dashboard', (initialRange = 'today', initialSalesView = 'daily', initialBookingView = 'daily') => ({
  range: initialRange,
  salesView: initialSalesView,
  bookingView: initialBookingView,
  isLoading: false,
  autoRefresh: true,
  refreshInterval: null,
  showShortcuts: false,
  shortcutUsed: false,
  keyboardEnabled: true,

  toggleAutoRefresh() {
    if (this.autoRefresh) {
      this.startAutoRefresh()
    } else {
      this.stopAutoRefresh()
    }
  },

  startAutoRefresh() {
    this.stopAutoRefresh()

    this.refreshInterval = setInterval(() => {
      if (!this.isLoading && document.visibilityState === 'visible') {
        window.location.reload()
      }
    }, 120000)
  },

  stopAutoRefresh() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval)
      this.refreshInterval = null
    }
  },

  init() {
    this.handleKeydown = (e) => {
      if (!this.keyboardEnabled || e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.contentEditable === 'true') {
        return
      }

      if (e.ctrlKey || e.metaKey) {
        let shortcutUsed = false
        switch (e.key) {
          case '1':
            e.preventDefault()
            this.setRange('today')
            shortcutUsed = true
            break
          case '2':
            e.preventDefault()
            this.setRange('7d')
            shortcutUsed = true
            break
          case '3':
            e.preventDefault()
            this.setRange('30d')
            shortcutUsed = true
            break
          case '4':
            e.preventDefault()
            this.range = 'custom'
            this.flashShortcut()
            break
        }

        if (shortcutUsed) {
          this.flashShortcut()
        }
      }
    }

    document.addEventListener('keydown', this.handleKeydown)
    this.startAutoRefresh()
  },

  destroy() {
    this.stopAutoRefresh()

    if (this.handleKeydown) {
      document.removeEventListener('keydown', this.handleKeydown)
    }
  },

  flashShortcut() {
    this.shortcutUsed = true
    setTimeout(() => {
      this.shortcutUsed = false
    }, 200)
  },

  submitFilterForm() {
    const form = this.$refs.filterForm

    if (!form) {
      return
    }

    const rangeInput = form.querySelector('select[name="range"]')
    const salesViewInput = form.querySelector('input[name="sales_view"]')
    const bookingViewInput = form.querySelector('input[name="booking_view"]')

    if (rangeInput) {
      rangeInput.value = this.range
    }

    if (salesViewInput) {
      salesViewInput.value = this.salesView
    }

    if (bookingViewInput) {
      bookingViewInput.value = this.bookingView
    }

    this.isLoading = true

    if (typeof form.requestSubmit === 'function') {
      form.requestSubmit()
      return
    }

    form.submit()
  },

  mapRangeToChartView(range) {
    switch (range) {
      case 'today':
        return 'daily'
      case '7d':
        return 'weekly'
      case '30d':
        return 'monthly'
      default:
        return this.salesView
    }
  },

  setRange(newRange) {
    this.range = newRange

    if (newRange !== 'custom') {
      const mappedView = this.mapRangeToChartView(newRange)
      this.salesView = mappedView
      this.bookingView = mappedView
      this.submitFilterForm()
    }
  },

  setSalesView(newSalesView, event = null) {
    if (event) {
      event.preventDefault()
    }

    this.salesView = newSalesView
    this.submitFilterForm()
  },

  setBookingView(newBookingView, event = null) {
    if (event) {
      event.preventDefault()
    }

    this.bookingView = newBookingView
    this.submitFilterForm()
  },
}))

Alpine.data('chartHoverCard', (chartWidth = 1, chartHeight = 1, displayMode = 'money') => ({
  chartWidth,
  chartHeight,
  displayMode,
  activePoint: null,
  tooltipX: 0,
  tooltipY: 0,

  get tooltipStyle() {
    const left = Math.max(8, Math.min(92, (Number(this.tooltipX ?? 0) / Number(this.chartWidth ?? 1)) * 100))
    const top = Math.max(12, Math.min(88, (Number(this.tooltipY ?? 0) / Number(this.chartHeight ?? 1)) * 100))

    return {
      left: `${left}%`,
      top: `${top}%`,
    }
  },

  setActive(point) {
    this.activePoint = point
    this.tooltipX = point.x
    this.tooltipY = point.y
  },

  clearActive() {
    this.activePoint = null
  },

  formatValue(value) {
    return this.displayMode === 'count'
      ? this.formatCount(value)
      : this.formatMoney(value)
  },

  formatMoney(value) {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      maximumFractionDigits: 0,
    }).format(Number(value ?? 0))
  },

  formatCount(value) {
    return new Intl.NumberFormat('en-PH').format(Number(value ?? 0))
  },
}))

Alpine.data('salesDistributionChart', (items = []) => ({
  items,
  activeIndex: null,

  get activeItem() {
    return this.activeIndex === null ? null : (this.items[this.activeIndex] ?? null)
  },

  setActive(index) {
    this.activeIndex = index
  },

  clearActive() {
    this.activeIndex = null
  },

  formatMoney(value) {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      maximumFractionDigits: 0,
    }).format(Number(value ?? 0))
  },

  formatCount(value) {
    return new Intl.NumberFormat('en-PH').format(Number(value ?? 0))
  },

  formatPercent(value) {
    return `${Number(value ?? 0).toFixed(1)}%`
  },
}))
