<?php

use App\Enums\RabReceiptStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rab_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('receipt_date');
            $table->string('store_name');
            $table->string('receipt_number')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->string('receipt_file');
            $table->string('status')->default(RabReceiptStatus::MENUNGGU_VALIDASI->value);
            $table->text('notes')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_receipts');
    }
};
