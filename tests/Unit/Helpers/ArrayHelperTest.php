<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Helpers\ArrayHelper;
use PHPUnit\Framework\TestCase;

final class ArrayHelperTest extends TestCase
{
    public function testGetExistingKey(): void
    {
        $helper = ArrayHelper::init(['foo' => 'bar']);
        $this->assertSame('bar', $helper->get('foo'));
    }

    public function testGetMissingKeyReturnsDefault(): void
    {
        $helper = ArrayHelper::init(['foo' => 'bar']);
        $this->assertNull($helper->get('baz'));
        $this->assertSame('default', $helper->get('baz', 'default'));
    }

    public function testGetArray(): void
    {
        $data = ['a' => 1, 'b' => 2];
        $helper = ArrayHelper::init($data);
        $this->assertSame($data, $helper->getArray());
    }

    public function testAppendMergesArrays(): void
    {
        $helper = ArrayHelper::init(['a' => 1]);
        $helper->append(['b' => 2]);
        $this->assertSame(['a' => 1, 'b' => 2], $helper->getArray());
    }

    public function testMapClassCallsSetters(): void
    {
        $helper = ArrayHelper::init(['name' => 'John', 'age' => 30]);
        $target = new class {
            public string $name = '';
            public int $age = 0;

            public function setName(string $name): void
            {
                $this->name = $name;
            }

            public function setAge(int $age): void
            {
                $this->age = $age;
            }
        };

        $helper->mapClass($target);

        $this->assertSame('John', $target->name);
        $this->assertSame(30, $target->age);
    }

    public function testInitReturnsSameInstanceForSameArray(): void
    {
        $first = ArrayHelper::init(['same' => true]);
        $second = ArrayHelper::init(['same' => true]);
        $this->assertSame($first, $second);
    }
}
