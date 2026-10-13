import './bootstrap'

import '../css/app.css'

import Alpine from 'alpinejs'
import Collapse from '@alpinejs/collapse'

async function startApplication() {
  const path = window.location.pathname
  const modules = []

  if (path === '/admin/dashboard' || path.startsWith('/admin/dashboard/')) {
    modules.push(import('./admin/dashboard.js'))
  }

  if (document.querySelector('[data-booking-confirmation-cms]')) {
    modules.push(import('./admin/bookingConfirmation.js'))
  }

  if (document.querySelector('[data-booking-settings]')) {
    modules.push(import('./admin/bookingSettings.js'))
  }

  if (document.querySelector('[data-season-duration-manager]')) {
    modules.push(import('./admin/seasonDurations.js'))
  }

  if (document.querySelector('[data-cms-manager]')) {
    modules.push(import('./admin/cms-manager.js'))
  }

  if (modules.length > 0) {
    modules.push(import('./admin/flash.js'))
    await Promise.all(modules)
  }

  Alpine.plugin(Collapse)
  window.Alpine = Alpine
  Alpine.start()
}

startApplication()
