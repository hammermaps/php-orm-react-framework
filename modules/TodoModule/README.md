# TodoModule - Example Module

This is a complete, working example module for the PHP ORM React Framework that demonstrates best practices for building a CRUD application.

## Features

- ✅ Create, Read, Update, Delete (CRUD) operations
- ✅ User authentication and authorization
- ✅ Doctrine ORM entity with relationships
- ✅ Custom repository with complex queries
- ✅ Priority levels (Low, Medium, High)
- ✅ Due date tracking with overdue detection
- ✅ Completion status tracking
- ✅ Flash messages for user feedback
- ✅ Responsive Twig templates with CoreUI
- ✅ Navigation with filters and sub-routes
- ✅ Dashboard statistics

## Installation

### 1. Database Migration

Create the database table using Doctrine schema tools or run this SQL:

```sql
CREATE TABLE `todos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `completed` TINYINT(1) NOT NULL DEFAULT 0,
  `priority` VARCHAR(20) NOT NULL DEFAULT 'low',
  `due_date` DATETIME DEFAULT NULL,
  `user_id` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_completed` (`completed`),
  KEY `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Access the Module

Once installed, you can access the module at:

```
http://yoursite.com/?module=todoModule&controller=todo&action=index
```

**Note**: You must be logged in to access this module (requires at least USER role).

## Module Structure

```
modules/TodoModule/
├── README.md
├── src/
│   ├── Controllers/
│   │   └── TodoController.php      # Main controller with all actions
│   ├── Entities/
│   │   └── Todo.php                # Doctrine entity
│   └── Repositories/
│       └── TodoRepository.php      # Custom repository with queries
└── views/
    └── TodoController/
        ├── indexAction.tpl.twig    # List view with stats
        ├── createAction.tpl.twig   # Create form
        └── editAction.tpl.twig     # Edit form
```

## Key Concepts Demonstrated

### 1. Controller Structure

The `TodoController` extends `RestrictedFrontController` to require authentication:

```php
/**
 * @Navigation(text="Todo List", position="sidebar")
 * @Access(role=Group::ROLE_USER)
 */
class TodoController extends RestrictedFrontController
```

### 2. Navigation Annotations

```php
/**
 * @SubNavigation(text="My Todos", icon="cil-list")
 */
public function indexAction(): void
```

### 3. Sub-Routes for Filtering

```php
/**
 * @SubNavigation(text="Filter", icon="cil-filter")
 * @SubRoutes(routes={
 *     @SubRoute(text="All Todos", icon="cil-list", hrefQueryAddition={"filter": "all"}),
 *     @SubRoute(text="Incomplete", icon="cil-task", hrefQueryAddition={"filter": "incomplete"}),
 *     // ...
 * })
 */
```

### 4. Doctrine Entity with Gedmo Extensions

The `Todo` entity uses:
- `@Gedmo\Timestampable` for automatic timestamp management
- Type hints for PHP 7.4+
- Getter/setter methods
- Custom business logic methods (`isOverdue()`, `getPriorityBadgeClass()`)

### 5. Custom Repository Queries

The `TodoRepository` provides:
- `findByUser()` - All todos for a user
- `findIncompleteByUser()` - Only incomplete todos
- `findCompletedByUser()` - Only completed todos
- `findOverdueByUser()` - Overdue todos
- `findByPriority()` - Filter by priority
- `countIncomplete()` - Count incomplete todos
- `countOverdue()` - Count overdue todos

### 6. Form Handling

Standard POST request handling:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $this->handleCreateTodo();
    return;
}
```

### 7. Flash Messages

User feedback using the flash message handler:

```php
$this->getFlashHandler()->addFlashMessage('success', 'Todo created successfully!');
```

### 8. Redirects

```php
$this->redirect('?module=todoModule&controller=todo&action=index');
```

## Usage Examples

### Creating a Todo

1. Navigate to "Add New Todo" from the sidebar
2. Fill in the form:
   - Title (required)
   - Description (optional)
   - Priority (low/medium/high)
   - Due Date (optional)
3. Click "Create Todo"

### Filtering Todos

Use the "Filter" dropdown in the navigation to view:
- All todos
- Incomplete todos
- Completed todos
- Overdue todos
- By priority (high/medium/low)

### Quick Actions

- **Toggle completion**: Click the checkmark/reload icon
- **Edit**: Click the pencil icon
- **Delete**: Click the trash icon (with confirmation)

## Extending the Module

### Add Categories

1. Create a `Category` entity
2. Add ManyToOne relationship to `Todo`
3. Update forms to include category selection
4. Add category filter to navigation

### Add Tags

1. Create a `Tag` entity
2. Add ManyToMany relationship to `Todo`
3. Update forms with tag input
4. Add tag-based filtering

### Add Comments

1. Create a `TodoComment` entity
2. Add OneToMany relationship to `Todo`
3. Create a comment view/form
4. Display comments in the edit view

### Add API Endpoints

Create `TodoApiController` extending `ApiController`:

```php
class TodoApiController extends ApiController
{
    public function listAction(): void
    {
        // Return JSON list of todos
    }
}
```

## Best Practices Demonstrated

1. **Separation of Concerns**: Controller, Entity, Repository, View
2. **DRY Principle**: Reusable methods and components
3. **Security**: User authentication, authorization, input validation
4. **User Experience**: Flash messages, confirmations, responsive design
5. **Code Organization**: Clear structure, proper namespacing
6. **Database Design**: Indexes on frequently queried columns
7. **Error Handling**: Try-catch blocks with user-friendly messages
8. **Documentation**: Inline comments and docblocks

## Troubleshooting

### Navigation doesn't appear

Make sure you're logged in with at least USER role.

### Database errors

Ensure the `todos` table is created and your database configuration is correct.

### Module not found

Check that the autoloader can find the module. The framework should automatically detect modules in the `modules/` directory.

## Learning Path

1. Study the `TodoController` to understand controller structure
2. Examine the `Todo` entity to learn about Doctrine entities
3. Review the `TodoRepository` for custom queries
4. Explore the Twig templates for view rendering
5. Modify and extend the module to add your own features

## Support

This module is part of the PHP ORM React Framework examples. For framework-specific questions, refer to:
- [EVALUATION.md](../../EVALUATION.md) - Framework evaluation
- [EXAMPLES.md](../../EXAMPLES.md) - More code examples
- [README.md](../../README.md) - Framework documentation
