<?php

declare(strict_types=1);

namespace Tests\Unit\Entities;

use Entities\Group;
use Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class GroupTest extends TestCase
{
    public function testDefaultRoleIsAny(): void
    {
        $group = new Group();
        $this->assertSame(Group::ROLE_ANY, $group->getRole());
    }

    public function testSetValidRoles(): void
    {
        $group = new Group();

        $validRoles = [
            Group::ROLE_ROOT,
            Group::ROLE_ADMIN,
            Group::ROLE_RESELLER,
            Group::ROLE_USER,
            Group::ROLE_ANY,
        ];

        foreach ($validRoles as $role) {
            $group->setRole($role);
            $this->assertSame($role, $group->getRole());
        }
    }

    public function testSetInvalidRoleThrowsException(): void
    {
        $group = new Group();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role');

        $group->setRole(999);
    }

    public function testSetAndGetName(): void
    {
        $group = new Group();
        $group->setName('Admins');
        $this->assertSame('Admins', $group->getName());
    }

    public function testGetCreatedReturnsDateTimeWhenNull(): void
    {
        $group = new Group();
        $this->assertInstanceOf(\DateTime::class, $group->getCreated());
    }

    public function testGetUpdatedReturnsDateTimeWhenNull(): void
    {
        $group = new Group();
        $this->assertInstanceOf(\DateTime::class, $group->getUpdated());
    }

    public function testGetChangedReturnsDateTimeWhenNull(): void
    {
        $group = new Group();
        $this->assertInstanceOf(\DateTime::class, $group->getChanged());
    }

    public function testGetUsersReturnsCollection(): void
    {
        $group = new Group();
        $this->assertInstanceOf(\Doctrine\Common\Collections\ArrayCollection::class, $group->getUsers());
    }

    public function testMapClassViaConstructor(): void
    {
        $group = new Group(['name' => 'TestGroup', 'role' => Group::ROLE_ADMIN]);
        $this->assertSame('TestGroup', $group->getName());
        $this->assertSame(Group::ROLE_ADMIN, $group->getRole());
    }
}
