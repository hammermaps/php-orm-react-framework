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

namespace Modules\TodoModule\Controllers;

use Annotations\Access;
use Annotations\Navigation;
use Annotations\SubNavigation;
use Annotations\SubRoute;
use Annotations\SubRoutes;
use Controllers\RestrictedFrontController;
use Entities\Group;
use Modules\TodoModule\Entities\Todo;
use Modules\TodoModule\Repositories\TodoRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use DateTime;
use Exception;

/**
 * Class TodoController
 * @package Modules\TodoModule\Controllers
 * @Navigation(text="Todo List", position="sidebar")
 * @Access(role=Group::ROLE_USER)
 */
class TodoController extends RestrictedFrontController
{
    /**
     * @SubNavigation(text="My Todos", icon="cil-list")
     */
    public function indexAction(): void
    {
        /** @var TodoRepository $todoRepo */
        $todoRepo = $this->getDoctrineService()
            ->getEntityManager()
            ->getRepository(Todo::class);

        $userId = $this->getSessionHandler()->getUserId();
        $filter = $_GET['filter'] ?? 'all';

        switch ($filter) {
            case 'incomplete':
                $todos = $todoRepo->findIncompleteByUser($userId);
                break;
            case 'completed':
                $todos = $todoRepo->findCompletedByUser($userId);
                break;
            case 'overdue':
                $todos = $todoRepo->findOverdueByUser($userId);
                break;
            case 'high':
            case 'medium':
            case 'low':
                $todos = $todoRepo->findByPriority($userId, $filter);
                break;
            default:
                $todos = $todoRepo->findByUser($userId);
        }

        $stats = [
            'total' => count($todoRepo->findByUser($userId)),
            'incomplete' => $todoRepo->countIncomplete($userId),
            'overdue' => $todoRepo->countOverdue($userId),
        ];

        $this->getView()->assign('todos', $todos);
        $this->getView()->assign('filter', $filter);
        $this->getView()->assign('stats', $stats);

        parent::indexAction();
    }

    /**
     * @SubNavigation(text="Add New Todo", icon="cil-plus")
     */
    public function createAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreateTodo();
            return;
        }

        parent::indexAction();
    }

    /**
     * Edit existing todo
     */
    public function editAction(): void
    {
        $todoId = (int)($_GET['id'] ?? 0);
        $userId = $this->getSessionHandler()->getUserId();

        if (!$todoId) {
            $this->render404();
            return;
        }

        $entityManager = $this->getDoctrineService()->getEntityManager();
        $todo = $entityManager->find(Todo::class, $todoId);

        if (!$todo || $todo->getUserId() !== $userId) {
            $this->render404();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdateTodo($todo);
            return;
        }

        $this->getView()->assign('todo', $todo);
        parent::indexAction();
    }

    /**
     * Toggle todo completion status
     */
    public function toggleAction(): void
    {
        $todoId = (int)($_GET['id'] ?? 0);
        $userId = $this->getSessionHandler()->getUserId();

        if (!$todoId) {
            $this->redirect('?module=todoModule&controller=todo&action=index');
            return;
        }

        $entityManager = $this->getDoctrineService()->getEntityManager();
        $todo = $entityManager->find(Todo::class, $todoId);

        if (!$todo || $todo->getUserId() !== $userId) {
            $this->render404();
            return;
        }

        try {
            $todo->setCompleted(!$todo->isCompleted());
            $entityManager->flush();

            $message = $todo->isCompleted() 
                ? 'Todo marked as completed!' 
                : 'Todo marked as incomplete.';
            $this->getFlashHandler()->addFlashMessage('success', $message);
        } catch (OptimisticLockException | ORMException $e) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Failed to update todo');
        }

        $this->redirect('?module=todoModule&controller=todo&action=index');
    }

    /**
     * Delete todo
     */
    public function deleteAction(): void
    {
        $todoId = (int)($_GET['id'] ?? 0);
        $userId = $this->getSessionHandler()->getUserId();

        if (!$todoId) {
            $this->redirect('?module=todoModule&controller=todo&action=index');
            return;
        }

        $entityManager = $this->getDoctrineService()->getEntityManager();
        $todo = $entityManager->find(Todo::class, $todoId);

        if (!$todo || $todo->getUserId() !== $userId) {
            $this->render404();
            return;
        }

        try {
            $entityManager->remove($todo);
            $entityManager->flush();

            $this->getFlashHandler()->addFlashMessage('success', 'Todo deleted successfully');
        } catch (ORMException $e) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Failed to delete todo');
        }

        $this->redirect('?module=todoModule&controller=todo&action=index');
    }

    /**
     * @SubNavigation(text="Filter", icon="cil-filter")
     * @SubRoutes(routes={
     *     @SubRoute(text="All Todos", icon="cil-list", hrefQueryAddition={"filter": "all"}),
     *     @SubRoute(text="Incomplete", icon="cil-task", hrefQueryAddition={"filter": "incomplete"}),
     *     @SubRoute(text="Completed", icon="cil-check", hrefQueryAddition={"filter": "completed"}),
     *     @SubRoute(text="Overdue", icon="cil-clock", hrefQueryAddition={"filter": "overdue"}),
     *     @SubRoute(text="High Priority", labelIcon="cil-warning", isLabel=true, labelClass="danger", hrefQueryAddition={"filter": "high"}),
     *     @SubRoute(text="Medium Priority", labelIcon="cil-info", isLabel=true, labelClass="warning", hrefQueryAddition={"filter": "medium"}),
     *     @SubRoute(text="Low Priority", labelIcon="cil-star", isLabel=true, labelClass="success", hrefQueryAddition={"filter": "low"})
     * }, onlyWhenActive=false)
     */
    public function filterAction(): void
    {
        $this->indexAction();
    }

    private function handleCreateTodo(): void
    {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = $_POST['priority'] ?? 'low';
        $dueDate = $_POST['due_date'] ?? '';

        if (empty($title)) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Title is required');
            $this->redirect('?module=todoModule&controller=todo&action=create');
            return;
        }

        $todo = new Todo();
        $todo->setTitle($title);
        $todo->setDescription($description);
        $todo->setPriority($priority);
        $todo->setUserId($this->getSessionHandler()->getUserId());

        if (!empty($dueDate)) {
            try {
                $todo->setDueDate(new DateTime($dueDate));
            } catch (Exception $e) {
                $this->getFlashHandler()->addFlashMessage('warning', 'Invalid due date format');
            }
        }

        try {
            $entityManager = $this->getDoctrineService()->getEntityManager();
            $entityManager->persist($todo);
            $entityManager->flush();

            $this->getFlashHandler()->addFlashMessage('success', 'Todo created successfully!');
            $this->redirect('?module=todoModule&controller=todo&action=index');
        } catch (ORMException $e) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Failed to create todo');
            $this->redirect('?module=todoModule&controller=todo&action=create');
        }
    }

    private function handleUpdateTodo(Todo $todo): void
    {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = $_POST['priority'] ?? 'low';
        $dueDate = $_POST['due_date'] ?? '';
        $completed = isset($_POST['completed']);

        if (empty($title)) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Title is required');
            $this->redirect('?module=todoModule&controller=todo&action=edit&id=' . $todo->getId());
            return;
        }

        $todo->setTitle($title);
        $todo->setDescription($description);
        $todo->setPriority($priority);
        $todo->setCompleted($completed);

        if (!empty($dueDate)) {
            try {
                $todo->setDueDate(new DateTime($dueDate));
            } catch (Exception $e) {
                $this->getFlashHandler()->addFlashMessage('warning', 'Invalid due date format');
            }
        } else {
            $todo->setDueDate(null);
        }

        try {
            $entityManager = $this->getDoctrineService()->getEntityManager();
            $entityManager->flush();

            $this->getFlashHandler()->addFlashMessage('success', 'Todo updated successfully!');
            $this->redirect('?module=todoModule&controller=todo&action=index');
        } catch (OptimisticLockException | ORMException $e) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Failed to update todo');
            $this->redirect('?module=todoModule&controller=todo&action=edit&id=' . $todo->getId());
        }
    }
}
