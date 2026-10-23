<?php

namespace Tests\Unit;

use App\Support\Bangla;
use PHPUnit\Framework\TestCase;

class BanglaTest extends TestCase
{
    public function test_mobile_numbers_are_normalised(): void
    {
        $this->assertSame('01711223344', Bangla::mobile('01711223344'));
        $this->assertSame('01711223344', Bangla::mobile('+880 1711-223344'));
        $this->assertSame('01711223344', Bangla::mobile('৮৮০১৭১১২২৩৩৪৪'));
        $this->assertNull(Bangla::mobile('01211223344'));
        $this->assertNull(Bangla::mobile('0171122334'));
        $this->assertNull(Bangla::mobile(null));
    }

    public function test_masked_mobile_hides_the_middle(): void
    {
        $this->assertSame('017•••••344', Bangla::maskMobile('01711223344'));
    }

    public function test_comparable_ignores_case_dots_and_spaces_but_not_abbreviations(): void
    {
        $this->assertSame(Bangla::comparable('MD.  RAFIQ'), Bangla::comparable('md rafiq'));
        $this->assertNotSame(Bangla::comparable('Md. Rafiq'), Bangla::comparable('Mohammad Rafiq'));
        $this->assertSame(Bangla::comparable('১৯৯৯'), Bangla::comparable('1999'));
    }
}
