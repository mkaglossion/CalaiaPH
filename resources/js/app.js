import './bootstrap'

import '../css/app.css'

import Alpine from 'alpinejs'
import Collapse from '@alpinejs/collapse'

async function startApplication() {
  if (window.location.pathname.startsWith('/admin')) {
    await Promise.all([
      import('./admin/dashboard.js'),
      import('./admin/bookingConfirmation.js'),
      import('./admin/bookingSettings.js'),
      import('./admin/flash.js'),
      import('./admin/seasonDurations.js'),
      import('./admin/cms-manager.js'),
    ])
  }

  Alpine.plugin(Collapse)
  window.Alpine = Alpine
  Alpine.start()
}

startApplication()
