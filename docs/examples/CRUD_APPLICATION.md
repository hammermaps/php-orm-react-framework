# Beispiel: Vollständige CRUD-Anwendung

Dieses Tutorial zeigt, wie Sie eine vollständige CRUD-Anwendung (Create, Read, Update, Delete) mit dem PHP ORM React Framework erstellen.

## Was wir bauen

Ein Aufgabenverwaltungssystem (Task Manager) mit:
- Aufgaben erstellen, anzeigen, bearbeiten und löschen
- Kategorien und Prioritäten
- Fälligkeitsdaten
- Status-Tracking
- Filter und Suche

## Projektstruktur

```
modules/TaskModule/
├── src/
│   ├── Controllers/
│   │   └── TaskController.php
│   ├── Entities/
│   │   ├── Task.php
│   │   └── Category.php
│   └── Repositories/
│       └── TaskRepository.php
└── views/
    └── TaskController/
        ├── indexAction.tpl.twig
        ├── createAction.tpl.twig
        ├── editAction.tpl.twig
        └── viewAction.tpl.twig
```

## Schritt 1: Entities erstellen

### Task Entity

**`modules/TaskModule/src/Entities/Task.php`**

```php
<?php
namespace Modules\TaskModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="Modules\TaskModule\Repositories\TaskRepository")
 * @ORM\Table(name="tasks", indexes={
 *     @ORM\Index(name="idx_status", columns={"status"}),
 *     @ORM\Index(name="idx_priority", columns={"priority"}),
 *     @ORM\Index(name="idx_due_date", columns={"due_date"}),
 *     @ORM\Index(name="idx_user_id", columns={"user_id"})
 * })
 */
class Task
{
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=20, options={"default": "open"})
     */
    private string $status = self::STATUS_OPEN;

    /**
     * @ORM\Column(type="string", length=20, options={"default": "medium"})
     */
    private string $priority = self::PRIORITY_MEDIUM;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="due_date")
     */
    private ?DateTime $dueDate = null;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private ?Category $category = null;

    /**
     * @ORM\Column(type="integer", name="user_id")
     */
    private int $userId;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_at")
     */
    private DateTime $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="updated_at")
     */
    private DateTime $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="completed_at")
     */
    private ?DateTime $completedAt = null;

    // Getter und Setter

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $oldStatus = $this->status;
        $this->status = $status;
        
        // Abschlussdatum setzen
        if ($status === self::STATUS_COMPLETED && $oldStatus !== self::STATUS_COMPLETED) {
            $this->completedAt = new DateTime();
        } elseif ($status !== self::STATUS_COMPLETED) {
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
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

    // Hilfsmethoden

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isOverdue(): bool
    {
        if (!$this->dueDate || $this->isCompleted()) {
            return false;
        }
        return $this->dueDate < new DateTime('today');
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'Offen',
            self::STATUS_IN_PROGRESS => 'In Bearbeitung',
            self::STATUS_COMPLETED => 'Abgeschlossen',
            self::STATUS_CANCELLED => 'Abgebrochen',
            default => $this->status
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'secondary',
            self::STATUS_IN_PROGRESS => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary'
        };
    }

    public function getPriorityLabel(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Niedrig',
            self::PRIORITY_MEDIUM => 'Mittel',
            self::PRIORITY_HIGH => 'Hoch',
            self::PRIORITY_URGENT => 'Dringend',
            default => $this->priority
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'info',
            self::PRIORITY_MEDIUM => 'secondary',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_URGENT => 'danger',
            default => 'secondary'
        };
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_OPEN => 'Offen',
            self::STATUS_IN_PROGRESS => 'In Bearbeitung',
            self::STATUS_COMPLETED => 'Abgeschlossen',
            self::STATUS_CANCELLED => 'Abgebrochen'
        ];
    }

    public static function getPriorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Niedrig',
            self::PRIORITY_MEDIUM => 'Mittel',
            self::PRIORITY_HIGH => 'Hoch',
            self::PRIORITY_URGENT => 'Dringend'
        ];
    }
}
```

### Category Entity

**`modules/TaskModule/src/Entities/Category.php`**

```php
<?php
namespace Modules\TaskModule\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="task_categories")
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=7, options={"default": "#6c757d"})
     */
    private string $color = '#6c757d';

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;
        return $this;
    }
}
```

## Schritt 2: Repository erstellen

**`modules/TaskModule/src/Repositories/TaskRepository.php`**

```php
<?php
namespace Modules\TaskModule\Repositories;

use Doctrine\ORM\EntityRepository;
use Modules\TaskModule\Entities\Task;

class TaskRepository extends EntityRepository
{
    /**
     * Alle Aufgaben eines Benutzers
     */
    public function findByUser(int $userId, ?string $status = null, ?string $priority = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->setParameter('userId', $userId);
        
        if ($status) {
            $qb->andWhere('t.status = :status')
               ->setParameter('status', $status);
        }
        
        if ($priority) {
            $qb->andWhere('t.priority = :priority')
               ->setParameter('priority', $priority);
        }
        
        return $qb->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Offene Aufgaben
     */
    public function findOpenByUser(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->andWhere('t.status != :completed')
            ->andWhere('t.status != :cancelled')
            ->setParameter('userId', $userId)
            ->setParameter('completed', Task::STATUS_COMPLETED)
            ->setParameter('cancelled', Task::STATUS_CANCELLED)
            ->orderBy('t.priority', 'DESC')
            ->addOrderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Überfällige Aufgaben
     */
    public function findOverdueByUser(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->andWhere('t.dueDate < :today')
            ->andWhere('t.status != :completed')
            ->andWhere('t.status != :cancelled')
            ->setParameter('userId', $userId)
            ->setParameter('today', new \DateTime('today'))
            ->setParameter('completed', Task::STATUS_COMPLETED)
            ->setParameter('cancelled', Task::STATUS_CANCELLED)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Aufgaben nach Kategorie
     */
    public function findByCategory(int $userId, int $categoryId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->andWhere('t.category = :categoryId')
            ->setParameter('userId', $userId)
            ->setParameter('categoryId', $categoryId)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Suche
     */
    public function search(int $userId, string $query): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->andWhere('t.title LIKE :query OR t.description LIKE :query')
            ->setParameter('userId', $userId)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Statistiken
     */
    public function getStatistics(int $userId): array
    {
        $em = $this->getEntityManager();
        
        $total = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
        
        $completed = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.userId = :userId')
            ->andWhere('t.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', Task::STATUS_COMPLETED)
            ->getQuery()
            ->getSingleScalarResult();
        
        $overdue = count($this->findOverdueByUser($userId));
        
        return [
            'total' => (int) $total,
            'completed' => (int) $completed,
            'open' => (int) $total - (int) $completed,
            'overdue' => $overdue
        ];
    }
}
```

## Schritt 3: Controller erstellen

**`modules/TaskModule/src/Controllers/TaskController.php`**

```php
<?php
namespace Modules\TaskModule\Controllers;

use Annotations\Access;
use Annotations\Navigation;
use Annotations\SubNavigation;
use Annotations\SubRoute;
use Annotations\SubRoutes;
use Controllers\RestrictedFrontController;
use Entities\Group;
use Modules\TaskModule\Entities\Task;
use Modules\TaskModule\Entities\Category;
use Modules\TaskModule\Repositories\TaskRepository;
use DateTime;

/**
 * Aufgabenverwaltung
 * 
 * @Navigation(text="Aufgaben", position="sidebar", icon="cil-task")
 * @Access(role=Group::ROLE_USER)
 */
class TaskController extends RestrictedFrontController
{
    /**
     * Aufgabenliste
     * 
     * @SubNavigation(text="Alle Aufgaben", icon="cil-list")
     */
    public function indexAction(): void
    {
        $userId = $this->getSessionHandler()->getUser()->getId();
        $status = $_GET['status'] ?? null;
        $priority = $_GET['priority'] ?? null;
        
        $em = $this->getDoctrineService()->getEntityManager();
        /** @var TaskRepository $repo */
        $repo = $em->getRepository(Task::class);
        
        $tasks = $repo->findByUser($userId, $status, $priority);
        $stats = $repo->getStatistics($userId);
        $categories = $em->getRepository(Category::class)->findAll();
        
        $this->getView()->assign('tasks', $tasks);
        $this->getView()->assign('stats', $stats);
        $this->getView()->assign('categories', $categories);
        $this->getView()->assign('currentStatus', $status);
        $this->getView()->assign('currentPriority', $priority);
        $this->getView()->assign('statusOptions', Task::getStatusOptions());
        $this->getView()->assign('priorityOptions', Task::getPriorityOptions());
        
        parent::indexAction();
    }
    
    /**
     * Filter-Optionen
     * 
     * @SubNavigation(text="Filter", icon="cil-filter")
     * @SubRoutes(routes={
     *     @SubRoute(text="Alle", icon="cil-list", hrefQueryAddition={"status": ""}),
     *     @SubRoute(text="Offen", icon="cil-clock", hrefQueryAddition={"status": "open"}),
     *     @SubRoute(text="In Bearbeitung", icon="cil-cog", hrefQueryAddition={"status": "in_progress"}),
     *     @SubRoute(text="Abgeschlossen", icon="cil-check-circle", hrefQueryAddition={"status": "completed"}),
     *     @SubRoute(text="Dringend", icon="cil-warning", hrefQueryAddition={"priority": "urgent"})
     * })
     */
    public function filterAction(): void
    {
        // Wird über indexAction mit Parametern behandelt
        $this->indexAction();
    }
    
    /**
     * Neue Aufgabe erstellen
     * 
     * @SubNavigation(text="Neue Aufgabe", icon="cil-plus")
     */
    public function createAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveTask();
            return;
        }
        
        $em = $this->getDoctrineService()->getEntityManager();
        $categories = $em->getRepository(Category::class)->findAll();
        
        $this->getView()->assign('categories', $categories);
        $this->getView()->assign('priorityOptions', Task::getPriorityOptions());
        
        parent::indexAction();
    }
    
    /**
     * Aufgabe anzeigen
     * 
     * @SubNavigation(text="Details", icon="cil-info", hidden=true, requiredGetParams={"id"})
     */
    public function viewAction(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $task = $this->getTask($id);
        
        if (!$task) {
            $this->render404();
            return;
        }
        
        $this->getView()->assign('task', $task);
        parent::indexAction();
    }
    
    /**
     * Aufgabe bearbeiten
     * 
     * @SubNavigation(text="Bearbeiten", icon="cil-pencil", hidden=true, requiredGetParams={"id"})
     */
    public function editAction(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $task = $this->getTask($id);
        
        if (!$task) {
            $this->render404();
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateTask($task);
            return;
        }
        
        $em = $this->getDoctrineService()->getEntityManager();
        $categories = $em->getRepository(Category::class)->findAll();
        
        $this->getView()->assign('task', $task);
        $this->getView()->assign('categories', $categories);
        $this->getView()->assign('statusOptions', Task::getStatusOptions());
        $this->getView()->assign('priorityOptions', Task::getPriorityOptions());
        
        parent::indexAction();
    }
    
    /**
     * Aufgabe löschen
     * 
     * @SubNavigation(text="Löschen", hidden=true, requiredGetParams={"id"})
     */
    public function deleteAction(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $task = $this->getTask($id);
        
        if (!$task) {
            $this->render404();
            return;
        }
        
        try {
            $em = $this->getDoctrineService()->getEntityManager();
            $em->remove($task);
            $em->flush();
            
            $this->getFlashHandler()->addFlashMessage('success', 'Aufgabe wurde gelöscht.');
            
        } catch (\Exception $e) {
            $this->getLoggerService()->error('Fehler beim Löschen', ['error' => $e->getMessage()]);
            $this->getFlashHandler()->addFlashMessage('danger', 'Fehler beim Löschen der Aufgabe.');
        }
        
        $this->redirect('taskModule', 'task', 'index');
    }
    
    /**
     * Status schnell ändern
     * 
     * @SubNavigation(text="Status", hidden=true, requiredGetParams={"id", "status"})
     */
    public function statusAction(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $newStatus = $_GET['status'] ?? '';
        
        $task = $this->getTask($id);
        
        if (!$task || !array_key_exists($newStatus, Task::getStatusOptions())) {
            $this->render404();
            return;
        }
        
        try {
            $task->setStatus($newStatus);
            $this->getDoctrineService()->getEntityManager()->flush();
            
            $this->getFlashHandler()->addFlashMessage('success', 'Status wurde aktualisiert.');
            
        } catch (\Exception $e) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Fehler beim Aktualisieren.');
        }
        
        $this->redirect('taskModule', 'task', 'index');
    }
    
    /**
     * Suche
     * 
     * @SubNavigation(text="Suche", icon="cil-search", hidden=true)
     */
    public function searchAction(): void
    {
        $query = trim($_GET['q'] ?? '');
        $userId = $this->getSessionHandler()->getUser()->getId();
        
        $tasks = [];
        if (strlen($query) >= 2) {
            $em = $this->getDoctrineService()->getEntityManager();
            /** @var TaskRepository $repo */
            $repo = $em->getRepository(Task::class);
            $tasks = $repo->search($userId, $query);
        }
        
        $this->getView()->assign('tasks', $tasks);
        $this->getView()->assign('query', $query);
        $this->getView()->assign('statusOptions', Task::getStatusOptions());
        
        parent::indexAction();
    }
    
    // ===== Private Methoden =====
    
    private function getTask(int $id): ?Task
    {
        $em = $this->getDoctrineService()->getEntityManager();
        $task = $em->find(Task::class, $id);
        
        // Nur eigene Aufgaben
        if ($task && $task->getUserId() !== $this->getSessionHandler()->getUser()->getId()) {
            return null;
        }
        
        return $task;
    }
    
    private function saveTask(): void
    {
        $data = $this->getFormData();
        
        if (!$this->validateFormData($data)) {
            parent::indexAction();
            return;
        }
        
        try {
            $em = $this->getDoctrineService()->getEntityManager();
            
            $task = new Task();
            $task->setTitle($data['title']);
            $task->setDescription($data['description']);
            $task->setPriority($data['priority']);
            $task->setUserId($this->getSessionHandler()->getUser()->getId());
            
            if ($data['due_date']) {
                $task->setDueDate(new DateTime($data['due_date']));
            }
            
            if ($data['category_id']) {
                $category = $em->find(Category::class, $data['category_id']);
                $task->setCategory($category);
            }
            
            $em->persist($task);
            $em->flush();
            
            $this->getFlashHandler()->addFlashMessage('success', 'Aufgabe wurde erstellt.');
            $this->redirect('taskModule', 'task', 'index');
            
        } catch (\Exception $e) {
            $this->getLoggerService()->error('Fehler beim Erstellen', ['error' => $e->getMessage()]);
            $this->getFlashHandler()->addFlashMessage('danger', 'Fehler beim Erstellen der Aufgabe.');
            parent::indexAction();
        }
    }
    
    private function updateTask(Task $task): void
    {
        $data = $this->getFormData();
        
        if (!$this->validateFormData($data)) {
            parent::indexAction();
            return;
        }
        
        try {
            $em = $this->getDoctrineService()->getEntityManager();
            
            $task->setTitle($data['title']);
            $task->setDescription($data['description']);
            $task->setPriority($data['priority']);
            $task->setStatus($data['status'] ?? $task->getStatus());
            
            if ($data['due_date']) {
                $task->setDueDate(new DateTime($data['due_date']));
            } else {
                $task->setDueDate(null);
            }
            
            if ($data['category_id']) {
                $category = $em->find(Category::class, $data['category_id']);
                $task->setCategory($category);
            } else {
                $task->setCategory(null);
            }
            
            $em->flush();
            
            $this->getFlashHandler()->addFlashMessage('success', 'Aufgabe wurde aktualisiert.');
            $this->redirect('taskModule', 'task', 'index');
            
        } catch (\Exception $e) {
            $this->getLoggerService()->error('Fehler beim Aktualisieren', ['error' => $e->getMessage()]);
            $this->getFlashHandler()->addFlashMessage('danger', 'Fehler beim Aktualisieren.');
            parent::indexAction();
        }
    }
    
    private function getFormData(): array
    {
        return [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'priority' => $_POST['priority'] ?? Task::PRIORITY_MEDIUM,
            'status' => $_POST['status'] ?? null,
            'due_date' => $_POST['due_date'] ?? null,
            'category_id' => (int) ($_POST['category_id'] ?? 0) ?: null
        ];
    }
    
    private function validateFormData(array $data): bool
    {
        if (empty($data['title'])) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Titel ist erforderlich.');
            return false;
        }
        
        if (!array_key_exists($data['priority'], Task::getPriorityOptions())) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Ungültige Priorität.');
            return false;
        }
        
        return true;
    }
}
```

## Schritt 4: Views erstellen

### Aufgabenliste

**`modules/TaskModule/views/TaskController/indexAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    {# Statistiken #}
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-primary">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ stats.total }}</h3>
                            <small>Gesamt</small>
                        </div>
                        <i class="cil-task" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-warning">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ stats.open }}</h3>
                            <small>Offen</small>
                        </div>
                        <i class="cil-clock" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-success">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ stats.completed }}</h3>
                            <small>Erledigt</small>
                        </div>
                        <i class="cil-check-circle" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-danger">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ stats.overdue }}</h3>
                            <small>Überfällig</small>
                        </div>
                        <i class="cil-warning" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {# Filter und Suche #}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <form method="GET" action="?module=taskModule&controller=task&action=search">
                        <div class="input-group">
                            <input type="text" 
                                   name="q" 
                                   class="form-control" 
                                   placeholder="Suchen..."
                                   value="">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="cil-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4">
                    <select class="form-control" onchange="location = this.value;">
                        <option value="?module=taskModule&controller=task&action=index">Alle Status</option>
                        {% for key, label in statusOptions %}
                        <option value="?module=taskModule&controller=task&action=index&status={{ key }}"
                                {% if currentStatus == key %}selected{% endif %}>
                            {{ label }}
                        </option>
                        {% endfor %}
                    </select>
                </div>
                <div class="col-md-4 text-right">
                    <a href="?module=taskModule&controller=task&action=create" class="btn btn-success">
                        <i class="cil-plus"></i> Neue Aufgabe
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    {# Aufgabenliste #}
    <div class="card">
        <div class="card-header">
            <i class="cil-list"></i> Aufgaben
            <span class="badge badge-secondary">{{ tasks|length }}</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40%;">Titel</th>
                        <th>Priorität</th>
                        <th>Status</th>
                        <th>Fällig</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    {% for task in tasks %}
                    <tr class="{% if task.isOverdue() %}table-danger{% elseif task.isCompleted() %}table-success{% endif %}">
                        <td>
                            <a href="?module=taskModule&controller=task&action=view&id={{ task.id }}">
                                <strong>{{ task.title }}</strong>
                            </a>
                            {% if task.category %}
                                <span class="badge" style="background-color: {{ task.category.color }}; color: white;">
                                    {{ task.category.name }}
                                </span>
                            {% endif %}
                            {% if task.isOverdue() %}
                                <span class="badge badge-danger">Überfällig</span>
                            {% endif %}
                        </td>
                        <td>
                            <span class="badge badge-{{ task.priorityBadgeClass }}">
                                {{ task.priorityLabel }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ task.statusBadgeClass }}">
                                {{ task.statusLabel }}
                            </span>
                        </td>
                        <td>
                            {% if task.dueDate %}
                                {{ task.dueDate|date('d.m.Y') }}
                            {% else %}
                                <span class="text-muted">-</span>
                            {% endif %}
                        </td>
                        <td>
                            {% if not task.isCompleted() %}
                            <a href="?module=taskModule&controller=task&action=status&id={{ task.id }}&status=completed" 
                               class="btn btn-sm btn-success" 
                               title="Als erledigt markieren">
                                <i class="cil-check"></i>
                            </a>
                            {% endif %}
                            <a href="?module=taskModule&controller=task&action=edit&id={{ task.id }}" 
                               class="btn btn-sm btn-primary" 
                               title="Bearbeiten">
                                <i class="cil-pencil"></i>
                            </a>
                            <a href="?module=taskModule&controller=task&action=delete&id={{ task.id }}" 
                               class="btn btn-sm btn-danger" 
                               title="Löschen"
                               onclick="return confirm('Aufgabe wirklich löschen?')">
                                <i class="cil-trash"></i>
                            </a>
                        </td>
                    </tr>
                    {% else %}
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="cil-check-circle" style="font-size: 3rem;"></i>
                            <p class="mt-2">Keine Aufgaben gefunden.</p>
                            <a href="?module=taskModule&controller=task&action=create" class="btn btn-primary">
                                Erste Aufgabe erstellen
                            </a>
                        </td>
                    </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
</div>
{% endblock %}
```

### Aufgabe erstellen

**`modules/TaskModule/views/TaskController/createAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <i class="cil-plus"></i> Neue Aufgabe erstellen
                </div>
                <div class="card-body">
                    <form method="POST" action="?module=taskModule&controller=task&action=create">
                        <div class="form-group">
                            <label for="title">Titel *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   required
                                   autofocus>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Beschreibung</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="priority">Priorität</label>
                                    <select class="form-control" id="priority" name="priority">
                                        {% for key, label in priorityOptions %}
                                        <option value="{{ key }}" {% if key == 'medium' %}selected{% endif %}>
                                            {{ label }}
                                        </option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category_id">Kategorie</label>
                                    <select class="form-control" id="category_id" name="category_id">
                                        <option value="">Keine</option>
                                        {% for cat in categories %}
                                        <option value="{{ cat.id }}">{{ cat.name }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="due_date">Fällig am</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="due_date" 
                                           name="due_date">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="cil-save"></i> Aufgabe erstellen
                            </button>
                            <a href="?module=taskModule&controller=task&action=index" class="btn btn-secondary">
                                Abbrechen
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

### Aufgabe bearbeiten

**`modules/TaskModule/views/TaskController/editAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <i class="cil-pencil"></i> Aufgabe bearbeiten
                </div>
                <div class="card-body">
                    <form method="POST" action="?module=taskModule&controller=task&action=edit&id={{ task.id }}">
                        <div class="form-group">
                            <label for="title">Titel *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   value="{{ task.title }}"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Beschreibung</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4">{{ task.description }}</textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        {% for key, label in statusOptions %}
                                        <option value="{{ key }}" {% if task.status == key %}selected{% endif %}>
                                            {{ label }}
                                        </option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="priority">Priorität</label>
                                    <select class="form-control" id="priority" name="priority">
                                        {% for key, label in priorityOptions %}
                                        <option value="{{ key }}" {% if task.priority == key %}selected{% endif %}>
                                            {{ label }}
                                        </option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="category_id">Kategorie</label>
                                    <select class="form-control" id="category_id" name="category_id">
                                        <option value="">Keine</option>
                                        {% for cat in categories %}
                                        <option value="{{ cat.id }}" 
                                                {% if task.category and task.category.id == cat.id %}selected{% endif %}>
                                            {{ cat.name }}
                                        </option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="due_date">Fällig am</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="due_date" 
                                           name="due_date"
                                           value="{{ task.dueDate ? task.dueDate|date('Y-m-d') : '' }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="cil-save"></i> Speichern
                            </button>
                            <a href="?module=taskModule&controller=task&action=index" class="btn btn-secondary">
                                Abbrechen
                            </a>
                            <a href="?module=taskModule&controller=task&action=delete&id={{ task.id }}" 
                               class="btn btn-danger float-right"
                               onclick="return confirm('Aufgabe wirklich löschen?')">
                                <i class="cil-trash"></i> Löschen
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        Erstellt: {{ task.createdAt|date('d.m.Y H:i') }}
                        | Aktualisiert: {{ task.updatedAt|date('d.m.Y H:i') }}
                        {% if task.completedAt %}
                        | Abgeschlossen: {{ task.completedAt|date('d.m.Y H:i') }}
                        {% endif %}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

## Schritt 5: Datenbank-Tabellen erstellen

```sql
CREATE TABLE `task_categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) NOT NULL DEFAULT '#6c757d',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tasks` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `status` VARCHAR(20) NOT NULL DEFAULT 'open',
    `priority` VARCHAR(20) NOT NULL DEFAULT 'medium',
    `due_date` DATETIME DEFAULT NULL,
    `category_id` INT(11) DEFAULT NULL,
    `user_id` INT(11) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`),
    KEY `idx_due_date` (`due_date`),
    KEY `idx_user_id` (`user_id`),
    FOREIGN KEY (`category_id`) REFERENCES `task_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Beispiel-Kategorien
INSERT INTO `task_categories` (`name`, `color`) VALUES
    ('Arbeit', '#007bff'),
    ('Privat', '#28a745'),
    ('Einkaufen', '#ffc107'),
    ('Wichtig', '#dc3545');
```

## Ergebnis

Sie haben eine vollständige CRUD-Anwendung mit:
- ✅ Aufgaben erstellen, anzeigen, bearbeiten und löschen
- ✅ Kategorien und Prioritäten
- ✅ Fälligkeitsdaten mit Überfällig-Erkennung
- ✅ Status-Tracking mit Schnellwechsel
- ✅ Filter nach Status und Priorität
- ✅ Suchfunktion
- ✅ Dashboard mit Statistiken
- ✅ Benutzerbasierte Aufgaben (jeder sieht nur seine)
- ✅ Flash-Messages für Feedback

## Nächste Schritte

- [REST API](REST_API.md) - API für die Task-Anwendung erstellen
- [Admin Dashboard](ADMIN_DASHBOARD.md) - Verwaltungsbereich
- [Einfache Webseite](SIMPLE_WEBSITE.md) - Öffentliche Seiten
