<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Modules\TodoModule\Repositories;

use Doctrine\ORM\EntityRepository;
use Modules\TodoModule\Entities\Todo;

class TodoRepository extends EntityRepository
{
    /**
     * Find all todos for a specific user
     *
     * @param int $userId
     * @return Todo[]
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('t.completed', 'ASC')
            ->addOrderBy('t.dueDate', 'ASC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find incomplete todos for a user
     *
     * @param int $userId
     * @return Todo[]
     */
    public function findIncompleteByUser(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->andWhere('t.completed = :completed')
            ->setParameter('userId', $userId)
            ->setParameter('completed', false)
            ->orderBy('t.dueDate', 'ASC')
            ->addOrderBy('t.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find completed todos for a user
     *
     * @param int $userId
     * @return Todo[]
     */
    public function findCompletedByUser(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->andWhere('t.completed = :completed')
            ->setParameter('userId', $userId)
            ->setParameter('completed', true)
            ->orderBy('t.completedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find overdue todos for a user
     *
     * @param int $userId
     * @return Todo[]
     */
    public function findOverdueByUser(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->andWhere('t.completed = :completed')
            ->andWhere('t.dueDate < :now')
            ->setParameter('userId', $userId)
            ->setParameter('completed', false)
            ->setParameter('now', new \DateTime())
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find todos by priority for a user
     *
     * @param int $userId
     * @param string $priority
     * @return Todo[]
     */
    public function findByPriority(int $userId, string $priority): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->andWhere('t.priority = :priority')
            ->andWhere('t.completed = :completed')
            ->setParameter('userId', $userId)
            ->setParameter('priority', $priority)
            ->setParameter('completed', false)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count incomplete todos for a user
     *
     * @param int $userId
     * @return int
     */
    public function countIncomplete(int $userId): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.userId = :userId')
            ->andWhere('t.completed = :completed')
            ->setParameter('userId', $userId)
            ->setParameter('completed', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count overdue todos for a user
     *
     * @param int $userId
     * @return int
     */
    public function countOverdue(int $userId): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.userId = :userId')
            ->andWhere('t.completed = :completed')
            ->andWhere('t.dueDate < :now')
            ->setParameter('userId', $userId)
            ->setParameter('completed', false)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
