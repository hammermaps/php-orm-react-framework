<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Helpers\StringHelper;
use PHPUnit\Framework\TestCase;

final class StringHelperTest extends TestCase
{
    public function testDecamelize(): void
    {
        $helper = StringHelper::init('HelloWorld');
        $this->assertSame('hello_world', $helper->decamelize()->getString());

        $helper = StringHelper::init('ABCDef');
        $this->assertSame('a_b_c_def', $helper->decamelize()->getString());
    }

    public function testCamelize(): void
    {
        $helper = StringHelper::init('hello_world');
        $this->assertSame('HelloWorld', $helper->camelize()->getString());
    }

    public function testCamelizeLcFirst(): void
    {
        $helper = StringHelper::init('hello_world');
        $this->assertSame('helloWorld', $helper->camelizeLcFirst()->getString());
    }

    public function testReplace(): void
    {
        $helper = StringHelper::init('hello world');
        $this->assertSame('hello-world', $helper->replace(' ', '-')->getString());

        $helper = StringHelper::init('foo bar baz');
        $this->assertSame('foo-bar-baz', $helper->replace(' ', '-')->getString());
    }

    public function testToLower(): void
    {
        $helper = StringHelper::init('HELLO');
        $this->assertSame('hello', $helper->toLower()->getString());
    }

    public function testHasFilter(): void
    {
        $helper = StringHelper::init('hello world');
        $this->assertTrue($helper->hasFilter('world'));
        $this->assertFalse($helper->hasFilter('foo'));
    }

    public function testLcFirst(): void
    {
        $helper = StringHelper::init('Hello');
        $this->assertSame('hello', $helper->lcFirst()->getString());
    }

    public function testUcFirst(): void
    {
        $helper = StringHelper::init('hello');
        $this->assertSame('Hello', $helper->ucFirst()->getString());
    }

    public function testRmNamespace(): void
    {
        $helper = StringHelper::init('\\Vendor\\Package\\ClassName');
        $this->assertSame('ClassName', $helper->rmNamespace()->getString());
    }

    public function testInitReturnsSameInstanceForSameString(): void
    {
        $first = StringHelper::init('same');
        $second = StringHelper::init('same');
        $this->assertSame($first, $second);
    }
}
