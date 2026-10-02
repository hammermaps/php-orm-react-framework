<?php

declare(strict_types=1);

namespace Tests\Unit\Entities;

use Entities\Group;
use Entities\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testDefaultLocale(): void
    {
        $user = new User();
        $this->assertSame('en_US', $user->getLocale());
    }

    public function testSetAndGetName(): void
    {
        $user = new User();
        $user->setName('John Doe');
        $this->assertSame('John Doe', $user->getName());
    }

    public function testSetAndGetLocale(): void
    {
        $user = new User();
        $user->setLocale('de_DE');
        $this->assertSame('de_DE', $user->getLocale());
    }

    public function testPasswordIsHashedAndVerified(): void
    {
        $user = new User();
        $user->setPassword('secret');

        $this->assertTrue($user->isValidPassword('secret'));
        $this->assertFalse($user->isValidPassword('wrong'));
    }

    public function testSetAndGetGroup(): void
    {
        $user = new User();
        $group = new Group(['name' => 'Admins', 'role' => Group::ROLE_ADMIN]);

        $user->setGroup($group);
        $this->assertSame($group, $user->getGroup());
    }

    public function testSetAndGetBy(): void
    {
        $user = new User();
        $creator = new User(['name' => 'Creator']);

        $user->setBy($creator);
        $this->assertSame($creator, $user->getBy());
    }

    public function testGetCreatedReturnsDateTimeWhenNull(): void
    {
        $user = new User();
        $this->assertInstanceOf(\DateTime::class, $user->getCreated());
    }

    public function testGetUpdatedReturnsDateTimeWhenNull(): void
    {
        $user = new User();
        $this->assertInstanceOf(\DateTime::class, $user->getUpdated());
    }

    public function testGetChangedReturnsDateTimeWhenNull(): void
    {
        $user = new User();
        $this->assertInstanceOf(\DateTime::class, $user->getChanged());
    }

    public function testDefaultAvatarIsNotEmpty(): void
    {
        $user = new User();
        $this->assertNotEmpty($user->getAvatar());
        $this->assertStringStartsWith('data:image/png;base64,', $user->getAvatar());
    }

    public function testMapClassViaConstructor(): void
    {
        $user = new User(['name' => 'Jane', 'locale' => 'en_GB']);
        $this->assertSame('Jane', $user->getName());
        $this->assertSame('en_GB', $user->getLocale());
    }
}
