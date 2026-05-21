<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->string('need_name');
            $table->text('description')->nullable();
            $table->decimal('volume', 12, 2);
            $table->string('unit');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_expense_items');
    }
};
