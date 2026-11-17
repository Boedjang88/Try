<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'id',
        'code',
        'name',
        'building',
        'floor',
        'capacity',
        'room_type',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'is_active',
    ];

    protected $casts = [
        'floor' => 'integer',
        'capacity' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'geofence_radius_meters' => 'integer',
        'is_active' => 'boolean',
    ];

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    public function lectureSessions(): HasMany
    {
        return $this->hasMany(LectureSession::class);
    }

    /**
     * Calculate Haversine distance in meters to a given latitude/longitude.
     */
    public function distanceTo(float $lat, float $lon): float
    {
        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad((float)$this->latitude);
        $lonFrom = deg2rad((float)$this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lon);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Check if a given GPS coordinate is within this room's geofence boundary.
     */
    public function isWithinGeofence(float $lat, float $lon): bool
    {
        return $this->distanceTo($lat, $lon) <= $this->geofence_radius_meters;
    }
}
