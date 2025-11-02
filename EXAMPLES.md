# PHP ORM React Framework - Usage Examples

This document provides comprehensive examples for working with the PHP ORM React Framework (Phorm RF).

## Table of Contents

1. [Getting Started](#getting-started)
2. [Creating a Custom Module](#creating-a-custom-module)
3. [Working with Controllers](#working-with-controllers)
4. [Doctrine ORM Entities](#doctrine-orm-entities)
5. [Navigation and Routing](#navigation-and-routing)
6. [Working with Views](#working-with-views)
7. [React Integration](#react-integration)
8. [Authentication and Authorization](#authentication-and-authorization)
9. [Services and Helpers](#services-and-helpers)
10. [Advanced Examples](#advanced-examples)

---

## Getting Started

### Installation

```bash
# Create new project
composer create-project dwwe/php-orm-react-framework my-project

# Navigate to project directory
cd my-project

# Install Node.js dependencies
yarn install

# Install asset dependencies
cd assets
yarn install
cd ..
```

### Basic Configuration

```bash
# Copy configuration files
cp config/default-config.php.dist config/default-config.php
cp config/portal-config.php.dist config/portal-config.php

# Edit configuration
nano config/default-config.php
```

### Running the Application

```bash
# Development server (if using PHP built-in server)
php -S localhost:8000

# Build assets
cd assets
yarn build
```

---

## Creating a Custom Module

### Module Structure

Create a new module called `BlogModule`:

```
modules/
└── BlogModule/
    ├── src/
    │   ├── Controllers/
    │   │   └── PostController.php
    │   ├── Entities/
    │   │   └── Post.php
    │   └── Repositories/
    │       └── PostRepository.php
    └── views/
        └── PostController/
            ├── indexAction.tpl.twig
            ├── viewAction.tpl.twig
            └── createAction.tpl.twig
```

### Creating the Post Entity

**File**: `modules/BlogModule/src/Entities/Post.php`

```php
<?php

namespace Modules\BlogModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="Modules\BlogModule\Repositories\PostRepository")
 * @ORM\Table(name="blog_posts")
 */
class Post
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
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $slug;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private string $content;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $authorId;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $published = false;

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

    // Getters and Setters

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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $authorId): self
    {
        $this->authorId = $authorId;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;
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
}
```

### Creating the Repository

**File**: `modules/BlogModule/src/Repositories/PostRepository.php`

```php
<?php

namespace Modules\BlogModule\Repositories;

use Doctrine\ORM\EntityRepository;
use Modules\BlogModule\Entities\Post;

class PostRepository extends EntityRepository
{
    /**
     * Find all published posts
     */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.published = :published')
            ->setParameter('published', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find post by slug
     */
    public function findBySlug(string $slug): ?Post
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Find posts by author
     */
    public function findByAuthor(int $authorId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.authorId = :authorId')
            ->setParameter('authorId', $authorId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
```

---

## Working with Controllers

### Public Controller Example

**File**: `modules/BlogModule/src/Controllers/PostController.php`

```php
<?php

namespace Modules\BlogModule\Controllers;

use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\PublicFrontController;
use Modules\BlogModule\Entities\Post;
use Modules\BlogModule\Repositories\PostRepository;

/**
 * @Navigation(text="Blog", position="sidebar")
 */
class PostController extends PublicFrontController
{
    /**
     * @SubNavigation(text="All Posts", icon="cil-list")
     */
    public function indexAction(): void
    {
        /** @var PostRepository $postRepo */
        $postRepo = $this->getDoctrineService()
            ->getEntityManager()
            ->getRepository(Post::class);

        $posts = $postRepo->findPublished();

        $this->getView()->assign('posts', $posts);
        parent::indexAction();
    }

    /**
     * View single post
     */
    public function viewAction(): void
    {
        $slug = $_GET['slug'] ?? null;

        if (!$slug) {
            $this->render404();
            return;
        }

        /** @var PostRepository $postRepo */
        $postRepo = $this->getDoctrineService()
            ->getEntityManager()
            ->getRepository(Post::class);

        $post = $postRepo->findBySlug($slug);

        if (!$post || !$post->isPublished()) {
            $this->render404();
            return;
        }

        $this->getView()->assign('post', $post);
        parent::indexAction();
    }
}
```

### Restricted Controller Example (Admin)

**File**: `modules/BlogModule/src/Controllers/AdminPostController.php`

```php
<?php

namespace Modules\BlogModule\Controllers;

use Annotations\Access;
use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\RestrictedFrontController;
use Entities\Group;
use Modules\BlogModule\Entities\Post;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @Navigation(text="Blog Admin", position="sidebar")
 * @Access(role=Group::ROLE_ADMIN)
 */
class AdminPostController extends RestrictedFrontController
{
    /**
     * @SubNavigation(text="Manage Posts", icon="cil-pencil")
     */
    public function indexAction(): void
    {
        $entityManager = $this->getDoctrineService()->getEntityManager();
        $postRepo = $entityManager->getRepository(Post::class);

        $posts = $postRepo->findAll();

        $this->getView()->assign('posts', $posts);
        parent::indexAction();
    }

    /**
     * @SubNavigation(text="Create New Post", icon="cil-plus")
     */
    public function createAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreatePost();
            return;
        }

        parent::indexAction();
    }

    /**
     * Edit existing post
     */
    public function editAction(): void
    {
        $postId = (int)($_GET['id'] ?? 0);

        if (!$postId) {
            $this->render404();
            return;
        }

        $entityManager = $this->getDoctrineService()->getEntityManager();
        $post = $entityManager->find(Post::class, $postId);

        if (!$post) {
            $this->render404();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdatePost($post);
            return;
        }

        $this->getView()->assign('post', $post);
        parent::indexAction();
    }

    /**
     * Delete post
     */
    public function deleteAction(): void
    {
        $postId = (int)($_GET['id'] ?? 0);

        if (!$postId) {
            $this->render404();
            return;
        }

        $entityManager = $this->getDoctrineService()->getEntityManager();
        $post = $entityManager->find(Post::class, $postId);

        if (!$post) {
            $this->render404();
            return;
        }

        try {
            $entityManager->remove($post);
            $entityManager->flush();

            $this->getFlashHandler()->addFlashMessage('success', 'Post deleted successfully');
        } catch (ORMException $e) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Failed to delete post');
        }

        $this->redirect('?module=blogModule&controller=adminPost&action=index');
    }

    private function handleCreatePost(): void
    {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $published = isset($_POST['published']);

        if (empty($title) || empty($content)) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Title and content are required');
            return;
        }

        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setPublished($published);
        $post->setAuthorId($this->getSessionHandler()->getUserId());

        try {
            $entityManager = $this->getDoctrineService()->getEntityManager();
            $entityManager->persist($post);
            $entityManager->flush();

            $this->getFlashHandler()->addFlashMessage('success', 'Post created successfully');
            $this->redirect('?module=blogModule&controller=adminPost&action=index');
        } catch (ORMException $e) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Failed to create post');
        }
    }

    private function handleUpdatePost(Post $post): void
    {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $published = isset($_POST['published']);

        if (empty($title) || empty($content)) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Title and content are required');
            return;
        }

        $post->setTitle($title);
        $post->setContent($content);
        $post->setPublished($published);

        try {
            $entityManager = $this->getDoctrineService()->getEntityManager();
            $entityManager->flush();

            $this->getFlashHandler()->addFlashMessage('success', 'Post updated successfully');
            $this->redirect('?module=blogModule&controller=adminPost&action=index');
        } catch (OptimisticLockException | ORMException $e) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Failed to update post');
        }
    }
}
```

### API Controller Example

**File**: `modules/BlogModule/src/Controllers/PostApiController.php`

```php
<?php

namespace Modules\BlogModule\Controllers;

use Controllers\ApiController;
use Modules\BlogModule\Entities\Post;

class PostApiController extends ApiController
{
    /**
     * GET /api/posts
     */
    public function indexAction(): void
    {
        $entityManager = $this->getDoctrineService()->getEntityManager();
        $postRepo = $entityManager->getRepository(Post::class);

        $posts = $postRepo->findPublished();

        $data = array_map(function(Post $post) {
            return [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'content' => $post->getContent(),
                'published' => $post->isPublished(),
                'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $posts);

        $this->jsonResponse(['success' => true, 'data' => $data]);
    }

    /**
     * GET /api/posts/view?slug=post-slug
     */
    public function viewAction(): void
    {
        $slug = $_GET['slug'] ?? null;

        if (!$slug) {
            $this->jsonResponse(['success' => false, 'error' => 'Slug required'], 400);
            return;
        }

        $entityManager = $this->getDoctrineService()->getEntityManager();
        $postRepo = $entityManager->getRepository(Post::class);
        $post = $postRepo->findBySlug($slug);

        if (!$post) {
            $this->jsonResponse(['success' => false, 'error' => 'Post not found'], 404);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'data' => [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'content' => $post->getContent(),
                'published' => $post->isPublished(),
                'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
```

---

## Doctrine ORM Entities

### Entity with Relationships

**File**: `modules/BlogModule/src/Entities/Comment.php`

```php
<?php

namespace Modules\BlogModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="blog_comments")
 */
class Comment
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var Post
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="comments")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private Post $post;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private string $authorName;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private string $authorEmail;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private string $content;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $approved = false;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private DateTime $createdAt;

    // Getters and Setters

    public function getId(): int
    {
        return $this->id;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function setPost(Post $post): self
    {
        $this->post = $post;
        return $this;
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;
        return $this;
    }

    public function getAuthorEmail(): string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(string $authorEmail): self
    {
        $this->authorEmail = $authorEmail;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
```

Update the Post entity to include the relationship:

```php
// Add to Post.php

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @var Collection
 * @ORM\OneToMany(targetEntity="Comment", mappedBy="post", cascade={"remove"})
 */
private Collection $comments;

public function __construct()
{
    $this->comments = new ArrayCollection();
}

public function getComments(): Collection
{
    return $this->comments;
}

public function addComment(Comment $comment): self
{
    if (!$this->comments->contains($comment)) {
        $this->comments->add($comment);
        $comment->setPost($this);
    }
    return $this;
}
```

---

## Navigation and Routing

### Navigation Annotations

```php
/**
 * Main navigation item
 * @Navigation(text="Products", position="sidebar")
 */
class ProductController extends PublicFrontController
{
    /**
     * Sub-navigation item
     * @SubNavigation(text="All Products", icon="cil-list")
     */
    public function indexAction(): void
    {
        // ...
    }

    /**
     * Conditional sub-navigation (only shown when required params present)
     * @SubNavigation(text="Product Details", icon="cil-info", requiredGetParams={"id"})
     */
    public function detailsAction(): void
    {
        // ...
    }
}
```

### Sub-routes with Dropdowns

```php
use Annotations\SubRoute;
use Annotations\SubRoutes;

/**
 * @SubNavigation(text="Categories", icon="cil-folder")
 * @SubRoutes(routes={
 *     @SubRoute(text="Electronics", icon="cil-laptop", hrefQueryAddition={"category": "electronics"}),
 *     @SubRoute(text="Clothing", icon="cil-shirt", hrefQueryAddition={"category": "clothing"}),
 *     @SubRoute(text="Books", icon="cil-book", hrefQueryAddition={"category": "books"})
 * })
 */
public function categoryAction(): void
{
    $category = $_GET['category'] ?? 'all';
    // Handle category filtering
}
```

---

## Working with Views

### Twig Template Example

**File**: `modules/BlogModule/views/PostController/indexAction.tpl.twig`

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <i class="cil-list"></i> Blog Posts
                </div>
                <div class="card-body">
                    {% if posts is empty %}
                        <p class="text-muted">No posts found.</p>
                    {% else %}
                        <div class="row">
                            {% for post in posts %}
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ post.title }}</h5>
                                            <p class="card-text">{{ post.content|slice(0, 150) }}...</p>
                                            <p class="text-muted">
                                                <small>Posted on {{ post.createdAt|date('F d, Y') }}</small>
                                            </p>
                                        </div>
                                        <div class="card-footer">
                                            <a href="?module=blogModule&controller=post&action=view&slug={{ post.slug }}" 
                                               class="btn btn-primary btn-sm">
                                                Read More
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            {% endfor %}
                        </div>
                    {% endif %}
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

**File**: `modules/BlogModule/views/PostController/viewAction.tpl.twig`

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h2>{{ post.title }}</h2>
                    <p class="text-muted mb-0">
                        <small>Published on {{ post.createdAt|date('F d, Y \\a\\t H:i') }}</small>
                    </p>
                </div>
                <div class="card-body">
                    <div class="post-content">
                        {{ post.content|nl2br }}
                    </div>
                </div>
                <div class="card-footer">
                    <a href="?module=blogModule&controller=post&action=index" class="btn btn-secondary">
                        <i class="cil-arrow-left"></i> Back to Posts
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

### Form Example

**File**: `modules/BlogModule/views/AdminPostController/createAction.tpl.twig`

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <i class="cil-plus"></i> Create New Post
                </div>
                <div class="card-body">
                    <form method="POST" action="?module=blogModule&controller=adminPost&action=create">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea class="form-control" 
                                      id="content" 
                                      name="content" 
                                      rows="10" 
                                      required></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="published" 
                                   name="published">
                            <label class="form-check-label" for="published">
                                Publish immediately
                            </label>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="cil-save"></i> Create Post
                            </button>
                            <a href="?module=blogModule&controller=adminPost&action=index" 
                               class="btn btn-secondary">
                                Cancel
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

---

## React Integration

### Creating a React Component

**File**: `assets/react/components/PostList.jsx`

```jsx
import React, { useState, useEffect } from 'react';
import { CCard, CCardBody, CCardHeader, CSpinner, CAlert } from '@coreui/react';

const PostList = () => {
    const [posts, setPosts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchPosts();
    }, []);

    const fetchPosts = async () => {
        try {
            const response = await fetch('?module=blogModule&controller=postApi&action=index');
            const data = await response.json();
            
            if (data.success) {
                setPosts(data.data);
            } else {
                setError('Failed to load posts');
            }
        } catch (err) {
            setError('An error occurred while fetching posts');
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <CCard>
                <CCardBody className="text-center">
                    <CSpinner color="primary" />
                </CCardBody>
            </CCard>
        );
    }

    if (error) {
        return (
            <CAlert color="danger">
                {error}
            </CAlert>
        );
    }

    return (
        <div>
            {posts.map(post => (
                <CCard key={post.id} className="mb-3">
                    <CCardHeader>
                        <h5>{post.title}</h5>
                    </CCardHeader>
                    <CCardBody>
                        <p>{post.content.substring(0, 200)}...</p>
                        <small className="text-muted">
                            Published: {new Date(post.created_at).toLocaleDateString()}
                        </small>
                    </CCardBody>
                </CCard>
            ))}
        </div>
    );
};

export default PostList;
```

### Using React in Twig

**File**: `modules/BlogModule/views/PostController/reactIndexAction.tpl.twig`

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="row">
        <div class="col-lg-12">
            <div id="react-post-list"></div>
        </div>
    </div>
</div>
{% endblock %}

{% block page_scripts %}
<script>
    // Assuming React and PostList component are bundled
    ReactDOM.render(
        React.createElement(PostList),
        document.getElementById('react-post-list')
    );
</script>
{% endblock %}
```

---

## Authentication and Authorization

### Checking User Authentication

```php
// In a controller
public function restrictedAction(): void
{
    if (!$this->getSessionHandler()->isRegistered()) {
        $this->render403();
        return;
    }
    
    // User is authenticated
    $userId = $this->getSessionHandler()->getUserId();
    $userName = $this->getSessionHandler()->getUserName();
}
```

### Role-Based Access Control

```php
use Entities\Group;
use Annotations\Access;

/**
 * Require at least USER role for entire controller
 * @Access(role=Group::ROLE_USER)
 */
class UserDashboardController extends RestrictedFrontController
{
    public function indexAction(): void
    {
        // Accessible by USER, MODERATOR, and ADMIN
    }

    /**
     * Require ADMIN role for this specific action
     * @Access(role=Group::ROLE_ADMIN)
     */
    public function settingsAction(): void
    {
        // Only accessible by ADMIN
    }

    /**
     * Check role programmatically
     */
    public function customAction(): void
    {
        if ($this->getSessionHandler()->hasRequiredRole(Group::ROLE_MODERATOR)) {
            // User has at least MODERATOR role
        }
    }
}
```

---

## Services and Helpers

### Using Doctrine Service

```php
// Get Entity Manager
$entityManager = $this->getDoctrineService()->getEntityManager();

// Find entity by ID
$user = $entityManager->find(User::class, $userId);

// Get repository
$userRepo = $entityManager->getRepository(User::class);
$allUsers = $userRepo->findAll();

// Custom query
$qb = $entityManager->createQueryBuilder();
$users = $qb->select('u')
    ->from(User::class, 'u')
    ->where('u.locale = :locale')
    ->setParameter('locale', 'en_US')
    ->getQuery()
    ->getResult();

// Persist new entity
$user = new User();
$user->setName('John Doe');
$entityManager->persist($user);
$entityManager->flush();
```

### Using Cache Service

```php
// Get cache service
$cacheService = $this->getCacheService();

// Store in cache
$cacheService->set('key', 'value', 3600); // 1 hour TTL

// Retrieve from cache
$value = $cacheService->get('key');

// Check if exists
if ($cacheService->has('key')) {
    // Key exists in cache
}

// Delete from cache
$cacheService->delete('key');

// Clear all cache
$cacheService->clear();
```

### Using Flash Messages

```php
// Add success message
$this->getFlashHandler()->addFlashMessage('success', 'Operation completed successfully');

// Add error message
$this->getFlashHandler()->addFlashMessage('danger', 'An error occurred');

// Add warning
$this->getFlashHandler()->addFlashMessage('warning', 'Please be careful');

// Add info
$this->getFlashHandler()->addFlashMessage('info', 'Here is some information');

// In Twig template, flash messages are automatically displayed
```

### Using Helper Classes

```php
use Helpers\StringHelper;
use Helpers\ArrayHelper;
use Helpers\FileHelper;

// String Helper
$slug = StringHelper::slugify('Hello World'); // "hello-world"

// Array Helper
$value = ArrayHelper::get($array, 'key.nested.path', 'default');

// File Helper
$exists = FileHelper::exists('/path/to/file.txt');
$content = FileHelper::read('/path/to/file.txt');
FileHelper::write('/path/to/file.txt', 'content');
```

---

## Advanced Examples

### Custom Service

**File**: `modules/BlogModule/src/Services/EmailService.php`

```php
<?php

namespace Modules\BlogModule\Services;

class EmailService
{
    private string $fromEmail;
    private string $fromName;

    public function __construct(string $fromEmail, string $fromName)
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function sendPostNotification(string $to, string $postTitle, string $postUrl): bool
    {
        $subject = "New blog post: {$postTitle}";
        $message = "A new blog post has been published: {$postTitle}\n\n";
        $message .= "Read it here: {$postUrl}";

        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        return mail($to, $subject, $message, $headers);
    }
}
```

### Custom Middleware (Pre-Run Logic)

```php
<?php

namespace Modules\BlogModule\Controllers;

use Controllers\PublicFrontController;

class BlogController extends PublicFrontController
{
    /**
     * Runs before any action
     */
    public function preRun(string $action): void
    {
        parent::preRun($action);

        // Custom logic before action execution
        $this->logPageView();
        $this->checkMaintenanceMode();
    }

    private function logPageView(): void
    {
        // Log page view to analytics
        $page = $_GET['action'] ?? 'index';
        // ... logging logic
    }

    private function checkMaintenanceMode(): void
    {
        if ($this->getConfig()->get('maintenance_mode', false)) {
            $this->renderMaintenancePage();
        }
    }
}
```

### Database Transaction Example

```php
public function complexOperation(): void
{
    $entityManager = $this->getDoctrineService()->getEntityManager();
    
    try {
        $entityManager->beginTransaction();

        // Create post
        $post = new Post();
        $post->setTitle('New Post');
        $post->setContent('Content here');
        $post->setPublished(true);
        $entityManager->persist($post);
        $entityManager->flush();

        // Create related comment
        $comment = new Comment();
        $comment->setPost($post);
        $comment->setContent('First comment!');
        $entityManager->persist($comment);
        $entityManager->flush();

        $entityManager->commit();

        $this->getFlashHandler()->addFlashMessage('success', 'Post and comment created');
    } catch (\Exception $e) {
        $entityManager->rollback();
        $this->getFlashHandler()->addFlashMessage('danger', 'Failed to create post');
    }
}
```

### AJAX Request Handling

```php
public function ajaxSearchAction(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        return;
    }

    $query = $_POST['query'] ?? '';

    if (strlen($query) < 3) {
        echo json_encode(['success' => false, 'error' => 'Query too short']);
        return;
    }

    $entityManager = $this->getDoctrineService()->getEntityManager();
    $qb = $entityManager->createQueryBuilder();

    $posts = $qb->select('p')
        ->from(Post::class, 'p')
        ->where('p.title LIKE :query')
        ->orWhere('p.content LIKE :query')
        ->setParameter('query', "%{$query}%")
        ->getQuery()
        ->getResult();

    $results = array_map(function(Post $post) {
        return [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'slug' => $post->getSlug(),
        ];
    }, $posts);

    echo json_encode(['success' => true, 'results' => $results]);
}
```

---

## Conclusion

These examples demonstrate the most common patterns and use cases in the PHP ORM React Framework. The framework provides a solid foundation for building modular web applications with:

- Clean MVC architecture
- Powerful ORM (Doctrine)
- Modern templating (Twig)
- React integration for dynamic UIs
- Built-in authentication and authorization
- Comprehensive helper and service classes

For more advanced usage and specific scenarios, refer to the system core files and the existing ExampleModule for additional patterns and best practices.
