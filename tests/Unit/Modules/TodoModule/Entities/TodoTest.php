<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\TodoModule\Entities;

use DateTime;
use Modules\TodoModule\Entities\Todo;
use PHPUnit\Framework\TestCase;

final class TodoTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $todo = new Todo();
        $this->assertFalse($todo->isCompleted());
        $this->assertSame('low', $todo->getPriority());
        $this->assertNull($todo->getDueDate());
        $this->assertNull($todo->getCompletedAt());
    }

    public function testSetAndGetTitle(): void
    {
        $todo = new Todo();
        $todo->setTitle('Buy milk');
        $this->assertSame('Buy milk', $todo->getTitle());
    }

    public function testSetAndGetDescription(): void
    {
        $todo = new Todo();
        $todo->setDescription('Some details');
        $this->assertSame('Some details', $todo->getDescription());
    }

    public function testSetAndGetUserId(): void
    {
        $todo = new Todo();
        $todo->setUserId(42);
        $this->assertSame(42, $todo->getUserId());
    }

    public function testSetCompletedSetsCompletedAt(): void
    {
        $todo = new Todo();
        $todo->setCompleted(true);

        $this->assertTrue($todo->isCompleted());
        $this->assertInstanceOf(DateTime::class, $todo->getCompletedAt());
    }

    public function testSetCompletedFalseClearsCompletedAt(): void
    {
        $todo = new Todo();
        $todo->setCompleted(true);
        $todo->setCompleted(false);

        $this->assertFalse($todo->isCompleted());
        $this->assertNull($todo->getCompletedAt());
    }

    public function testSetCompletedTrueAgainDoesNotOverwriteCompletedAt(): void
    {
        $todo = new Todo();
        $todo->setCompleted(true);
        $firstCompletedAt = $todo->getCompletedAt();

        sleep(1);
        $todo->setCompleted(true);

        $this->assertSame($firstCompletedAt, $todo->getCompletedAt());
    }

    public function testValidPriorities(): void
    {
        $todo = new Todo();

        foreach (['low', 'medium', 'high'] as $priority) {
            $todo->setPriority($priority);
            $this->assertSame($priority, $todo->getPriority());
        }
    }

    public function testInvalidPriorityThrowsException(): void
    {
        $todo = new Todo();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Priority must be low, medium, or high');

        $todo->setPriority('urgent');
    }

    public function testSetAndGetDueDate(): void
    {
        $todo = new Todo();
        $dueDate = new DateTime('+1 day');
        $todo->setDueDate($dueDate);

        $this->assertSame($dueDate, $todo->getDueDate());
    }

    public function testIsOverdueWhenPastDue(): void
    {
        $todo = new Todo();
        $todo->setDueDate(new DateTime('-1 day'));

        $this->assertTrue($todo->isOverdue());
    }

    public function testIsNotOverdueWhenCompleted(): void
    {
        $todo = new Todo();
        $todo->setDueDate(new DateTime('-1 day'));
        $todo->setCompleted(true);

        $this->assertFalse($todo->isOverdue());
    }

    public function testIsNotOverdueWithoutDueDate(): void
    {
        $todo = new Todo();
        $this->assertFalse($todo->isOverdue());
    }

    public function testPriorityBadgeClass(): void
    {
        $todo = new Todo();

        $todo->setPriority('low');
        $this->assertSame('success', $todo->getPriorityBadgeClass());

        $todo->setPriority('medium');
        $this->assertSame('warning', $todo->getPriorityBadgeClass());

        $todo->setPriority('high');
        $this->assertSame('danger', $todo->getPriorityBadgeClass());
    }
}
