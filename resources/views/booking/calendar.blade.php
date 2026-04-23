{{-- calendar.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="CalaiaPH - Farm stays and outdoor adventures">
  <meta name="keywords" content="farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience">
  <meta name="author" content="CalaiaPH">
  @vite(['resources/js/app.js'])

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // SweetAlert always above modals/panels
    window.fireSwalOverModal = function (opts) {
      const openModal = document.querySelector('.modal.show,[role="dialog"][aria-modal="true"],.fixed.inset-0.z-\\[9999\\],.z-9999');
      let baseZ = 1055;
      if (openModal) {
        const z = Number(getComputedStyle(openModal).zIndex);
        if (!Number.isNaN(z)) baseZ = z;
      }
      return Swal.fire({
        ...opts,
        didOpen: () => {
          const c = Swal.getContainer();
          if (c) c.style.zIndex = String(baseZ + 50);
        }
      });
    };

    // Alpine.js component functions - MUST be defined before Alpine.js initializes
    function dayTourPicker({ availability, initialDate, lowThreshold, totalQuantity, maxQty, dayTours, bookingStartDate, bookingEndDate, maxPetsPerRoom, bookingType }) {
      return {
        availability: availability || {},
        selectedDate: initialDate || null,
        calendarInstance: null,
        dayTours: dayTours || [],
        bookingStartDate: bookingStartDate || null,
        bookingEndDate: bookingEndDate || null,
        bookingType: bookingType || 'daytour',
        isGuestsPanelOpen: false,
        toggleGuestsPanel() { this.isGuestsPanelOpen = !this.isGuestsPanelOpen; },
        closeGuestsPanel() { this.isGuestsPanelOpen = false; },
        roomsCount: 1,
        rooms: [{ adults: 2, children: 0 }],
        get totalAdults() { return this.rooms.reduce((sum, r) => sum + Number(r.adults || 0), 0); },
        get totalChildren() { return this.rooms.reduce((sum, r) => sum + Number(r.children || 0), 0); },
        get guestsSummary() {
          const a = this.totalAdults;
          const c = this.totalChildren;
          const adultLabel = a === 1 ? 'Adult' : 'Adults';
          const childLabel = c === 1 ? 'Child' : 'Children';
          return `${a} ${adultLabel}${c > 0 ? `, ${c} ${childLabel}` : ''}`;
        },
        get guestsSummaryShort() {
          const a = this.totalAdults;
          const c = this.totalChildren;
          const adultLabel = a === 1 ? 'Adult' : 'Adults';
          const childLabel = c === 1 ? 'Child' : 'Children';
          return `${a} ${adultLabel}${c > 0 ? ` • ${c} ${childLabel}` : ''}`;
        },
        setRoomsCount(val) {
          const next = Number(val || 1);
          if (next > 4) { fireSwalOverModal({ title:'Maximum Rooms Reached', text:'You can only add up to 4 rooms.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' }); return; }
          if (next < 1) { fireSwalOverModal({ title:'Minimum Rooms Required', text:'You need at least 1 room.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' }); return; }
          this.roomsCount = next;
          if (this.rooms.length < next) { while (this.rooms.length < next) this.rooms.push({ adults: 2, children: 0 }); }
          else if (this.rooms.length > next) { this.rooms = this.rooms.slice(0, next); }
          this.calculatePrice();
        },
        incAdults(idx) { this.rooms[idx].adults = Number(this.rooms[idx].adults || 0) + 1; this.calculatePrice(); },
        decAdults(idx) { this.rooms[idx].adults = Math.max(1, Number(this.rooms[idx].adults || 1) - 1); this.calculatePrice(); },
        incChildren(idx) { this.rooms[idx].children = Number(this.rooms[idx].children || 0) + 1; this.calculatePrice(); },
        decChildren(idx) { this.rooms[idx].children = Math.max(0, Number(this.rooms[idx].children || 0) - 1); this.calculatePrice(); },
        resetGuests() { this.roomsCount = 1; this.rooms = [{ adults: 2, children: 0 }]; this.calculatePrice(); },
        isPromoPanelOpen: false,
        promoCode: '',
        togglePromoPanel() { this.isPromoPanelOpen = !this.isPromoPanelOpen; },
        closePromoPanel() { this.isPromoPanelOpen = false; },
        cancelPromo() { this.promoCode = ''; this.isPromoPanelOpen = false; },
        applyPromo() { this.isPromoPanelOpen = false; },
        estimatedPrice: null,
        isCalculatingPrice: false,
        async calculatePrice() {
          if (!this.selectedDate) { this.estimatedPrice = null; return; }
          this.isCalculatingPrice = true;
          try {
            const response = await fetch('/api/pricing/day-tour', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ date: this.selectedDate, adults: this.totalAdults, children: this.totalChildren }) });
            if (!response.ok) throw new Error('Price calculation failed');
            const data = await response.json();
            this.estimatedPrice = data.total || null;
          } catch (e) { console.error('Price fetch error:', e); this.estimatedPrice = null; } finally { this.isCalculatingPrice = false; }
        },
        clearSelection() { this.selectedDate = null; this.estimatedPrice = null; this.initCalendar(false); },
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
        nextMonth() {
          if (this.calendarInstance) {
            const next = new Date(this.currentYear, this.currentMonth + 1, 1);
            this.calendarInstance.jumpToDate(next, true);
            this.currentMonth = next.getMonth();
            this.currentYear = next.getFullYear();
          }
        },
        prevMonth() {
          if (this.calendarInstance) {
            const prev = new Date(this.currentYear, this.currentMonth - 1, 1);
            this.calendarInstance.jumpToDate(prev, true);
            this.currentMonth = prev.getMonth();
            this.currentYear = prev.getFullYear();
          }
        },
        getMonthYearDisplay() {
          const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
          return `${monthNames[this.currentMonth]} ${this.currentYear}`;
        },
        legendMaxQty: maxQty || 0,
        legendLowThreshold: 2,
        totalQuantity: totalQuantity || 0,
        _months: 1,
        _resizeTimer: null,
        init() {
          if (initialDate) this.selectedDate = initialDate;
          this.calculateSmartThresholds();
          this._months = this.getResponsiveMonths();
          this.$nextTick(() => this.initCalendar());
          window.addEventListener('resize', () => {
            clearTimeout(this._resizeTimer);
            this._resizeTimer = setTimeout(() => {
              const next = this.getResponsiveMonths();
              if (next !== this._months) { this._months = next; this.initCalendar(true); }
            }, 200);
          });
        },
        getResponsiveMonths() { return 1; },
        get selectedDateDisplay() { return this.selectedDate ? this.formatDisplay(this.selectedDate) : ''; },
        availableThresholdText: 'Loading...',
        limitedThresholdText: 'Loading...',
        calculateSmartThresholds() {
          this.legendLowThreshold = Math.max(1, Math.floor(this.totalQuantity * 0.3));
          this.availableThresholdText = (this.legendLowThreshold + 1) + '+ slots';
          this.limitedThresholdText = '1-' + this.legendLowThreshold + ' slots';
        },
        getQty(details){
          if (!details) return null;
          const raw = details.min_quantity ?? details.quantity ?? details.slots ?? details.available ?? details.remaining ?? details.qty ?? null;
          const n = Number(raw);
          return Number.isFinite(n) ? n : null;
        },
        initCalendar(keepSelected = false) {
          if (!this.bookingStartDate || !this.bookingEndDate) return;
          const selected = keepSelected ? this.selectedDate : null;
          if (this.calendarInstance) { this.calendarInstance.destroy(); this.calendarInstance = null; }
          this.calculateSmartThresholds();
          const disabledDates = [];
          for (const [dateString, details] of Object.entries(this.availability)) {
            if (details && details.is_available === false) disabledDates.push(dateString);
            // Also disable dates with 0 quantity
            const qty = this.getQty(details);
            if (qty === 0 || (qty === null && !details)) disabledDates.push(dateString);
          }
          if (!this.$refs.calendar) return;
          const today = new Date();
          today.setHours(0,0,0,0);
          const minDate = this.parseDate(this.bookingStartDate) || today;
          const maxDate = this.parseDate(this.bookingEndDate);
          const months = this._months;
          this.calendarInstance = flatpickr(this.$refs.calendar, {
            mode: 'single', dateFormat: 'Y-m-d', inline: true, disableMobile: true, monthSelectorType: 'dropdown', showMonths: months, minDate, maxDate,
            defaultDate: selected || this.selectedDate,
            disable: [(date) => {
              const checkDate = new Date(date);
              checkDate.setHours(0,0,0,0);
              if (checkDate < today) return true;
              const dateStr = this.formatDateForCompare(date);
              const hasInventory = this.availability && Object.prototype.hasOwnProperty.call(this.availability, dateStr);
              if (!hasInventory) return true;
              return disabledDates.includes(dateStr);
            }],
            onDayCreate: (_, __, fp, dayElem) => {
              const dateString = fp.formatDate(dayElem.dateObj, 'Y-m-d');
              const existingBadge = dayElem.querySelector('.avail-badge');
              if (existingBadge) existingBadge.remove();
              dayElem.classList.remove('fp-avail-high','fp-avail-low','fp-not-avail','fp-invalid');
              dayElem.style.position = 'relative';
              dayElem.style.overflow = 'visible';
              const checkDate = new Date(dayElem.dateObj);
              checkDate.setHours(0,0,0,0);
              const isPast = checkDate < today;
              const hasInventory = this.availability && Object.prototype.hasOwnProperty.call(this.availability, dateString);
              const details = hasInventory ? this.availability[dateString] : null;
              const badge = document.createElement('span');
              badge.className = 'avail-badge';
              badge.style.cssText = 'position:absolute !important; top:-4px !important; right:-4px !important; display:flex !important; align-items:center !important; justify-content:center !important; font-size:11px !important; font-weight:600 !important; padding:0 !important; border-radius:999px !important; line-height:1 !important; width:20px !important; height:20px !important; text-align:center !important; z-index:10000 !important; pointer-events:none !important; visibility:visible !important; opacity:1 !important; box-shadow:0 2px 4px rgba(0,0,0,0.2) !important;';
              if (!hasInventory || disabledDates.includes(dateString) || isPast) {
                dayElem.classList.add('fp-invalid');
                badge.textContent = '-';
                dayElem.appendChild(badge);
                return;
              }
              const qty = this.getQty(details);
              const safeQty = (qty === null ? 0 : qty);
              if (safeQty === 0) dayElem.classList.add('fp-not-avail');
              else if (safeQty <= this.legendLowThreshold) dayElem.classList.add('fp-avail-low');
              else dayElem.classList.add('fp-avail-high');
              badge.textContent = String(safeQty);
              dayElem.appendChild(badge);
              badge.style.display = 'flex';
              if (this.selectedDate === dateString) {
                dayElem.style.outline = '3px solid #2563eb';
                dayElem.style.outlineOffset = '-3px';
              } else {
                dayElem.style.outline = 'none';
              }
            },
            onChange: (selectedDates, _, fp) => {
              this.selectedDate = (selectedDates.length === 1) ? fp.formatDate(selectedDates[0], 'Y-m-d') : null;
              if (this.selectedDate) this.calculatePrice();
              else this.estimatedPrice = null;
              this.currentMonth = fp.currentMonth;
              this.currentYear = fp.currentYear;
              this.initCalendar(true);
            },
            onReady: () => {
              const ensureFlatpickrAttrs = () => {
                document.querySelectorAll('input.numInput.cur-year:not([name])').forEach((input, idx) => {
                  if (!input.id) input.id = `fp-year-input-${Date.now()}-${idx}`;
                  if (!input.name) input.name = 'flatpickr_year';
                });
                document.querySelectorAll('input.numInput.cur-month:not([name])').forEach((input, idx) => {
                  if (!input.id) input.id = `fp-month-input-${Date.now()}-${idx}`;
                  if (!input.name) input.name = 'flatpickr_month';
                });
                document.querySelectorAll('select.flatpickr-monthDropdown-months:not([name])').forEach((select, idx) => {
                  if (!select.id) select.id = `fp-month-select-${Date.now()}-${idx}`;
                  if (!select.name) select.name = 'flatpickr_month_select';
                });
              };
              ensureFlatpickrAttrs();
              setTimeout(ensureFlatpickrAttrs, 50);
              setTimeout(ensureFlatpickrAttrs, 200);
              const observer = new MutationObserver(() => ensureFlatpickrAttrs());
              const calendarContainer = this.$refs.calendar?.closest('.flatpickr-calendar') || document.querySelector('.flatpickr-calendar');
              if (calendarContainer) observer.observe(calendarContainer, { childList: true, subtree: true });
              if (this.calendarInstance) {
                this.currentMonth = this.calendarInstance.currentMonth;
                this.currentYear = this.calendarInstance.currentYear;
              }
              this.$watch('selectedDate', () => {
                setTimeout(ensureFlatpickrAttrs, 50);
              });
            }
          });
        },
        continueBooking() {
          if (!this.selectedDate) {
            fireSwalOverModal({ title:'Please Select a Date', text:'You need to select a date for your day tour.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
            return;
          }
          // Validate that selected date has availability
          const dateData = this.availability[this.selectedDate];
          const qty = this.getQty(dateData);
          const availableQty = (qty === null ? 0 : qty);
          if (availableQty <= 0) {
            fireSwalOverModal({ title:'No Availability', text:'This date is fully booked. Please select another date.', icon:'error', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
            return;
          }
          const url = new URL(window.location.origin + '/booking/dayvisit/availability');
          url.searchParams.set('date', this.selectedDate);
          window.location.href = url.toString();
        },
        parseDate(dateStr) { const [y,m,d] = (dateStr || '').split('-').map(Number); if (!y || !m || !d) return null; return new Date(y, m - 1, d); },
        formatDisplay(dateStr) { const d = this.parseDate(dateStr); if (!d) return ''; return d.toLocaleDateString('en-US', { month:'short', day:'2-digit', year:'numeric' }); },
        formatDateForCompare(date) { const y = date.getFullYear(); const m = String(date.getMonth() + 1).padStart(2, '0'); const d = String(date.getDate()).padStart(2, '0'); return `${y}-${m}-${d}`; }
      }
    }

    function dateRangePicker({ availability, initialCheckin, initialCheckout, lowThreshold, totalQuantity, maxQty, bookingStartDate, bookingEndDate, maxPetsPerRoom, bookingType }) {
      return {
        availability: availability || {},
        checkin: null,
        checkout: null,
        calendarInstance: null,
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
        bookingStartDate: bookingStartDate || null,
        bookingEndDate: bookingEndDate || null,
        maxPetsPerRoom: maxPetsPerRoom || 2,
        bookingType: bookingType || 'staycation',
        isGuestsPanelOpen: false,
        toggleGuestsPanel() { this.isGuestsPanelOpen = !this.isGuestsPanelOpen; },
        closeGuestsPanel() { this.isGuestsPanelOpen = false; },
        nextMonth() {
          if (this.calendarInstance) {
            const next = new Date(this.currentYear, this.currentMonth + 1, 1);
            this.calendarInstance.jumpToDate(next, true);
            this.currentMonth = next.getMonth();
            this.currentYear = next.getFullYear();
          }
        },
        prevMonth() {
          if (this.calendarInstance) {
            const prev = new Date(this.currentYear, this.currentMonth - 1, 1);
            this.calendarInstance.jumpToDate(prev, true);
            this.currentMonth = prev.getMonth();
            this.currentYear = prev.getFullYear();
          }
        },
        getMonthYearDisplay() {
          const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
          return `${monthNames[this.currentMonth]} ${this.currentYear}`;
        },
        roomsCount: 1,
        rooms: [{ adults: 2, children: 0, pets: 0 }],
        get totalAdults() { return this.rooms.reduce((sum, r) => sum + Number(r.adults || 0), 0); },
        get totalChildren() { return this.rooms.reduce((sum, r) => sum + Number(r.children || 0), 0); },
        get totalPets() { return this.rooms.reduce((sum, r) => sum + Number(r.pets || 0), 0); },
        get guestsSummary() {
          const rooms = this.roomsCount;
          const a = this.totalAdults;
          const c = this.totalChildren;
          const p = this.totalPets;
          const roomLabel = rooms === 1 ? 'Room' : 'Rooms';
          const adultLabel = a === 1 ? 'Adult' : 'Adults';
          const childLabel = c === 1 ? 'Child' : 'Children';
          const petLabel = p === 1 ? 'Pet' : 'Pets';
          let summary = `${rooms} ${roomLabel}, ${a} ${adultLabel}`;
          if (c > 0) summary += `, ${c} ${childLabel}`;
          if (p > 0) summary += `, ${p} ${petLabel}`;
          return summary;
        },
        get guestsSummaryShort() {
          const r = this.roomsCount;
          const a = this.totalAdults;
          const c = this.totalChildren;
          const p = this.totalPets;
          const roomLabel = r === 1 ? 'Room' : 'Rooms';
          const adultLabel = a === 1 ? 'Adult' : 'Adults';
          const childLabel = c === 1 ? 'Child' : 'Children';
          const petLabel = p === 1 ? 'Pet' : 'Pets';
          let summary = `${r} ${roomLabel} • ${a} ${adultLabel}`;
          if (c > 0 || p > 0) summary += ` •`;
          if (c > 0) summary += ` ${c} ${childLabel}`;
          if (p > 0) summary += ` ${p} ${petLabel}`;
          return summary;
        },
        setRoomsCount(val) {
          const next = Number(val || 1);
          if (next > 4) { fireSwalOverModal({ title:'Maximum Rooms Reached', text:'You can only add up to 4 rooms.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' }); return; }
          if (next < 1) { fireSwalOverModal({ title:'Minimum Rooms Required', text:'You need at least 1 room.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' }); return; }
          this.roomsCount = next;
          if (this.rooms.length < next) { while (this.rooms.length < next) this.rooms.push({ adults: 2, children: 0 }); }
          else if (this.rooms.length > next) { this.rooms = this.rooms.slice(0, next); }
          this.calculatePrice();
        },
        incAdults(idx) { this.rooms[idx].adults = Number(this.rooms[idx].adults || 0) + 1; this.calculatePrice(); },
        decAdults(idx) { this.rooms[idx].adults = Math.max(1, Number(this.rooms[idx].adults || 1) - 1); this.calculatePrice(); },
        incChildren(idx) { this.rooms[idx].children = Number(this.rooms[idx].children || 0) + 1; this.calculatePrice(); },
        decChildren(idx) { this.rooms[idx].children = Math.max(0, Number(this.rooms[idx].children || 0) - 1); this.calculatePrice(); },
        incPets(idx) {
          const currentPets = Number(this.rooms[idx].pets || 0);
          if (currentPets >= this.maxPetsPerRoom) { fireSwalOverModal({ title: 'Pet Limit', text: `Maximum ${this.maxPetsPerRoom} pets per room`, icon: 'info', confirmButtonText: 'OK', confirmButtonColor: '#8f5d54' }); return; }
          this.rooms[idx].pets = currentPets + 1;
          this.calculatePrice();
        },
        decPets(idx) { this.rooms[idx].pets = Math.max(0, Number(this.rooms[idx].pets || 0) - 1); this.calculatePrice(); },
        resetGuests() { this.roomsCount = 1; this.rooms = [{ adults: 2, children: 0, pets: 0 }]; this.calculatePrice(); },
        isPromoPanelOpen: false,
        promoCode: '',
        togglePromoPanel() { this.isPromoPanelOpen = !this.isPromoPanelOpen; },
        closePromoPanel() { this.isPromoPanelOpen = false; },
        cancelPromo() { this.promoCode = ''; this.isPromoPanelOpen = false; },
        applyPromo() { this.isPromoPanelOpen = false; },
        estimatedPrice: null,
        isCalculatingPrice: false,
        async calculatePrice() {
          if (!this.checkin || !this.checkout) { this.estimatedPrice = null; return; }
          this.isCalculatingPrice = true;
          try {
            const response = await fetch('/api/pricing/staycation', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ checkin: this.checkin, checkout: this.checkout, rooms: this.roomsCount, adults: this.totalAdults, children: this.totalChildren, pets: this.totalPets }) });
            if (!response.ok) throw new Error('Price calculation failed');
            const data = await response.json();
            this.estimatedPrice = data.total || null;
          } catch (e) { console.error('Price fetch error:', e); this.estimatedPrice = null; } finally { this.isCalculatingPrice = false; }
        },
        clearSelection() { this.checkin = null; this.checkout = null; this.estimatedPrice = null; this.initCalendar(false); },
        legendMaxQty: maxQty || 0,
        legendLowThreshold: 2,
        totalQuantity: totalQuantity || 0,
        _months: 1,
        _resizeTimer: null,
        init() {
          if (initialCheckin) this.checkin = initialCheckin;
          if (initialCheckout) this.checkout = initialCheckout;
          this.calculateSmartThresholds();
          this._months = this.getResponsiveMonths();
          this.$nextTick(() => this.initCalendar());
          window.addEventListener('resize', () => {
            clearTimeout(this._resizeTimer);
            this._resizeTimer = setTimeout(() => {
              const next = this.getResponsiveMonths();
              if (next !== this._months) { this._months = next; this.initCalendar(true); }
            }, 200);
          });
        },
        getResponsiveMonths() { return 1; },
        get checkinDisplay() { return this.checkin ? this.formatDisplay(this.checkin) : ''; },
        get checkoutDisplay() { return this.checkout ? this.formatDisplay(this.checkout) : ''; },
        get nights() {
          if (!this.checkin || !this.checkout) return 0;
          const a = this.parseDate(this.checkin);
          const b = this.parseDate(this.checkout);
          const diff = Math.round((b - a) / (1000*60*60*24));
          return diff > 0 ? diff : 0;
        },
        get nightsText() { return this.nights ? `${this.nights} night(s)` : '—'; },
        availableThresholdText: 'Loading...',
        limitedThresholdText: 'Loading...',
        calculateSmartThresholds() {
          this.legendLowThreshold = Math.max(1, Math.floor(this.totalQuantity * 0.3));
          this.availableThresholdText = (this.legendLowThreshold + 1) + '+ slots';
          this.limitedThresholdText = '1-' + this.legendLowThreshold + ' slots';
        },
        getQty(details){
          if (!details) return null;
          const raw = details.available_quantity ?? details.min_quantity ?? details.quantity ?? details.slots ?? details.available ?? details.remaining ?? details.qty ?? null;
          const n = Number(raw);
          return Number.isFinite(n) ? n : null;
        },
        initCalendar(keepSelected = false) {
          if (!this.bookingStartDate || !this.bookingEndDate) return;
          const selected = keepSelected ? [this.checkin, this.checkout] : null;
          if (this.calendarInstance) { this.calendarInstance.destroy(); this.calendarInstance = null; }
          this.calculateSmartThresholds();
          const disabledDates = [];
          for (const [dateString, details] of Object.entries(this.availability)) {
            if (details && details.is_available === false) disabledDates.push(dateString);
            // Also disable dates with 0 quantity
            const qty = this.getQty(details);
            if (qty === 0 || (qty === null && !details)) disabledDates.push(dateString);
          }
          if (!this.$refs.calendar) return;
          const today = new Date();
          today.setHours(0,0,0,0);
          const minDate = this.parseDate(this.bookingStartDate) || today;
          const maxDate = this.parseDate(this.bookingEndDate);
          const months = this._months;
          this.calendarInstance = flatpickr(this.$refs.calendar, {
            mode: 'range', dateFormat: 'Y-m-d', inline: true, disableMobile: true, monthSelectorType: 'dropdown', showMonths: months, minDate, maxDate,
            defaultDate: selected ? selected.filter(Boolean) : [this.checkin, this.checkout].filter(Boolean),
            disable: [(date) => {
              const checkDate = new Date(date);
              checkDate.setHours(0,0,0,0);
              if (checkDate < today) return true;
              const dateStr = this.formatDateForCompare(date);
              const hasInventory = this.availability && Object.prototype.hasOwnProperty.call(this.availability, dateStr);
              if (!hasInventory) return true;
              return disabledDates.includes(dateStr);
            }],
            onDayCreate: (_, __, fp, dayElem) => {
              const dateString = fp.formatDate(dayElem.dateObj, 'Y-m-d');
              const existingBadge = dayElem.querySelector('.avail-badge');
              if (existingBadge) existingBadge.remove();
              dayElem.classList.remove('fp-avail-high','fp-avail-low','fp-not-avail','fp-invalid');
              dayElem.style.position = 'relative';
              dayElem.style.overflow = 'visible';
              const checkDate = new Date(dayElem.dateObj);
              checkDate.setHours(0,0,0,0);
              const isPast = checkDate < today;
              const hasInventory = this.availability && Object.prototype.hasOwnProperty.call(this.availability, dateString);
              const details = hasInventory ? this.availability[dateString] : null;
              const badge = document.createElement('span');
              badge.className = 'avail-badge';
              badge.style.cssText = 'position:absolute !important; top:-4px !important; right:-4px !important; display:flex !important; align-items:center !important; justify-content:center !important; font-size:11px !important; font-weight:600 !important; padding:0 !important; border-radius:999px !important; line-height:1 !important; width:20px !important; height:20px !important; text-align:center !important; z-index:10000 !important; pointer-events:none !important; visibility:visible !important; opacity:1 !important; box-shadow:0 2px 4px rgba(0,0,0,0.2) !important;';
              if (!hasInventory || disabledDates.includes(dateString) || isPast) {
                dayElem.classList.add('fp-invalid');
                badge.textContent = '-';
                dayElem.appendChild(badge);
                return;
              }
              const qty = this.getQty(details);
              const safeQty = (qty === null ? 0 : qty);
              if (safeQty === 0) dayElem.classList.add('fp-not-avail');
              else if (safeQty <= this.legendLowThreshold) dayElem.classList.add('fp-avail-low');
              else dayElem.classList.add('fp-avail-high');
              badge.textContent = String(safeQty);
              dayElem.appendChild(badge);
              badge.style.display = 'flex';
              const hasRange = !!this.checkin && !!this.checkout;
              if (hasRange) {
                if (dateString === this.checkin || dateString === this.checkout) {
                  dayElem.style.outline = '3px solid #2563eb';
                  dayElem.style.outlineOffset = '-3px';
                } else if (dateString > this.checkin && dateString < this.checkout) {
                  dayElem.style.outline = '3px solid #93c5fd';
                  dayElem.style.outlineOffset = '-3px';
                } else {
                  dayElem.style.outline = 'none';
                }
              } else {
                dayElem.style.outline = 'none';
              }
            },
            onChange: (selectedDates, _, fp) => {
              if (selectedDates.length === 2) {
                this.checkin = fp.formatDate(selectedDates[0], 'Y-m-d');
                this.checkout = fp.formatDate(selectedDates[1], 'Y-m-d');
              } else if (selectedDates.length === 1) {
                this.checkin = fp.formatDate(selectedDates[0], 'Y-m-d');
                this.checkout = null;
              } else {
                this.checkin = null;
                this.checkout = null;
              }
              if (this.checkin && this.checkout) this.calculatePrice();
              else this.estimatedPrice = null;
              this.currentMonth = fp.currentMonth;
              this.currentYear = fp.currentYear;
              this.initCalendar(true);
            },
            onReady: () => {
              const ensureFlatpickrAttrs = () => {
                document.querySelectorAll('input.numInput.cur-year:not([name])').forEach((input, idx) => {
                  if (!input.id) input.id = `fp-year-input-${Date.now()}-${idx}`;
                  if (!input.name) input.name = 'flatpickr_year';
                });
                document.querySelectorAll('input.numInput.cur-month:not([name])').forEach((input, idx) => {
                  if (!input.id) input.id = `fp-month-input-${Date.now()}-${idx}`;
                  if (!input.name) input.name = 'flatpickr_month';
                });
                document.querySelectorAll('select.flatpickr-monthDropdown-months:not([name])').forEach((select, idx) => {
                  if (!select.id) select.id = `fp-month-select-${Date.now()}-${idx}`;
                  if (!select.name) select.name = 'flatpickr_month_select';
                });
              };
              ensureFlatpickrAttrs();
              setTimeout(ensureFlatpickrAttrs, 50);
              setTimeout(ensureFlatpickrAttrs, 200);
              const observer = new MutationObserver(() => ensureFlatpickrAttrs());
              const calendarContainer = this.$refs.calendar?.closest('.flatpickr-calendar') || document.querySelector('.flatpickr-calendar');
              if (calendarContainer) observer.observe(calendarContainer, { childList: true, subtree: true });
              if (this.calendarInstance) {
                this.currentMonth = this.calendarInstance.currentMonth;
                this.currentYear = this.calendarInstance.currentYear;
              }
              this.$watch('checkin', () => {
                setTimeout(ensureFlatpickrAttrs, 50);
              });
              this.$watch('checkout', () => {
                setTimeout(ensureFlatpickrAttrs, 50);
              });
            }
          });
        },
        continueBooking() {
          if (!this.checkin || !this.checkout) {
            fireSwalOverModal({ title:'Please Select Dates', text:'You need to select both check-in and check-out dates.', icon:'warning', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
            return;
          }
          
          // Validate that all dates in range have availability
          const start = new Date(this.checkin + 'T00:00:00');
          const end = new Date(this.checkout + 'T00:00:00');
          let hasUnavailableDates = false;
          
          const fmtLocal = (d) => {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
          };
          
          let currentDate = new Date(start);
          while (currentDate < end) {
            const dateString = fmtLocal(currentDate);
            const dateData = this.availability[dateString];
            const qty = this.getQty(dateData);
            const availableQty = (qty === null ? 0 : qty);
            
            if (availableQty <= 0) {
              hasUnavailableDates = true;
              break;
            }
            currentDate.setDate(currentDate.getDate() + 1);
          }
          
          if (hasUnavailableDates) {
            fireSwalOverModal({ title:'Unavailable Dates', text:'Some dates in your selected range are fully booked. Please select different dates.', icon:'error', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
            return;
          }
          
          // Save booking session data including rooms and guests
          fetch('/booking/save-session', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              checkin: this.checkin,
              checkout: this.checkout,
              rooms_count: this.roomsCount,
              adults_total: this.totalAdults,
              children_total: this.totalChildren,
              pets_total: this.totalPets,
              rooms: this.rooms
            })
          })
          .then(response => {
            if (!response.ok) throw new Error('Failed to save session');
            // After session is saved, redirect to availability page
            const url = new URL(window.location.origin + '/booking/staycation/availability');
            url.searchParams.set('checkin', this.checkin);
            url.searchParams.set('checkout', this.checkout);
            window.location.href = url.toString();
          })
          .catch(error => {
            console.error('Session save error:', error);
            fireSwalOverModal({ title:'Error', text:'Failed to save booking data. Please try again.', icon:'error', confirmButtonText:'OK', confirmButtonColor:'#8f5d54' });
          });
        },
        parseDate(dateStr) { const [y,m,d] = (dateStr || '').split('-').map(Number); if (!y || !m || !d) return null; return new Date(y, m - 1, d); },
        formatDisplay(dateStr) { const d = this.parseDate(dateStr); if (!d) return ''; return d.toLocaleDateString('en-US', { month:'short', day:'2-digit', year:'numeric' }); },
        formatDateForCompare(date) { const y = date.getFullYear(); const m = String(date.getMonth() + 1).padStart(2, '0'); const d = String(date.getDate()).padStart(2, '0'); return `${y}-${m}-${d}`; }
      }
    }
  </script>

  <style>
    [x-cloak]{display:none!important;}
    .swal2-container { z-index: 200000 !important; }

    /* ===== availability tier colors ===== */
    .fp-avail-high { background:#d4edda !important; color:#155724 !important; }
    .fp-avail-low  { background:#fff3cd !important; color:#856404 !important; }
    .fp-not-avail  { background:#f8d7da !important; color:#721c24 !important; }
    .fp-invalid    { background:#e5e7eb !important; color:#6b7280 !important; }

    /* range highlights (staycation) */
    .fp-checkin    { background:#3b82f6 !important; color:#fff !important; }
    .fp-checkout   { background:#2563eb !important; color:#fff !important; }
    .fp-inrange    { background:#93c5fd !important; color:#0f172a !important; }

    /* ===== Responsive flatpickr centered ===== */
    .booking-calendar{
      display:flex !important;
      justify-content:center !important;
      align-items:center !important;
      width:100% !important;
      margin:0 auto !important;
      padding:0 !important;
      overflow-x:visible !important;
      overflow-y:visible !important;
    }

    .booking-calendar .flatpickr-calendar{
      display:block !important;
      position:relative !important;
      margin:0 auto !important;
      padding:0 !important;
      width:fit-content !important;
      max-width:none !important;
      box-shadow:none !important;
      border:none !important;
      left:auto !important;
      top:auto !important;
      transform:none !important;
      background:transparent !important;
      font-family:inherit !important;
      overflow-x:visible !important;
      overflow-y:visible !important;
    }

    .booking-calendar .flatpickr-calendar.inline{
      display:flex !important;
      justify-content:center !important;
      width:auto !important;
    }

    .booking-calendar .flatpickr-rContainer{
      display:block !important;
      margin:0 auto !important;
      overflow-x:visible !important;
      overflow-y:visible !important;
      width:fit-content !important;
    }

    .booking-calendar .flatpickr-innerContainer{
      display:block !important;
      margin:0 auto !important;
      padding:0 !important;
      overflow-x:visible !important;
      overflow-y:visible !important;
      width:fit-content !important;
    }

    .booking-calendar .flatpickr-months{
      width:fit-content !important;
      display:block !important;
      margin:0 auto !important;
      padding:0 !important;
      background:transparent !important;
      border-radius:0 !important;
      border:none !important;
    }

    .booking-calendar .flatpickr-month{ width:100% !important; margin:0 !important; }

    .booking-calendar .flatpickr-prev-month,
    .booking-calendar .flatpickr-next-month{
      fill:#1a1a1a !important;
      padding:10px !important;
    }

     /* day cells */
      .booking-calendar .flatpickr-day{
       border:1px solid #e5e7eb !important;
       margin:4px !important;
       width:calc(14.2857% - 8px) !important;
       max-width:none !important;
       min-height:80px !important;
       display:flex !important;
       flex-direction: column !important;
       align-items:center !important;
       justify-content:flex-start !important;
       padding-top:10px !important;
       font-size:24px !important; /* Increased date size */
       font-weight:700 !important;
       position:relative !important;
       text-align:center !important;
       transition:all .15s ease !important;
       border-radius:12px !important;
       overflow: visible !important; /* IMPORTANT: badge visibility */
       z-index: 1 !important;       /* IMPORTANT */
       background:rgba(255,255,255,0.7) !important;
     }

    .booking-calendar .flatpickr-day:hover:not(.flatpickr-disabled):not(.fp-invalid){
      transform: translateY(-1px) !important;
      box-shadow:0 6px 18px rgba(0,0,0,.12) !important;
      z-index:10 !important;
    }

     /* ===== Quantity badge: VERY visible ===== */
      /* Make sure each day is relative so we can position overlays */
.booking-calendar .flatpickr-day{
  position: relative !important;
  overflow: visible !important;
}

/* GREEN circle background (like screenshot) */
.booking-calendar .flatpickr-day.is-available::before{
  content:"";
  position:absolute;
  inset: 6px;                 /* controls circle size */
  border-radius: 999px;
  background: rgba(34,197,94,.18); /* green tint */
  z-index: 1;
}

/* Day number should stay above the circle */
.booking-calendar .flatpickr-day.is-available{
  color: #111827 !important;
}
.booking-calendar .flatpickr-day.is-available .flatpickr-day-number,
.booking-calendar .flatpickr-day.is-available{
  z-index: 2;
}

/* Blue ring on selected day (like screenshot) */
.booking-calendar .flatpickr-day.selected,
.booking-calendar .flatpickr-day.startRange,
.booking-calendar .flatpickr-day.endRange{
  background: transparent !important;
  border: 2px solid #2563eb !important; /* blue ring */
  border-radius: 999px !important;
  color: #111827 !important;
  z-index: 3;
}

/* Optional: keep range days with subtle fill */
.booking-calendar .flatpickr-day.inRange::before{
  content:"";
  position:absolute;
  inset: 6px;
  border-radius: 999px;
  background: rgba(37,99,235,.10);
  z-index: 1;
}

/* --- Badge: top-right number (your style, tuned to screenshot) --- */
    /* Flat picker day styling */
    .booking-calendar .flatpickr-day {
      border-radius: 8px !important;
      padding: 6px !important;
      margin: 3px !important;
      width: calc(14.2857% - 8px) !important;
      height: 50px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-weight: 600 !important;
      font-size: 14px !important;
      cursor: pointer !important;
      transition: all 0.2s ease !important;
      background: #f3f4f6 !important;
      color: #6b7280 !important;
      border: 1px solid transparent !important;
    }

    /* Available dates - high availability (green) */
    .booking-calendar .fp-avail-high {
      background: #dcfce7 !important;
      color: #166534 !important;
      border: 1px solid #bbf7d0 !important;
      font-weight: 700 !important;
    }

    .booking-calendar .fp-avail-high:hover {
      background: #bbf7d0 !important;
      box-shadow: 0 4px 8px rgba(16, 185, 129, 0.2) !important;
      transform: translateY(-2px) !important;
    }

    /* Limited availability (amber) */
    .booking-calendar .fp-avail-low {
      background: #fef3c7 !important;
      color: #92400e !important;
      border: 1px solid #fcd34d !important;
      font-weight: 700 !important;
    }

    .booking-calendar .fp-avail-low:hover {
      background: #fcd34d !important;
      box-shadow: 0 4px 8px rgba(245, 158, 11, 0.2) !important;
      transform: translateY(-2px) !important;
    }

    /* Not available (gray) */
    .booking-calendar .fp-not-avail {
      background: #e5e7eb !important;
      color: #9ca3af !important;
      cursor: not-allowed !important;
    }

    /* Invalid dates (past, etc) */
    .booking-calendar .fp-invalid {
      background: #f3f4f6 !important;
      color: #d1d5db !important;
      cursor: not-allowed !important;
    }

    /* Selected date - highlight with brand color */
    .booking-calendar .flatpickr-day.selected,
    .booking-calendar .flatpickr-day.startRange,
    .booking-calendar .flatpickr-day.endRange {
      background: #8f5d54 !important;
      color: #ffffff !important;
      border: 1px solid #7a4e48 !important;
      font-weight: 700 !important;
      box-shadow: 0 4px 12px rgba(143, 93, 84, 0.3) !important;
    }

    .booking-calendar .flatpickr-day.selected:hover,
    .booking-calendar .flatpickr-day.startRange:hover,
    .booking-calendar .flatpickr-day.endRange:hover {
      box-shadow: 0 6px 16px rgba(143, 93, 84, 0.4) !important;
      transform: translateY(-2px) !important;
    }

    /* Range days - light highlight */
    .booking-calendar .flatpickr-day.inRange {
      background: #fef3c7 !important;
      color: #92400e !important;
      border: none !important;
    }

    .booking-calendar .flatpickr-days{ width:fit-content !important; display:block !important; margin:0 auto !important; }
    .booking-calendar .dayContainer{
      width:auto !important;
      min-width:auto !important;
      max-width:none !important;
      margin:0 !important;
      display:flex !important;
      flex-wrap:wrap !important;
      overflow: visible !important; /* IMPORTANT */
    }

    .booking-calendar .flatpickr-monthDropdown-months,
    .booking-calendar .flatpickr-current-month{
      background:transparent !important;
      color:#111827 !important;
      font-weight:800 !important;
      font-size:16px !important;
      padding:8px 0 !important;
    }

    .booking-calendar .flatpickr-weekdays{
      background:transparent !important;
      color:#6b7280 !important;
      font-weight:600 !important;
      padding:8px 0 !important;
      width:100% !important;
    }

    .booking-calendar .flatpickr-weekday{
      color:#6b7280 !important;
      font-weight:700 !important;
      width:14.2857% !important;
      display:flex !important;
      align-items:center !important;
      justify-content:center !important;
      padding:8px 0 !important;
      font-size:12px !important;
    }


     @media (max-width: 767px){
       .booking-calendar .flatpickr-months{ 
         width:auto !important; 
         flex:0 0 auto !important;
         min-width:320px !important;
         max-width:360px !important; 
         padding:12px !important; 
         background:rgba(255,255,255,0.6) !important; 
         border-radius:12px !important; 
         border:1px solid rgba(0,0,0,0.05) !important; 
       }
       .booking-calendar .flatpickr-months{ 
         width:auto !important; 
         flex:0 0 auto !important;
         min-width:320px !important;
         max-width:360px !important; 
         padding:12px !important; 
         background:rgba(255,255,255,0.6) !important; 
         border-radius:12px !important; 
         border:1px solid rgba(0,0,0,0.05) !important; 
       }
       .booking-calendar .flatpickr-day{ 
         min-height:65px !important; 
         font-size:16px !important; 
         border-radius:8px !important; 
         padding-top:6px !important; 
         margin:2px !important; 
         width:calc(14.2857% - 4px) !important; 
         background:rgba(255,255,255,0.7) !important;
       }
       .booking-calendar .flatpickr-day .avail-badge{ 
         font-size:9px !important; 
         width:16px !important;
         height:16px !important; 
         top:-2px !important; 
         right:-2px !important; 
         z-index:10000 !important; 
       }
       .booking-calendar .flatpickr-current-month{
         font-size:14px !important;
       }
       .booking-calendar .flatpickr-weekday{
         font-size:11px !important;
       }
     }

     /* Visible scrollbar styling for mobile */
     @media (max-width: 767px){
       .booking-calendar::-webkit-scrollbar,
       .booking-calendar .flatpickr-calendar::-webkit-scrollbar,
       .booking-calendar .flatpickr-rContainer::-webkit-scrollbar,
       .booking-calendar .flatpickr-innerContainer::-webkit-scrollbar {
         height:8px !important;
       }
       .booking-calendar::-webkit-scrollbar-track,
       .booking-calendar .flatpickr-calendar::-webkit-scrollbar-track,
       .booking-calendar .flatpickr-rContainer::-webkit-scrollbar-track,
       .booking-calendar .flatpickr-innerContainer::-webkit-scrollbar-track {
         background:rgba(0,0,0,0.08) !important;
         border-radius:10px !important;
       }
       .booking-calendar::-webkit-scrollbar-thumb,
       .booking-calendar .flatpickr-calendar::-webkit-scrollbar-thumb,
       .booking-calendar .flatpickr-rContainer::-webkit-scrollbar-thumb,
       .booking-calendar .flatpickr-innerContainer::-webkit-scrollbar-thumb {
         background:rgba(0,0,0,0.25) !important;
         border-radius:10px !important;
       }
       .booking-calendar::-webkit-scrollbar-thumb:hover,
       .booking-calendar .flatpickr-calendar::-webkit-scrollbar-thumb:hover,
       .booking-calendar .flatpickr-rContainer::-webkit-scrollbar-thumb:hover,
       .booking-calendar .flatpickr-innerContainer::-webkit-scrollbar-thumb:hover {
         background:rgba(0,0,0,0.4) !important;
       }
     }

     /* TABLET: 768px to 1024px - centered single */
     @media (min-width: 768px) and (max-width: 1024px){
       .booking-calendar .flatpickr-innerContainer{ 
         justify-content:center !important;
         gap:0 !important; 
         padding:20px !important; 
         overflow-x:visible !important;
         width:100% !important;
       }
       .booking-calendar .flatpickr-months{
         flex:0 0 auto !important;
         max-width:420px !important;
         min-width:400px !important;
       }
       .booking-calendar .flatpickr-day{ 
         min-height:75px !important; 
         font-size:20px !important;
       }
     }

     /* DESKTOP: 1025px and up - centered single */
     @media (min-width: 1025px){
       .booking-calendar .flatpickr-innerContainer{ 
         justify-content:center !important;
         overflow-x:visible !important;
         width:100% !important;
       }
       .booking-calendar .flatpickr-months{
         flex:0 0 auto !important;
         max-width:450px !important;
         min-width:420px !important;
       }
       .booking-calendar .flatpickr-day{ 
         min-height:80px !important; 
         font-size:24px !important;
       }
     }

    /* Scrollbar styling for mobile scroll */
    .booking-calendar .flatpickr-innerContainer::-webkit-scrollbar {
      height:6px !important;
    }
    .booking-calendar .flatpickr-innerContainer::-webkit-scrollbar-track {
      background:rgba(0,0,0,0.05) !important;
    }
    .booking-calendar .flatpickr-innerContainer::-webkit-scrollbar-thumb {
      background:rgba(0,0,0,0.2) !important;
      border-radius:3px !important;
    }
    .booking-calendar .flatpickr-innerContainer::-webkit-scrollbar-thumb:hover {
      background:rgba(0,0,0,0.3) !important;
    }

    /* ===== Mobile sticky CTA ===== */
    .mobile-cta-wrap{
      position: fixed;
      left: 0; right: 0;
      bottom: 0;
      padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
      background: rgba(245,239,231,.92);
      backdrop-filter: blur(8px);
      border-top: 1px solid rgba(0,0,0,.08);
      z-index: 99999;
    }

    /* Mobile calendar scrolling wrapper */
    .calendar-section {
      overflow: hidden;
    }
    .calendar-wrapper {
      display: block;
    }

    @media (max-width: 767px) {
      .calendar-section {
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch !important;
      }
      .calendar-wrapper {
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch !important;
      }
    }
  </style>

  <title>{{ $bookingType === 'daytour' ? 'Day Tour Calendar' : 'Booking Calendar' }} - Calaia Farm</title>
</head>

<body class="bg-[#f5efe7]">
  <x-header active="booking" />

  {{-- extra bottom padding so sticky CTA won't cover content on mobile --}}
  <div class="pt-4 sm:pt-6 px-4 sm:px-6 lg:px-12 pb-32 md:pb-10">
    {{-- Header --}}
    <div class="text-center mb-10 px-2">
      @if($bookingType === 'daytour')
        <h1 class="text-lg sm:text-2xl font-bold tracking-tight">Select Your Day Tour Date</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-xs text-gray-500">Choose your preferred date for the day tour</p>
      @else
        <h1 class="text-lg sm:text-2xl font-bold tracking-tight">Select Your Staycation to Check-in & Check-out Date</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-xs text-gray-500">Choose your check-in and check-out dates</p>
      @endif
    </div>

    {{-- Progress --}}
    <div class="mb-6">
      @php $totalSteps = $bookingType === 'daytour' ? 3 : 4; @endphp
      <x-booking.progress-indicator :stepNumber="1" :totalSteps="$totalSteps" :bookingType="$bookingType" />
    </div>

    {{-- Back + Guest mode --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 sm:gap-0 mb-6">
      <a href="{{ route('home') }}"
         class="inline-flex items-center px-3 sm:px-4 py-2 border border-gray-300 rounded-md text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 transition whitespace-nowrap">
        <i class="bi bi-arrow-left mr-2"></i> Back to Home
      </a>

      @if (session()->has('user.is_guest') && session('user.is_guest'))
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
              style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
          <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
        </span>
      @endif
    </div>

    @if($bookingType === 'daytour')
      <div class="max-w-7xl mx-auto"
           x-data="dayTourPicker({
              availability: @js($availability),
              initialDate: @js($selectedDate),
              lowThreshold: @js($totalQuantity - 1),
              totalQuantity: @js($totalQuantity),
              maxQty: @js($maxQty),
              dayTours: @js($dayToursList),
              bookingStartDate: @js($bookingStartDate),
              bookingEndDate: @js($bookingEndDate),
              maxPetsPerRoom: @js($maxPetsPerRoom),
              bookingType: 'daytour'
           })"
           x-init="init()">
    @else
      <div class="max-w-7xl mx-auto"
           x-data="dateRangePicker({
              availability: @js($availability),
              initialCheckin: @js($checkin),
              initialCheckout: @js($checkout),
              lowThreshold: @js($totalQuantity - 1),
              totalQuantity: @js($totalQuantity),
              maxQty: @js($maxQty),
              bookingStartDate: @js($bookingStartDate),
              bookingEndDate: @js($bookingEndDate),
              maxPetsPerRoom: @js($maxPetsPerRoom),
              bookingType: 'staycation'
           })"
           x-init="init()">
    @endif

      {{-- GUESTS & PROMO --}}
      <section class="bg-white rounded-2xl shadow-lg p-5 sm:p-6 mb-6 border border-gray-100 hover:shadow-xl transition">
        @if($bookingType === 'daytour')
          {{-- DAY TOUR: Single-line horizontal layout --}}
          <div class="grid grid-cols-1 md:grid-cols-4 gap-2 sm:gap-4 items-end">
            {{-- DATE FIELD --}}
            <div class="flex flex-col">
              <div class="relative">
                <i class="bi bi-calendar3 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                <input type="text" id="selected-date" name="selected_date" readonly autocomplete="off"
                       class="w-full border border-gray-300 bg-gray-100 rounded-lg pl-10 pr-3 py-2 sm:py-3 text-xs sm:text-sm"
                       x-model="selectedDateDisplay" placeholder="Select date">              </div>
              <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Selected Date</p></div>
            </div>

            {{-- GUESTS BUTTON --}}
            <div class="flex flex-col relative" @click.away="closeGuestsPanel()">
              <button type="button" @click.stop="toggleGuestsPanel()"
                      class="w-full h-10 sm:h-12 border border-gray-300 rounded-lg text-left px-3 sm:px-4 hover:bg-gray-50 transition-colors flex items-center justify-between text-xs sm:text-sm"
                      :aria-expanded="isGuestsPanelOpen.toString()" aria-controls="guests-panel-daytour">
                <div class="flex items-center gap-2 text-gray-700 min-w-0">
                  <i class="bi bi-people shrink-0"></i>
                  <span class="truncate" x-text="guestsSummary"></span>
                </div>
                <i class="bi shrink-0" :class="isGuestsPanelOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
              </button>
              <div class="mt-1 block">
                <p class="text-xs text-gray-500" style="font-size:13px;">Add guests (Adults/Children)</p>
              </div>

              <div id="guests-panel-daytour" x-show="isGuestsPanelOpen" x-cloak x-transition.origin.top
                   class="absolute top-full mt-2 z-9999 left-1/2 -translate-x-1/2 w-[min(680px,calc(100vw-1.5rem))] bg-white border border-gray-200 rounded-2xl shadow-xl p-3 sm:p-4">
                <div class="flex items-start justify-between gap-2 sm:gap-3">
                  <div>
                    <h3 class="text-sm sm:text-base font-semibold text-gray-800">Guests</h3>
                    <p class="text-xs text-gray-500">Set adults and children</p>
                  </div>
                  <button type="button" class="text-gray-400 hover:text-gray-600 shrink-0 text-lg" @click="closeGuestsPanel()">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>

                {{-- Simplified Guests Controls for Day Tour --}}
                <div class="mt-3 space-y-2 sm:space-y-3">
                  {{-- Adults (Day Tour) --}}
                  <div class="flex items-center justify-between border border-gray-200 rounded-xl p-2 sm:p-3">
                    <div>
                      <p class="text-xs sm:text-sm font-medium text-gray-800">Adults</p>
                      <p class="text-xs text-gray-500">Age 18+</p>
                    </div>
                    <div class="flex items-center gap-1 sm:gap-2">
                      <button type="button" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                              @click.stop="decAdults(0)" :disabled="rooms[0].adults <= 1">
                        <i class="bi bi-dash-lg"></i>
                      </button>
                      <span class="min-w-6 sm:min-w-8 text-center font-semibold text-xs sm:text-sm" x-text="rooms[0].adults"></span>
                      <button type="button" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 text-xs flex items-center justify-center"
                              @click.stop="incAdults(0)">
                        <i class="bi bi-plus-lg"></i>
                      </button>
                    </div>
                  </div>

                  {{-- Children (Day Tour) --}}
                  <div class="flex items-center justify-between border border-gray-200 rounded-xl p-2 sm:p-3">
                    <div>
                      <p class="text-xs sm:text-sm font-medium text-gray-800">Children</p>
                      <p class="text-xs text-gray-500">Age 0–12</p>
                    </div>
                    <div class="flex items-center gap-1 sm:gap-2">
                      <button type="button" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                              @click.stop="decChildren(0)" :disabled="rooms[0].children <= 0">
                        <i class="bi bi-dash-lg"></i>
                      </button>
                      <span class="min-w-6 sm:min-w-8 text-center font-semibold text-xs sm:text-sm" x-text="rooms[0].children"></span>
                      <button type="button" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 text-xs flex items-center justify-center"
                              @click.stop="incChildren(0)">
                        <i class="bi bi-plus-lg"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="mt-3 sm:mt-4 flex gap-2 justify-end">
                  <button type="button" class="px-3 sm:px-4 py-2 rounded-lg border border-gray-300 hover:border-brand/70 text-xs sm:text-sm"
                          @click="resetGuests()">Reset</button>
                  <button type="button" class="px-3 sm:px-4 py-2 rounded-lg border border-red-300 hover:border-red-500 text-xs sm:text-sm text-red-600 hover:bg-red-50"
                          @click="clearSelection()" x-show="selectedDate">
                    <i class="bi bi-trash mr-1"></i> Clear Dates
                  </button>
                  <button type="button" class="px-3 sm:px-4 py-2 rounded-lg bg-[#8f5d54] text-white hover:bg-[#7a4e48] text-xs sm:text-sm"
                          @click="closeGuestsPanel()">Done</button>
                </div>
              </div>
            </div>

            {{-- PROMO CODE --}}
            <div class="flex flex-col relative" @click.away="closePromoPanel()">
              <button type="button" @click.stop="togglePromoPanel()"
                      class="w-full h-10 sm:h-12 border border-gray-300 rounded-lg text-left px-3 sm:px-4 hover:bg-gray-50 transition-colors flex items-center justify-between text-xs sm:text-sm"
                      :aria-expanded="isPromoPanelOpen.toString()" aria-controls="promo-panel">
                <div class="flex items-center gap-2 text-gray-700">
                  <i class="bi bi-ticket-perforated"></i>
                  <span class="text-sm" x-text="promoCode ? 'Promo Applied' : 'Add Promo Code'"></span>
                </div>
                <i class="bi" :class="isPromoPanelOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
              </button>
              <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Enter promo code for discounts</p></div>

              <div id="promo-panel" x-show="isPromoPanelOpen" x-cloak x-transition.origin.top
                   class="absolute top-full mt-2 z-9999 left-1/2 -translate-x-1/2 w-[min(400px,calc(100vw-1.5rem))] bg-white border border-gray-200 rounded-2xl shadow-xl p-3 sm:p-4">
                <div class="flex items-start justify-between gap-3 sm:gap-4">
                  <div>
                    <h3 class="text-sm sm:text-base font-semibold text-gray-800">Promo Code</h3>
                    <p class="text-xs text-gray-500">Enter your promo code to get discounts</p>
                  </div>
                  <button type="button" class="text-gray-400 hover:text-gray-600 shrink-0" @click="closePromoPanel()">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>

                <div class="mt-3">
                  <input type="text" id="promo-code" name="promo_code" autocomplete="off" x-model.trim="promoCode" placeholder="Add promo code"
                         class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none text-xs sm:text-sm">
                </div>

                <div class="mt-3 sm:mt-4 flex gap-2 justify-end">
                  <button type="button" class="px-3 sm:px-4 py-2 rounded-lg border border-gray-300 hover:border-brand/70 text-xs sm:text-sm"
                          @click="cancelPromo()">Cancel</button>
                  <button type="button" class="px-3 sm:px-4 py-2 rounded-lg bg-[#8f5d54] text-white hover:bg-[#7a4e48] text-xs sm:text-sm"
                          @click="applyPromo()">Apply</button>
                </div>
              </div>
            </div>

            {{-- CONTINUE BUTTON (inline for day tour) --}}
            <div class="flex flex-col items-end justify-end">
              <button
                class="w-full h-10 sm:h-12 px-4 sm:px-6 bg-[#8f5d54] text-white rounded-lg font-semibold hover:bg-[#7a4e48] transition flex items-center justify-center gap-2 whitespace-nowrap text-xs sm:text-sm"
                @click="continueBooking()"
              >
                Continue to Packages <i class="bi bi-arrow-right"></i>
              </button>
              <br>
            </div>
          </div>
        @else
          {{-- STAYCATION: Different layout --}}
          <div class="grid grid-cols-1 md:grid-cols-5 gap-2 sm:gap-4">
            <div class="flex flex-col">
              <div class="relative">
                <i class="bi bi-calendar3 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                <input type="text" id="checkin-date" name="checkin_date" autocomplete="off" readonly
                       class="w-full border border-gray-300 bg-gray-100 rounded-lg pl-10 pr-3 py-2 sm:py-3 text-xs sm:text-sm"
                       x-model="checkinDisplay" placeholder="Select check-in date">
              </div>
              <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Check-in Date</p></div>
            </div>

            <div class="flex flex-col">
              <div class="relative">
                <i class="bi bi-calendar3 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                <input type="text" id="checkout-date" name="checkout_date" autocomplete="off" readonly
                       class="w-full border border-gray-300 bg-gray-100 rounded-lg pl-10 pr-3 py-2 sm:py-3 text-xs sm:text-sm"
                       x-model="checkoutDisplay" placeholder="Select check-out date">
              </div>
              <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Check-out Date</p></div>
            </div>

            <div class="flex flex-col">
              <div class="relative">
                <i class="bi bi-clock absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                <input type="text" id="nights-count" name="nights_count" readonly autocomplete="off"
                       class="w-full border border-gray-300 bg-gray-100 rounded-lg pl-10 pr-3 py-2 sm:py-3 text-xs sm:text-sm"
                       x-model="nightsText" placeholder="0 nights">
              </div>
              <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Number of Nights</p></div>
            </div>

            {{-- ROOMS & GUESTS (Staycation only) --}}
            <div class="flex flex-col relative" @click.away="closeGuestsPanel()">
              <button type="button" @click.stop="toggleGuestsPanel()"
                      class="w-full h-10 sm:h-12 border border-gray-300 rounded-lg text-left px-3 sm:px-4 hover:bg-gray-50 transition-colors flex items-center justify-between text-xs sm:text-sm"
                      :aria-expanded="isGuestsPanelOpen.toString()" aria-controls="guests-panel-staycation">
                <div class="flex items-center gap-2 text-gray-700 min-w-0">
                  <i class="bi bi-people shrink-0"></i>
                  <span class="text-sm md:hidden truncate" x-text="guestsSummaryShort"></span>
                  <span class="text-sm hidden md:inline" x-text="guestsSummary"></span>
                </div>
                <i class="bi shrink-0" :class="isGuestsPanelOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
              </button>
              <div class="mt-1 block">
                <p class="text-xs text-gray-500" style="font-size:13px;">Add rooms and guests (Adults/Children)</p>
              </div>

              <div id="guests-panel-staycation" x-show="isGuestsPanelOpen" x-cloak x-transition.origin.top
                   class="absolute top-full mt-2 z-9999 left-1/2 -translate-x-1/2 w-[min(680px,calc(100vw-1.5rem))] bg-white border border-gray-200 rounded-2xl shadow-xl p-3 sm:p-4">
                <div class="flex items-start justify-between gap-2 sm:gap-3">
                  <div>
                    <h3 class="text-sm sm:text-base font-semibold text-gray-800">Rooms & Guests</h3>
                    <p class="text-xs text-gray-500">Up to 4 rooms. Add guests per room.</p>
                  </div>
                  <button type="button" class="text-gray-400 hover:text-gray-600 shrink-0 text-lg" @click="closeGuestsPanel()">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>

              @if($bookingType === 'daytour')
              {{-- Simplified Guests Controls for Day Tour --}}
              <div class="mt-3 space-y-2 sm:space-y-3">
                {{-- Adults (Day Tour) --}}
                <div class="flex items-center justify-between border border-gray-200 rounded-xl p-2 sm:p-3">
                  <div>
                    <p class="text-xs sm:text-sm font-medium text-gray-800" x-text="rooms[0].adults === 1 ? 'Adult' : 'Adult(s)'"></p>
                  </div>
                  <div class="flex items-center gap-1 sm:gap-2">
                    <button type="button" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                            @click.stop="decAdults(0)" :disabled="rooms[0].adults <= 1">
                      <i class="bi bi-dash-lg"></i>
                    </button>
                    <span class="min-w-6 sm:min-w-8 text-center font-semibold text-xs sm:text-sm" x-text="rooms[0].adults"></span>
                    <button type="button" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 text-xs flex items-center justify-center"
                            @click.stop="incAdults(0)">
                      <i class="bi bi-plus-lg"></i>
                    </button>
                  </div>
                </div>

                {{-- Children (Day Tour) --}}
                <div class="flex items-center justify-between border border-gray-200 rounded-xl p-2 sm:p-3">
                  <div>
                    <p class="text-xs sm:text-sm font-medium text-gray-800">Children</p>
                    <p class="text-xs text-gray-500">Age 0–12</p>
                  </div>
                  <div class="flex items-center gap-1 sm:gap-2">
                    <button type="button" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                            @click.stop="decChildren(0)" :disabled="rooms[0].children <= 0">
                      <i class="bi bi-dash-lg"></i>
                    </button>
                    <span class="min-w-6 sm:min-w-8 text-center font-semibold text-xs sm:text-sm" x-text="rooms[0].children"></span>
                    <button type="button" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 text-xs flex items-center justify-center"
                            @click.stop="incChildren(0)">
                      <i class="bi bi-plus-lg"></i>
                    </button>
                  </div>
                </div>
              </div>
              @else

              {{-- Rooms count - Staycation only --}}
              <div class="mt-3 flex items-center justify-between border border-gray-200 rounded-xl p-2 sm:p-3">
                <div>
                  <p class="text-xs sm:text-sm font-medium text-gray-800">Rooms</p>
                  <p class="text-xs text-gray-500">Choose 1 to 4 rooms</p>
                </div>
                <div class="flex items-center gap-1 sm:gap-2">
                  <button type="button" class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                          @click.stop="setRoomsCount(roomsCount - 1)">
                    <i class="bi bi-dash-lg"></i>
                  </button>
                  <span class="min-w-8 text-center font-semibold text-xs sm:text-sm" x-text="roomsCount"></span>
                  <button type="button" class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                          @click.stop="setRoomsCount(roomsCount + 1)">
                    <i class="bi bi-plus-lg"></i>
                  </button>
                </div>
              </div>

              {{-- Per-room controls - Staycation only --}}
              <div class="mt-3 space-y-3 max-h-80 md:max-h-96 overflow-auto pr-1">
                <template x-for="(room, idx) in rooms" :key="idx">
                  <div class="border border-gray-200 rounded-xl p-2 sm:p-3">
                    <div class="flex items-center justify-between gap-2">
                      <p class="text-xs sm:text-sm font-semibold text-gray-800 whitespace-nowrap" x-text="`Room ${idx+1}`"></p>
                      <p class="text-xs text-gray-500 text-right truncate">
                        <span x-text="room.adults"></span> <span x-text="room.adults === 1 ? 'Adult' : 'Adults'"></span> • <span x-text="room.children"></span> <span x-text="room.children === 1 ? 'Child' : 'Children'"></span><span x-show="room.pets > 0"> • <span x-text="room.pets"></span> <span x-text="room.pets === 1 ? 'Pet' : 'Pets'"></span></span>
                      </p>
                    </div>

                    <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                      <div class="flex items-center justify-between border border-gray-200 rounded-lg p-1.5 sm:p-2.5">
                        <div class="pr-1 flex-1 min-w-0">
                          <p class="text-xs sm:text-sm font-medium text-gray-800 truncate" x-text="room.adults === 1 ? 'Adult' : 'Adult(s)'"></p>
                        </div>
                        <div class="flex items-center gap-0.5 shrink-0">
                          <button type="button" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                                  @click="decAdults(idx)" :disabled="room.adults <= 1">
                            <i class="bi bi-dash-lg" style="font-size: 0.75rem;"></i>
                          </button>
                          <span class="w-5 sm:w-6 text-center font-semibold text-xs sm:text-sm" x-text="room.adults"></span>
                          <button type="button" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-lg border border-gray-300 hover:border-brand/70 text-xs flex items-center justify-center"
                                  @click="incAdults(idx)">
                            <i class="bi bi-plus-lg" style="font-size: 0.75rem;"></i>
                          </button>
                        </div>
                      </div>

                      <div class="flex items-center justify-between border border-gray-200 rounded-lg p-1.5 sm:p-2.5">
                        <div class="pr-1 flex-1 min-w-0">
                          <p class="text-xs sm:text-sm font-medium text-gray-800 truncate">Children</p>
                          <p class="text-xs text-gray-500">0–12</p>
                        </div>
                        <div class="flex items-center gap-0.5 shrink-0">
                          <button type="button" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                                  @click="decChildren(idx)" :disabled="room.children <= 0">
                            <i class="bi bi-dash-lg" style="font-size: 0.75rem;"></i>
                          </button>
                          <span class="w-5 sm:w-6 text-center font-semibold text-xs sm:text-sm" x-text="room.children"></span>
                          <button type="button" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-lg border border-gray-300 hover:border-brand/70 text-xs flex items-center justify-center"
                                  @click="incChildren(idx)">
                            <i class="bi bi-plus-lg" style="font-size: 0.75rem;"></i>
                          </button>
                        </div>
                      </div>

                      <div class="flex items-center justify-between border border-gray-200 rounded-lg p-1.5 sm:p-2.5">
                        <div class="pr-1 flex-1 min-w-0">
                          <p class="text-xs sm:text-sm font-medium text-gray-800 truncate" x-text="room.pets === 1 ? 'Pet' : 'Pet(s)'"></p>
                        </div>
                        <div class="flex items-center gap-0.5 shrink-0">
                          <button type="button" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-lg border border-gray-300 hover:border-brand/70 disabled:opacity-40 text-xs flex items-center justify-center"
                                  @click="decPets(idx)" :disabled="room.pets <= 0">
                            <i class="bi bi-dash-lg" style="font-size: 0.75rem;"></i>
                          </button>
                          <span class="w-5 sm:w-6 text-center font-semibold text-xs sm:text-sm" x-text="room.pets || 0"></span>
                          <button type="button" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-lg border border-gray-300 hover:border-brand/70 text-xs flex items-center justify-center"
                                  @click="incPets(idx)">
                            <i class="bi bi-plus-lg" style="font-size: 0.75rem;"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>

              <div class="mt-3 sm:mt-4 flex gap-2 justify-end">
                <button type="button" class="px-3 sm:px-4 py-2 rounded-lg border border-gray-300 hover:border-brand/70 text-xs sm:text-sm"
                        @click="resetGuests()">Reset</button>
                <button type="button" class="px-3 sm:px-4 py-2 rounded-lg border border-red-300 hover:border-red-500 text-xs sm:text-sm text-red-600 hover:bg-red-50"
                        @click="clearSelection()" x-show="checkin && checkout">
                  <i class="bi bi-trash mr-1"></i> Clear Dates
                </button>
                <button type="button" class="px-3 sm:px-4 py-2 rounded-lg bg-[#8f5d54] text-white hover:bg-[#7a4e48] text-xs sm:text-sm"
                        @click="closeGuestsPanel()">Done</button>
              </div>
            </div>
            </div>
            @endif

            {{-- PROMO CODE (Staycation only) --}}
            <div class="flex flex-col relative" @click.away="closePromoPanel()">
              <button type="button" @click.stop="togglePromoPanel()"
                      class="w-full h-10 sm:h-12 border border-gray-300 rounded-lg text-left px-3 sm:px-4 hover:bg-gray-50 transition-colors flex items-center justify-between text-xs sm:text-sm"
                      :aria-expanded="isPromoPanelOpen.toString()" aria-controls="promo-panel">
                <div class="flex items-center gap-2 text-gray-700">
                  <i class="bi bi-ticket-perforated"></i>
                  <span class="text-sm" x-text="promoCode ? 'Promo Applied' : 'Add Promo Code'"></span>
                </div>
                <i class="bi" :class="isPromoPanelOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
              </button>
              <div class="mt-1"><p class="text-xs text-gray-500" style="font-size:13px;">Enter promo code for discounts</p></div>

              <div id="promo-panel" x-show="isPromoPanelOpen" x-cloak x-transition.origin.top
                   class="absolute top-full mt-2 z-9999 left-1/2 -translate-x-1/2 w-[min(400px,calc(100vw-1.5rem))] bg-white border border-gray-200 rounded-2xl shadow-xl p-3 sm:p-4">
                <div class="flex items-start justify-between gap-3 sm:gap-4">
                  <div>
                    <h3 class="text-sm sm:text-base font-semibold text-gray-800">Promo Code</h3>
                    <p class="text-xs text-gray-500">Enter your promo code to get discounts</p>
                  </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 shrink-0" @click="closePromoPanel()">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>

              <div class="mt-3">
                <input type="text" id="promo-code" name="promo_code" autocomplete="off" x-model.trim="promoCode" placeholder="Add promo code"
                       class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none text-xs sm:text-sm">
              </div>

              <div class="mt-3 sm:mt-4 flex gap-2 justify-end">
                <button type="button" class="px-3 sm:px-4 py-2 rounded-lg border border-gray-300 hover:border-brand/70 text-xs sm:text-sm"
                        @click="cancelPromo()">Cancel</button>
                <button type="button" class="px-3 sm:px-4 py-2 rounded-lg bg-[#8f5d54] text-white hover:bg-[#7a4e48] text-xs sm:text-sm"
                        @click="applyPromo()">Apply</button>
              </div>
            </div>
            </div>
          </div>
        @endif

        {{-- CENTERED CONTINUE BUTTON (Desktop - Staycation only) --}}
        @if($bookingType !== 'daytour')
        <div class="mt-8 hidden md:flex justify-center">
          <button
            class="px-10 py-4 bg-[#8f5d54] text-white rounded-xl font-bold text-lg hover:bg-[#7a4e48] transition flex items-center justify-center gap-2 shadow-lg hover:shadow-xl hover:scale-105"
            @click="continueBooking()"
          >
            Continue to Rooms <i class="bi bi-arrow-right text-xl"></i>
          </button>
        </div>
        @endif
      </section>

      {{-- CONTINUE BUTTON (mobile sticky) --}}
      <div class="md:hidden fixed left-0 right-0 bottom-0 p-4 bg-white border-t border-gray-200 z-40 shadow-lg">
        <button
          class="w-full h-12 bg-[#8f5d54] text-white rounded-lg font-bold hover:bg-[#7a4e48] transition flex items-center justify-center gap-2 text-base shadow-md"
          @click="continueBooking()"
        >
          @if($bookingType === 'daytour')
            Continue to Packages <i class="bi bi-arrow-right"></i>
          @else
            Continue to Rooms <i class="bi bi-arrow-right"></i>
          @endif
        </button>
      </div>

      {{-- SELECTION SUMMARY BANNER --}}
      <template x-if="@js($bookingType) === 'daytour' && selectedDate">
        <section class="bg-linear-to-r from-[#8f5d54] to-[#a67563] rounded-2xl shadow-lg p-4 sm:p-6 mb-6 text-white">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
              <h3 class="text-xs sm:text-sm font-semibold opacity-90 mb-1">Your Selection</h3>
              <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm sm:text-base font-semibold">
                <div class="flex items-center gap-2">
                  <i class="bi bi-calendar2-check"></i>
                  <span x-text="selectedDateDisplay"></span>
                </div>
                <div class="hidden sm:block text-white/40">•</div>
                <div class="flex items-center gap-2">
                  <i class="bi bi-people"></i>
                  <span x-text="guestsSummary"></span>
                </div>
                <template x-if="estimatedPrice">
                  <div class="hidden sm:block text-white/40">•</div>
                  <div class="flex items-center gap-2" x-show="estimatedPrice">
                    <i class="bi bi-currency-dollar"></i>
                    <span x-text="'₱' + estimatedPrice.toLocaleString()"></span>
                  </div>
                </template>
              </div>
            </div>
            <button type="button" @click="clearSelection()" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-xs sm:text-sm font-medium transition flex items-center justify-center gap-2">
              <i class="bi bi-x-lg"></i> Clear
            </button>
          </div>
        </section>
      </template>

      <template x-if="@js($bookingType) !== 'daytour' && checkin && checkout">
        <section class="bg-linear-to-r from-[#8f5d54] to-[#a67563] rounded-2xl shadow-lg p-4 sm:p-6 mb-6 text-white">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
              <h3 class="text-xs sm:text-sm font-semibold opacity-90 mb-1">Your Selection</h3>
              <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm sm:text-base font-semibold">
                <div class="flex items-center gap-2">
                  <i class="bi bi-calendar2-range"></i>
                  <span><span x-text="checkinDisplay"></span> → <span x-text="checkoutDisplay"></span></span>
                </div>
                <div class="hidden sm:block text-white/40">•</div>
                <div class="flex items-center gap-2">
                  <i class="bi bi-hourglass-split"></i>
                  <span x-text="nightsText"></span>
                </div>
                <div class="hidden sm:block text-white/40">•</div>
                <div class="flex items-center gap-2">
                  <i class="bi bi-people"></i>
                  <span x-text="guestsSummary"></span>
                </div>
                <template x-if="estimatedPrice">
                  <div class="hidden sm:block text-white/40">•</div>
                  <div class="flex items-center gap-2" x-show="estimatedPrice">
                    <i class="bi bi-currency-dollar"></i>
                    <span x-text="'₱' + estimatedPrice.toLocaleString()"></span>
                  </div>
                </template>
              </div>
            </div>
            <button type="button" @click="clearSelection()" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-xs sm:text-sm font-medium transition flex items-center justify-center gap-2">
              <i class="bi bi-x-lg"></i> Clear
            </button>
          </div>
        </section>
      </template>

      {{-- CALENDAR WRAP - CENTERED CARD LAYOUT --}}
      <section class="mx-auto mb-6 px-3 sm:px-4" style="max-width: 700px;">
        <div class="bg-white rounded-2xl shadow-lg calendar-section overflow-hidden hover:shadow-xl transition-shadow duration-300">
          <div class="p-5 sm:p-6 md:p-8 calendar-wrapper">
            <div x-show="!bookingStartDate || !bookingEndDate" class="text-center py-12">
              <i class="bi bi-calendar-x text-6xl text-gray-300 mb-4"></i>
              <h3 class="text-xl font-semibold text-gray-700 mb-2">Booking Not Available</h3>
              <p class="text-gray-500">No booking dates are currently configured. Please check back later or contact us for assistance.</p>
            </div>

            <!-- Month/Year Navigation - Centered -->
            <div x-show="bookingStartDate && bookingEndDate" class="flex items-center justify-center gap-3 sm:gap-4 mb-6 sm:mb-8">
              <button @click="prevMonth()" class="p-2 hover:bg-gray-100 rounded-lg transition duration-200 hover:scale-110" title="Previous Month">
                <i class="bi bi-chevron-left text-xl sm:text-2xl text-gray-700"></i>
              </button>
              <div class="text-center min-w-40">
                <span x-text="getMonthYearDisplay()" class="text-lg sm:text-xl font-bold text-gray-900"></span>
              </div>
              <button @click="nextMonth()" class="p-2 hover:bg-gray-100 rounded-lg transition duration-200 hover:scale-110" title="Next Month">
                <i class="bi bi-chevron-right text-xl sm:text-2xl text-gray-700"></i>
              </button>
            </div>

            <!-- Calendar - Perfectly Centered -->
            <div x-show="bookingStartDate && bookingEndDate" class="flex justify-center w-full mb-8">
              <div x-ref="calendar" class="booking-calendar"></div>
            </div>

            <!-- Availability Legend -->
            <div x-show="bookingStartDate && bookingEndDate" class="flex justify-center gap-4 sm:gap-6 mb-8 pb-6 sm:pb-8 border-b border-gray-100">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-green-100 border-2 border-green-500"></div>
                <span class="text-xs sm:text-sm text-gray-600">Available</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-yellow-100 border-2 border-yellow-500"></div>
                <span class="text-xs sm:text-sm text-gray-600">Limited</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-gray-200"></div>
                <span class="text-xs sm:text-sm text-gray-600">Booked</span>
              </div>
            </div>

            <!-- Estimated Price - Enhanced & Responsive -->
            <div x-show="estimatedPrice" class="text-center">
              <p class="text-xs sm:text-sm text-gray-500 mb-2 uppercase tracking-wide font-semibold">Total Price</p>
              <div class="flex items-center justify-center gap-2 sm:gap-3 mb-3">
                <p class="text-3xl sm:text-4xl md:text-5xl font-black text-[#8f5d54]" x-show="!isCalculatingPrice" x-text="estimatedPrice ? '₱' + estimatedPrice.toLocaleString() : '—'"></p>
              </div>
              <p class="text-xs sm:text-sm text-gray-500">Rates are subject to change</p>
              <div x-show="isCalculatingPrice" class="mt-4">
                <div class="flex justify-center items-center gap-2">
                  <div class="w-2 h-2 bg-[#8f5d54] rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                  <div class="w-2 h-2 bg-[#8f5d54] rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                  <div class="w-2 h-2 bg-[#8f5d54] rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Calculating price...</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Info Bar -->
        @if($bookingType === 'daytour')
          <div class="mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-2 px-4 sm:px-6 py-4 sm:py-5 text-white rounded-b-2xl text-center md:text-left" style="background:#91b474; max-width: 700px;">
            <div class="text-xs sm:text-sm font-semibold">⏰ Operating Hours: <span class="font-normal">8:00 AM - 5:00 PM</span></div>
            <div class="text-xs sm:text-sm italic hidden sm:block">Select a date to see available packages</div>
          </div>
        @else
          <div class="mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 text-white rounded-b-2xl text-center md:text-left" style="background:#91b474; max-width: 700px;">
            <div class="text-xs sm:text-sm font-semibold">
              🕐 Check-in: <span class="font-normal">1:00 PM</span> | Check-out: <span class="font-normal">11:00 AM</span>
            </div>
            <div class="text-xs sm:text-sm italic">Rates may vary</div>
          </div>
        @endif
      </section>

      {{-- MOBILE STICKY CTA (always visible) --}}
      <div class="mobile-cta-wrap md:hidden">
        <button
          class="w-full h-13 bg-[#8f5d54] text-white rounded-xl font-extrabold shadow-xl hover:bg-[#7a4e48] transition flex items-center justify-center gap-2 text-base hover:scale-105"
          @click="continueBooking()"
        >
          @if($bookingType === 'daytour')
            Continue to Packages <i class="bi bi-arrow-right text-lg"></i>
          @else
            Continue to Rooms <i class="bi bi-arrow-right text-lg"></i>
          @endif
        </button>
      </div>

    </div> {{-- end x-data wrapper --}}
  </div> {{-- end page wrapper --}}

  <x-floating-buttons />
  <x-footer />
</body>
</html>
{{-- Alpine.js components (dayTourPicker, dateRangePicker) are defined in the <head> section to ensure they're available when Alpine.js initializes --}}
