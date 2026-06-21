<?php

use App\Enums\PaymentValidationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rab_payments', function (Blueprint $table) {
            $table->string('validation_status')->default(PaymentValidationStatus::MENUNGGU_VALIDASI->value)->after('notes');
            $table->text('validation_notes')->nullable()->after('validation_status');
            $table->foreignId('validated_by')->nullable()->after('validation_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable()->after('validated_by');
        });

        DB::table('rab_payments')
            ->join('rabs', 'rab_payments.rab_id', '=', 'rabs.id')
            ->where('rabs.status', 'selesai')
            ->update(['rab_payments.validation_status' => PaymentValidationStatus::VALID->value]);

        DB::table('rab_payments')
            ->whereNull('validation_status')
            ->update(['validation_status' => PaymentValidationStatus::MENUNGGU_VALIDASI->value]);
    }

    public function down(): void
    {
        Schema::table('rab_payments', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn(['validation_status', 'validation_notes', 'validated_by', 'validated_at']);
        });
    }
};
