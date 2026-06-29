import Alpine from 'alpinejs'

Alpine.data('dashboard', (initialRange = 'today') => ({
  range: initialRange,
  isLoading: false,
  autoRefresh: false,
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
      if (!this.isLoading) {
        window.location.reload()
      }
    }, 30000)
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

  setRange(newRange) {
    this.range = newRange
    this.isLoading = true

    if (newRange !== 'custom') {
      const form = document.querySelector('form')
      const input = form.querySelector('input[name="range"]') || document.createElement('input')
      input.type = 'hidden'
      input.name = 'range'
      input.value = newRange
      if (!form.querySelector('input[name="range"]')) {
        form.appendChild(input)
      } else {
        form.querySelector('input[name="range"]').value = newRange
      }
      form.submit()
    }
  },
}))

Alpine.data('chartHoverCard', (chartWidth = 1, chartHeight = 1) => ({
  chartWidth,
  chartHeight,
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
