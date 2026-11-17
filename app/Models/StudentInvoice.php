<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentInvoice extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'student_id',
        'academic_period_id',
        'invoice_number',
        'va_number',
        'title',
        'amount',
        'paid_amount',
        'payment_status', // unpaid, partially_paid, paid, expired
        'due_date',
        'paid_at',
        'payment_method',
        'gateway_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid' && $this->paid_amount >= $this->amount;
    }
}
