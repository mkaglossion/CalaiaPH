# CalaiaPH Configuration Guide

## Critical Configuration Settings

This document outlines important configuration settings that must be maintained for proper system functionality.

---

## Broadcasting Configuration (CRITICAL ⚠️)

### Setting: `BROADCAST_CONNECTION`

**Location:** `.env` file, line ~59

**Current Value:** `pusher`

**Purpose:** Enables real-time updates for:
- 📊 Inventory updates (room availability changes)
- 🔔 System notifications
- 📱 Live booking updates
- 🌍 Multi-user synchronization

### Important Notes

⚠️ **If set to 'log':**
- Real-time features will NOT work
- Inventory changes won't sync across users
- Notifications won't be delivered
- Calendar will show stale data

✅ **If set to 'pusher':**
- All real-time features work correctly
- Users see live inventory updates
- System notifications deliver instantly
- Multiple users see synchronized data

### Pusher Configuration

Ensure these are also configured in `.env`:
```env
PUSHER_APP_ID=2105024
PUSHER_APP_KEY=4777b95d5181134b08f5
PUSHER_APP_SECRET=25b201a8f47add95acde
PUSHER_APP_CLUSTER=ap1
```

### Broadcast-Dependent Features

These features require `BROADCAST_CONNECTION=pusher`:

1. **Staycation Availability Page**
   - File: `resources/views/booking/staycation-availability.blade.php`
   - Listens to: `staycation-inventory` channel
   - Event: `StaycationInventoryUpdated`

2. **Day Visit Availability Page**
   - File: `resources/views/booking/dayvisit-availability.blade.php`
   - Listens to: `daytour-inventory` channel
   - Event: `DayTourInventoryUpdated`

3. **Admin Dashboard**
   - Real-time notifications for bookings, system alerts, activity logs

### Troubleshooting Broadcasting

**Problem:** Real-time updates not working

**Solution Steps:**
1. Verify `.env` has `BROADCAST_CONNECTION=pusher`
2. Verify Pusher credentials are correct
3. Clear Laravel cache: `php artisan cache:clear`
4. Restart your application
5. Check browser console for Pusher connection errors

---

## Default Booking Values (User Experience)

### Setting: Adults & Children Defaults

**Default Values:** 
- Adults: `1`
- Children: `0`

**Locations Modified:**
- `app/Http/Controllers/BookingController.php` (lines 1756-1758)
- `resources/views/booking/staycation-availability.blade.php` (line 1539)
- `resources/views/booking/calendar.blade.php` (multiple locations)

**Purpose:** Provides better UX by defaulting to single adult booking, reducing form interactions

---

## Calendar Availability Colors

**File:** `resources/views/components/check-availability.blade.php`

**Color Scheme:**
- 🟢 **Green** (`#22c55e`) - Full availability (3+ slots)
- 🟡 **Yellow** (`#eab308`) - Limited availability (1-2 slots)
- ⚫ **Gray** (`#d1d5db`) - No availability (0 slots or unavailable)

**CSS Classes:**
- `.fp-avail-high` - Green (full)
- `.fp-avail-low` - Yellow (limited)
- `.fp-not-avail` - Gray (none)

---

## Maintenance Checklist

- [ ] `BROADCAST_CONNECTION` is set to `pusher` in `.env`
- [ ] Pusher credentials are valid and active
- [ ] Calendar colors are displaying correctly
- [ ] Default booking values (1 adult, 0 children) are showing
- [ ] Real-time inventory updates working in booking pages
- [ ] No console errors related to broadcasting/Pusher

---

## Related Files

- Configuration: `config/broadcasting.php`
- Environment Template: `.env.example`
- Booking Controller: `app/Http/Controllers/BookingController.php`
- Staycation View: `resources/views/booking/staycation-availability.blade.php`
- Calendar Component: `resources/views/components/check-availability.blade.php`

---

**Last Updated:** April 23, 2026
**Status:** ✅ All critical settings configured
