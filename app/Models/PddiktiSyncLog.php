<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PddiktiSyncLog extends Model
{
    protected $fillable = [
        'id',
        'entity_type', // Mahasiswa, Dosen, KelasKuliah, Nilai, AKM
        'batch_reference',
        'total_records',
        'success_count',
        'failed_count',
        'status', // pending, in_progress, completed, failed
        'error_payload',
        'synced_by_user_id',
    ];

    protected $casts = [
        'total_records' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'error_payload' => 'array',
    ];

    public function syncedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'synced_by_user_id');
    }
}
