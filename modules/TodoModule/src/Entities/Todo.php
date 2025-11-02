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

namespace Modules\TodoModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * Class Todo
 * @package Modules\TodoModule\Entities
 * @ORM\Entity(repositoryClass="Modules\TodoModule\Repositories\TodoRepository")
 * @ORM\Table(name="todos")
 */
class Todo
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $title;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private string $description = "";

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $completed = false;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, options={"default": "low"})
     */
    private string $priority = "low";

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $dueDate = null;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $userId;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private DateTime $createdAt;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private DateTime $updatedAt;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $completedAt = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;
        if ($completed && $this->completedAt === null) {
            $this->completedAt = new DateTime();
        } elseif (!$completed) {
            $this->completedAt = null;
        }
        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): self
    {
        if (!in_array($priority, ['low', 'medium', 'high'])) {
            throw new \InvalidArgumentException('Priority must be low, medium, or high');
        }
        $this->priority = $priority;
        return $this;
    }

    public function getDueDate(): ?DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(?DateTime $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getCompletedAt(): ?DateTime
    {
        return $this->completedAt;
    }

    public function isOverdue(): bool
    {
        if ($this->completed || $this->dueDate === null) {
            return false;
        }
        return $this->dueDate < new DateTime();
    }

    public function getPriorityBadgeClass(): string
    {
        return [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
        ][$this->priority] ?? 'secondary';
    }
}
