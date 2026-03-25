<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageInventory extends Model
{
    use HasFactory;

    protected $table = 'package_inventory';

    protected $fillable = [
        'package_id',
        'date',
        'max_groups',
        'booked_groups',
        'available_groups',
        'is_blocked',
    ];

    protected $casts = [
        'date' => 'date',
        'max_groups' => 'integer',
        'booked_groups' => 'integer',
        'available_groups' => 'integer',
        'is_blocked' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Check if this inventory slot is available
     */
    public function isAvailable(): bool
    {
        return $this->available_groups > 0 && !$this->is_blocked;
    }

    /**
     * Book groups for this date
     */
    public function bookGroups($groupsToBook): bool
    {
        if ($groupsToBook > $this->available_groups) {
            return false;
        }

        $this->update([
            'booked_groups' => $this->booked_groups + $groupsToBook,
            'available_groups' => $this->available_groups - $groupsToBook,
        ]);

        return true;
    }

    /**
     * Release booked groups
     */
    public function releaseGroups($groupsToRelease): bool
    {
        if ($groupsToRelease > $this->booked_groups) {
            return false;
        }

        $this->update([
            'booked_groups' => $this->booked_groups - $groupsToRelease,
            'available_groups' => $this->available_groups + $groupsToRelease,
        ]);

        return true;
    }
}
