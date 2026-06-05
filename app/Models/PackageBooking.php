<?php

namespace App\Models;

use App\Helpers\TimeHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageBooking extends Model
{
    protected $fillable = [
        'booking_reference',
        'package_id',
        'booking_date',
        'groups_booked',
        'session',
        'total_guests',
        'first_name',
        'last_name',
        'email',
        'phone',
        'special_requests',
        'total_amount',
        'status',
        'payment_status',
        'paymongo_reference',
        'payment_reference'
    ];
    
    protected $attributes = [
        'groups_booked' => 1,
        'inventory_booked' => false,
    ];

    protected $casts = [
        'booking_date' => 'date',
        'total_amount' => 'decimal:2',
        'groups_booked' => 'integer',
        'inventory_booked' => 'boolean',
    ];

    /**
     * Get the package for this booking
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFormattedBookingDateAttribute(): string
    {
        $rawDate = $this->getRawOriginal('booking_date');
        if (!empty($rawDate)) {
            return \Carbon\Carbon::parse($rawDate)->format('F d, Y');
        }

        return optional($this->booking_date)->format('F d, Y') ?? 'N/A';
    }

    public function getDisplaySessionAttribute(): string
    {
        $sessionName = trim((string) $this->session);
        if ($sessionName === '') {
            return 'To be confirmed';
        }

        $label = collect(preg_split('/[_\-\s]+/', $sessionName) ?: [])
            ->filter()
            ->map(fn (string $part) => ucfirst(strtolower($part)))
            ->implode(' ');

        $package = $this->relationLoaded('package') ? $this->package : $this->package()->first();
        $sessions = collect($package?->sessions ?? []);
        $matchingSession = $sessions->first(function ($session) use ($sessionName) {
            $name = trim((string) data_get($session, 'name', ''));
            return strcasecmp($name, $sessionName) === 0;
        });

        $start = data_get($matchingSession, 'start');
        $end = data_get($matchingSession, 'end');

        if ($start && $end) {
            return sprintf('%s (%s)', $label !== '' ? $label : $sessionName, TimeHelper::formatSessionTime($start, $end));
        }

        return $label !== '' ? $label : $sessionName;
    }
}
