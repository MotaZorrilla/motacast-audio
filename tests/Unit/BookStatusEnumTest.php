<?php

namespace Tests\Unit;

use App\Enums\BookStatus;
use App\Enums\ChapterStatus;
use PHPUnit\Framework\TestCase;

class BookStatusEnumTest extends TestCase
{
    public function test_book_status_cases_and_values(): void
    {
        $this->assertEquals('pending', BookStatus::Pending->value);
        $this->assertEquals('extracting', BookStatus::Extracting->value);
        $this->assertEquals('synthesizing', BookStatus::Synthesizing->value);
        $this->assertEquals('ready', BookStatus::Ready->value);
        $this->assertEquals('failed', BookStatus::Failed->value);

        $values = BookStatus::values();
        $this->assertContains('pending', $values);
        $this->assertContains('ready', $values);
        $this->assertCount(5, $values);
    }

    public function test_book_status_helper_methods(): void
    {
        $this->assertTrue(BookStatus::Pending->isProcessing());
        $this->assertTrue(BookStatus::Extracting->isProcessing());
        $this->assertTrue(BookStatus::Synthesizing->isProcessing());
        $this->assertFalse(BookStatus::Ready->isProcessing());
        $this->assertFalse(BookStatus::Failed->isProcessing());

        $this->assertTrue(BookStatus::Ready->isTerminal());
        $this->assertTrue(BookStatus::Failed->isTerminal());
        $this->assertFalse(BookStatus::Pending->isTerminal());

        $this->assertEquals('Listo para escuchar', BookStatus::Ready->label());
    }

    public function test_chapter_status_cases_and_values(): void
    {
        $this->assertEquals('pending', ChapterStatus::Pending->value);
        $this->assertEquals('synthesizing', ChapterStatus::Synthesizing->value);
        $this->assertEquals('ready', ChapterStatus::Ready->value);
        $this->assertEquals('failed', ChapterStatus::Failed->value);

        $values = ChapterStatus::values();
        $this->assertContains('ready', $values);
        $this->assertCount(4, $values);
        $this->assertEquals('Listo', ChapterStatus::Ready->label());
    }
}
