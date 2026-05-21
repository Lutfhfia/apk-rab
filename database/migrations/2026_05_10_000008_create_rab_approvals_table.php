<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rab_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['manajer_keuangan', 'direktur']);
            $table->enum('approval_level', ['manager', 'director']);
            $table->enum('status', ['approved', 'rejected']);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_approvals');
    }
};
