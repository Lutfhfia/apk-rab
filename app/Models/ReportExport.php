<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    protected $fillable = [
        'exported_by',
        'report_type',
        'start_date',
        'end_date',
        'file_path',
        'format',
        'total_debit',
        'total_credit',
        'ending_balance',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
            'ending_balance' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke model User (Pengguna yang melakukan ekspor laporan).
     */
    public function exportedBy()
    {
        return $this->belongsTo(User::class, 'exported_by');
    }
}
