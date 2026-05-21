<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryExpenseItem extends Model
{
    protected $fillable = [
        'rab_id',
        'employee_name',
        'position',
        'bank_account_number',
        'bank_name',
        'attendance_days',
        'base_salary',
        'meal_allowance_daily',
        'transport_daily',
        'overtime',
        'total_salary',
        'salary_nominal',
        'notes',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'attendance_days' => 'integer',
            'base_salary' => 'decimal:2',
            'meal_allowance_daily' => 'decimal:2',
            'transport_daily' => 'decimal:2',
            'overtime' => 'decimal:2',
            'total_salary' => 'decimal:2',
            'salary_nominal' => 'decimal:2',
        ];
    }

    /**
     * Calculate total salary from components.
     */
    public function calculateTotal(): float
    {
        $mealTotal = $this->attendance_days * $this->meal_allowance_daily;
        $transportTotal = $this->attendance_days * $this->transport_daily;
        return $this->base_salary + $mealTotal + $transportTotal + $this->overtime;
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }
}
