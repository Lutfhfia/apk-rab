<?php

namespace Tests\Unit;

use App\Models\Rab;
use App\Enums\RabStatus;
use PHPUnit\Framework\TestCase;

class RabModelTest extends TestCase
{
    // ── Number Building ──

    public function test_build_number_formats_correctly(): void
    {
        $this->assertEquals('001/RAB/SBK/I/2026', Rab::buildNumber(1, '01', '2026'));
        $this->assertEquals('005/RAB/SBK/VI/2026', Rab::buildNumber(5, '06', '2026'));
        $this->assertEquals('012/RAB/SBK/XII/2026', Rab::buildNumber(12, '12', '2026'));
        $this->assertEquals('100/RAB/SBK/III/2026', Rab::buildNumber(100, '03', '2026'));
    }

    public function test_build_number_pads_sequence_to_3_digits(): void
    {
        $this->assertStringStartsWith('001/', Rab::buildNumber(1, '01', '2026'));
        $this->assertStringStartsWith('010/', Rab::buildNumber(10, '01', '2026'));
        $this->assertStringStartsWith('100/', Rab::buildNumber(100, '01', '2026'));
    }

    // ── Month to Roman Numeral ──

    public function test_normalize_month_to_roman_works_for_numeric_input(): void
    {
        $this->assertEquals('I', Rab::normalizeMonthToRoman('1'));
        $this->assertEquals('I', Rab::normalizeMonthToRoman('01'));
        $this->assertEquals('VI', Rab::normalizeMonthToRoman('6'));
        $this->assertEquals('VI', Rab::normalizeMonthToRoman('06'));
        $this->assertEquals('XII', Rab::normalizeMonthToRoman('12'));
    }

    public function test_normalize_month_to_roman_works_for_name_input(): void
    {
        $this->assertEquals('I', Rab::normalizeMonthToRoman('Januari'));
        $this->assertEquals('II', Rab::normalizeMonthToRoman('Februari'));
        $this->assertEquals('V', Rab::normalizeMonthToRoman('Mei'));
        $this->assertEquals('IX', Rab::normalizeMonthToRoman('September'));
        $this->assertEquals('XII', Rab::normalizeMonthToRoman('Desember'));
    }

    public function test_normalize_month_to_roman_is_case_insensitive(): void
    {
        $this->assertEquals('I', Rab::normalizeMonthToRoman('januari'));
        $this->assertEquals('I', Rab::normalizeMonthToRoman('JANUARI'));
        $this->assertEquals('I', Rab::normalizeMonthToRoman('Januari'));
    }

    public function test_normalize_month_to_roman_accepts_roman_input(): void
    {
        $this->assertEquals('I', Rab::normalizeMonthToRoman('I'));
        $this->assertEquals('VI', Rab::normalizeMonthToRoman('VI'));
        $this->assertEquals('XII', Rab::normalizeMonthToRoman('XII'));
    }

    // ── Year Normalization ──

    public function test_normalize_year_keeps_valid_4_digit_year(): void
    {
        $this->assertEquals('2026', Rab::normalizeYear('2026'));
        $this->assertEquals('2025', Rab::normalizeYear('2025'));
    }

    public function test_normalize_year_falls_back_for_invalid_year(): void
    {
        $this->assertEquals(now()->format('Y'), Rab::normalizeYear('99'));
        $this->assertEquals(now()->format('Y'), Rab::normalizeYear('abc'));
    }

    // ── Parse Number Parts ──

    public function test_parse_number_parts_extracts_correctly(): void
    {
        $parts = Rab::parseNumberParts('001/RAB/SBK/VI/2026');
        $this->assertEquals(1, $parts['sequence']);
        $this->assertEquals('VI', $parts['month']);
        $this->assertEquals('2026', $parts['year']);
    }

    public function test_parse_number_parts_handles_larger_sequence(): void
    {
        $parts = Rab::parseNumberParts('015/RAB/SBK/XII/2026');
        $this->assertEquals(15, $parts['sequence']);
        $this->assertEquals('XII', $parts['month']);
        $this->assertEquals('2026', $parts['year']);
    }

    // ── RabStatus Enum ──

    public function test_rab_status_labels(): void
    {
        $this->assertEquals('Draft', RabStatus::DRAFT->label());
        $this->assertEquals('Diajukan', RabStatus::DIAJUKAN->label());
        $this->assertEquals('Disetujui Manajer', RabStatus::DISETUJUI_MANAJER->label());
        $this->assertEquals('Disetujui Direktur', RabStatus::DISETUJUI->label());
        $this->assertEquals('Ditolak', RabStatus::DITOLAK->label());
        $this->assertEquals('Selesai', RabStatus::SELESAI->label());
    }

    public function test_rab_status_colors(): void
    {
        $this->assertEquals('gray', RabStatus::DRAFT->color());
        $this->assertEquals('blue', RabStatus::DIAJUKAN->color());
        $this->assertEquals('emerald', RabStatus::DISETUJUI->color());
        $this->assertEquals('red', RabStatus::DITOLAK->color());
        $this->assertEquals('green', RabStatus::SELESAI->color());
    }

    public function test_rab_status_badge_classes(): void
    {
        $this->assertStringContainsString('bg-gray', RabStatus::DRAFT->badgeClasses());
        $this->assertStringContainsString('bg-blue', RabStatus::DIAJUKAN->badgeClasses());
        $this->assertStringContainsString('bg-red', RabStatus::DITOLAK->badgeClasses());
    }
}
