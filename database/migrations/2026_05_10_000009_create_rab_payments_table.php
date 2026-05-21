<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rab_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->foreignId('paid_by')->constrained('users')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('paid_amount', 15, 2);
            $table->string('payment_method');
            $table->string('recipient_account')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('proof_file_path');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_payments');
    }
};
