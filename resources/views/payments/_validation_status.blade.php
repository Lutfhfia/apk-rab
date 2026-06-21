@php
    $payment = $payment ?? $rab->payment;
    $validationStatus = $payment->validation_status
        ?? \App\Enums\PaymentValidationStatus::VALID;
@endphp

<div class="mt-4 pt-4 border-t border-gray-100">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase">Status Pencairan Dana</p>
            <div class="mt-1 flex flex-wrap items-center gap-2">
                <span class="{{ $validationStatus->badgeClasses() }} text-[10px] font-bold px-3 py-1.5 rounded-lg">{{ $validationStatus->label() }}</span>
                @if($payment->validator)
                <span class="text-xs text-gray-400">oleh {{ $payment->validator->name }} pada {{ $payment->validated_at?->format('d/m/Y H:i') }}</span>
                @endif
            </div>
            @if($payment->validation_notes)
            <p class="text-sm text-gray-600 bg-gray-50 border border-gray-100 rounded-lg p-3 mt-3 whitespace-pre-line">{{ $payment->validation_notes }}</p>
            @endif
        </div>
    </div>
</div>

