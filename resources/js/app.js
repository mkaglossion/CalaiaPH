import './bootstrap'

import '../css/app.css'
import './admin/dashboard.js'
import './admin/bookingConfirmation.js'
import './admin/bookingSettings.js'
import './admin/flash.js'
import './admin/seasonDurations.js'

import Alpine from 'alpinejs'
import Collapse from '@alpinejs/collapse'

Alpine.plugin(Collapse)
window.Alpine = Alpine
Alpine.start()
