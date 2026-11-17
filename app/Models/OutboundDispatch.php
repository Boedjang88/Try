<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OutboundDispatch extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'dispatch_id',
        'channel',
        'recipient',
        'subject_or_title',
        'payload_body',
        'status',
        'event_type',
    ];
}
