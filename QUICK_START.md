# Quick Start Guide - PHP ORM React Framework

Welcome! This guide will help you get started with the PHP ORM React Framework (Phorm RF) quickly.

## 📚 Documentation Overview

This repository now includes comprehensive documentation:

1. **[README.md](README.md)** - Main framework documentation and installation
2. **[EVALUATION.md](EVALUATION.md)** - In-depth code evaluation, strengths, weaknesses, and recommendations
3. **[EXAMPLES.md](EXAMPLES.md)** - Comprehensive code examples and usage patterns
4. **[TodoModule](modules/TodoModule/)** - Complete working example module

## 🚀 Getting Started in 5 Minutes

### 1. Install the Framework

```bash
# Create a new project
composer create-project dwwe/php-orm-react-framework my-app

# Navigate to the project
cd my-app

# Install dependencies
yarn install

# Install assets dependencies
cd assets
yarn install
cd ..
```

### 2. Configure the Application

```bash
# Copy configuration files
cp config/default-config.php.dist config/default-config.php
cp config/portal-config.php.dist config/portal-config.php

# Edit the configuration (database, etc.)
nano config/default-config.php
```

### 3. Set Up the Database

Create your database and update the configuration, then create the schema:

```sql
-- Example for User and Group tables
CREATE DATABASE my_app;
USE my_app;

-- Run Doctrine schema tools or import your schema
```

### 4. Start the Development Server

```bash
# Using PHP built-in server
php -S localhost:8000

# Or configure your web server (Apache/Nginx) to point to the project directory
```

### 5. Access Your Application

Open your browser and navigate to:
```
http://localhost:8000
```

## 📖 Learning Path

### For Beginners

1. **Read the evaluation** - [EVALUATION.md](EVALUATION.md)
   - Understand the framework architecture
   - Learn about design patterns used
   - See strengths and limitations

2. **Study the examples** - [EXAMPLES.md](EXAMPLES.md)
   - Basic controller creation
   - Working with entities
   - Creating views with Twig

3. **Explore the TodoModule** - [modules/TodoModule](modules/TodoModule/)
   - See a complete working example
   - CRUD operations
   - Authentication and authorization
   - Custom queries and repositories

### For Intermediate Users

1. **Create your first module**
   ```bash
   mkdir -p modules/MyModule/src/Controllers
   mkdir -p modules/MyModule/views/MyController
   ```

2. **Follow the patterns from TodoModule**
   - Copy the structure
   - Modify for your needs
   - Add your business logic

3. **Integrate React components**
   - See React examples in [EXAMPLES.md](EXAMPLES.md)
   - Build dynamic UIs
   - Use CoreUI components

### For Advanced Users

1. **Extend the framework**
   - Create custom services
   - Add middleware
   - Build API endpoints

2. **Optimize performance**
   - Implement caching strategies
   - Optimize database queries
   - Use React for dynamic updates

3. **Deploy to production**
   - Configure for production
   - Set up proper web server
   - Enable caching
   - Security hardening

## 🎯 Common Tasks

### Creating a New Module

```bash
# Create directory structure
mkdir -p modules/MyModule/src/{Controllers,Entities,Repositories}
mkdir -p modules/MyModule/views/MyController
```

**Controller** - `modules/MyModule/src/Controllers/MyController.php`:
```php
<?php
namespace Modules\MyModule\Controllers;

use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\PublicFrontController;

/**
 * @Navigation(text="My Module", position="sidebar")
 */
class MyController extends PublicFrontController
{
    /**
     * @SubNavigation(text="Home", icon="cil-home")
     */
    public function indexAction(): void
    {
        $this->getView()->assign('message', 'Hello World!');
        parent::indexAction();
    }
}
```

**View** - `modules/MyModule/views/MyController/indexAction.tpl.twig`:
```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="card">
        <div class="card-header">My Module</div>
        <div class="card-body">
            <h3>{{ message }}</h3>
        </div>
    </div>
</div>
{% endblock %}
```

### Creating a Doctrine Entity

```php
<?php
namespace Modules\MyModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="my_items")
 */
class Item
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private DateTime $createdAt;

    // Add getters and setters...
}
```

### Working with the Database

```php
// In your controller
public function listAction(): void
{
    $entityManager = $this->getDoctrineService()->getEntityManager();
    
    // Get repository
    $itemRepo = $entityManager->getRepository(Item::class);
    
    // Find all
    $items = $itemRepo->findAll();
    
    // Find by criteria
    $items = $itemRepo->findBy(['status' => 'active']);
    
    // Custom query
    $items = $itemRepo->createQueryBuilder('i')
        ->where('i.status = :status')
        ->setParameter('status', 'active')
        ->orderBy('i.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
    
    $this->getView()->assign('items', $items);
    parent::indexAction();
}
```

### Adding Authentication

```php
use Controllers\RestrictedFrontController;
use Entities\Group;
use Annotations\Access;

/**
 * @Access(role=Group::ROLE_USER)
 */
class SecureController extends RestrictedFrontController
{
    public function dashboardAction(): void
    {
        // Only authenticated users can access
        $userId = $this->getSessionHandler()->getUserId();
        $userName = $this->getSessionHandler()->getUserName();
        
        $this->getView()->assign('userId', $userId);
        $this->getView()->assign('userName', $userName);
        
        parent::indexAction();
    }
}
```

## 🔧 Useful Commands

### Development

```bash
# Watch and rebuild assets
cd assets
yarn watch

# Build for production
yarn build

# Clear cache
rm -rf data/cache/*
```

### Database

```bash
# Generate Doctrine entities from database (if needed)
vendor/bin/doctrine orm:generate-entities ./system/Entities

# Validate schema
vendor/bin/doctrine orm:validate-schema

# Update schema
vendor/bin/doctrine orm:schema-tool:update --force
```

## 📚 Additional Resources

### Framework Components

- **Doctrine ORM**: https://www.doctrine-project.org/
- **Twig Templates**: https://twig.symfony.com/
- **React.js**: https://reactjs.org/
- **CoreUI**: https://coreui.io/
- **Webpack Encore**: https://symfony.com/doc/current/frontend.html

### Code Examples

- **Full CRUD Example**: [modules/TodoModule](modules/TodoModule/)
- **API Examples**: [EXAMPLES.md](EXAMPLES.md#api-controller-example)
- **React Integration**: [EXAMPLES.md](EXAMPLES.md#react-integration)
- **Authentication**: [EXAMPLES.md](EXAMPLES.md#authentication-and-authorization)

### Framework Evaluation

- **Architecture Analysis**: [EVALUATION.md](EVALUATION.md#architecture-overview)
- **Best Practices**: [EVALUATION.md](EVALUATION.md#code-quality-assessment)
- **Recommendations**: [EVALUATION.md](EVALUATION.md#recommendations-summary)

## 🤝 Getting Help

1. **Check the examples** - Most common patterns are documented
2. **Review the TodoModule** - It demonstrates best practices
3. **Read the evaluation** - Understand framework capabilities and limitations
4. **Explore existing modules** - See how ExampleModule is structured

## 🎓 Next Steps

After completing this quick start:

1. ✅ Build your first module following the TodoModule example
2. ✅ Read through [EVALUATION.md](EVALUATION.md) for deeper understanding
3. ✅ Study [EXAMPLES.md](EXAMPLES.md) for specific patterns
4. ✅ Customize the framework for your needs
5. ✅ Share your experience and contribute back!

## 📝 Summary

You now have:
- ✅ A working PHP ORM React Framework installation
- ✅ Comprehensive documentation and examples
- ✅ A complete TodoModule to learn from
- ✅ Understanding of framework capabilities
- ✅ Resources to build your own modules

Happy coding! 🚀
