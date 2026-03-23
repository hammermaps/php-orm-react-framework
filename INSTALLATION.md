# Installation Guide – PHP ORM React Framework (Phorm RF)

This guide walks you through every step needed to install and configure a new **Phorm RF** project from scratch.

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Create a New Project](#2-create-a-new-project)
3. [Install PHP Dependencies](#3-install-php-dependencies)
4. [Install JavaScript Dependencies](#4-install-javascript-dependencies)
5. [Configure the Application](#5-configure-the-application)
6. [Set Up the Database](#6-set-up-the-database)
7. [Configure the Web Server](#7-configure-the-web-server)
8. [Verify the Installation](#8-verify-the-installation)
9. [Build Assets for Production](#9-build-assets-for-production)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Prerequisites

Make sure the following software is installed on your system before you begin.

| Requirement | Minimum Version | Notes |
|-------------|-----------------|-------|
| PHP | 7.4 | Extensions `pdo`, `pdo_mysql` / `pdo_sqlite`, `json` must be enabled |
| Composer | 2.x (1.x also works) | [https://getcomposer.org/](https://getcomposer.org/) |
| Node.js | 12 LTS or newer | [https://nodejs.org/](https://nodejs.org/) |
| Yarn | 1.x (Classic) | [https://classic.yarnpkg.com/en/docs/install](https://classic.yarnpkg.com/en/docs/install) |
| Database | — | MySQL 5.7+ / MariaDB 10.2+ **or** SQLite 3 (bundled with PHP) |
| Web Server | — | Apache 2.4+, Nginx, or PHP's built-in server (development only) |

### Check your versions

```bash
php -v
composer --version
node -v
yarn --version
```

---

## 2. Create a New Project

Use Composer's `create-project` command. Replace `my-project` with your desired directory name.

```bash
composer create-project dwwe/php-orm-react-framework my-project
cd my-project
```

> **Tip:** To pin a specific release append the version string, e.g.  
> `composer create-project dwwe/php-orm-react-framework my-project "^1.0"`

---

## 3. Install PHP Dependencies

Composer already ran `composer install` during `create-project`. If you need to re-install (e.g. after a fresh clone), run:

```bash
composer install
```

For a production deployment use the optimised autoloader:

```bash
composer install --no-dev --optimize-autoloader
```

---

## 4. Install JavaScript Dependencies

The project has **two** separate `package.json` files – one in the project root and one in the `assets/` directory.

### 4.1 – Root dependencies (Webpack Encore, Babel, …)

```bash
# From the project root
yarn install
```

### 4.2 – Asset dependencies (React, axios, …)

```bash
cd assets
yarn install
cd ..
```

---

## 5. Configure the Application

The framework ships with two `.dist` configuration files that must be copied and edited.

### 5.1 – Copy the configuration templates

```bash
cp config/default-config.php.dist config/default-config.php
cp config/portal-config.php.dist  config/portal-config.php
```

### 5.2 – Edit `config/default-config.php`

Open the file in your editor and adjust the settings that apply to your environment:

```php
$config = [
    // Show detailed error pages (set to false in production)
    'debug_mode' => true,

    // Default UI language
    'locale_default' => 'en_GB',

    // --- Database ---
    // SQLite example (good for a quick local setup)
    'connection_options' => [
        'default' => [
            'driver' => 'pdo_sqlite',
            'path'   => __DIR__ . '/../system/db.sqlite',
        ]
    ],

    // MySQL / MariaDB example
    // 'connection_options' => [
    //     'default' => [
    //         'driver'   => 'pdo_mysql',
    //         'host'     => '127.0.0.1',
    //         'port'     => 3306,
    //         'dbname'   => 'my_project',
    //         'user'     => 'db_user',
    //         'password' => 'db_password',
    //         'charset'  => 'utf8mb4',
    //     ]
    // ],

    // Doctrine: auto-generate proxy classes (disable in production)
    'doctrine_options' => [
        'autogenerate_proxy_classes' => true,
    ],

    // Logger: 400 = WARNING level
    'logger_options' => [
        'log_level' => 400,
    ],
];
```

### 5.3 – Edit `config/portal-config.php`

```php
$config = [
    'portal_options' => [
        'b_core_ui_pro'        => false,  // set true if you have a CoreUI Pro licence
        'b_dark_layout'        => false,  // enable dark theme
        'b_allow_registration' => false,  // allow self-registration
        'b_allow_stay_logged_in' => true, // "remember me" checkbox
    ],
];
```

---

## 6. Set Up the Database

### 6.1 – SQLite (zero-configuration, development only)

Nothing extra is needed. Doctrine will create the `system/db.sqlite` file automatically the first time it connects.

### 6.2 – MySQL / MariaDB

1. Create the database:

```sql
CREATE DATABASE my_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'db_user'@'localhost' IDENTIFIED BY 'db_password';
GRANT ALL PRIVILEGES ON my_project.* TO 'db_user'@'localhost';
FLUSH PRIVILEGES;
```

2. Make sure the credentials in `config/default-config.php` match.

### 6.3 – Create / update the schema with Doctrine

```bash
# Validate the current mapping
vendor/bin/doctrine orm:validate-schema

# Create tables from your entity mappings
vendor/bin/doctrine orm:schema-tool:create

# Or update an existing schema (use with care in production)
vendor/bin/doctrine orm:schema-tool:update --force
```

---

## 7. Configure the Web Server

### 7.1 – PHP built-in server (development only)

```bash
php -S localhost:8000
```

Open [http://localhost:8000](http://localhost:8000) in your browser.

### 7.2 – Apache

The project already ships with an `.htaccess` file that blocks direct access to PHP files other than `index.php`. You only need to enable `mod_rewrite` and point the document root at the project directory.

**Virtual host example (`/etc/apache2/sites-available/my-project.conf`):**

```apacheconf
<VirtualHost *:80>
    ServerName my-project.local
    DocumentRoot /var/www/my-project
    DirectoryIndex index.php

    <Directory /var/www/my-project>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable the site and restart Apache:

```bash
sudo a2enmod rewrite
sudo a2ensite my-project
sudo systemctl restart apache2
```

### 7.3 – Nginx

```nginx
server {
    listen 80;
    server_name my-project.local;
    root /var/www/my-project;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

Reload Nginx:

```bash
sudo nginx -t && sudo systemctl reload nginx
```

### 7.4 – File permissions

Make sure the web server user (`www-data` on Debian/Ubuntu, `nginx` or `apache` on RHEL/Fedora) can write to:

```bash
chmod -R 775 data/
chmod -R 775 log/
chown -R www-data:www-data data/ log/
```

---

## 8. Verify the Installation

After completing the steps above, open your application in a browser. You should see the Phorm RF default page.

**Manual checklist:**

- [ ] `http://localhost:8000` (or your virtual host URL) loads without errors
- [ ] No PHP errors or warnings in the browser / log files
- [ ] `vendor/` directory exists and contains the Doctrine packages
- [ ] `assets/node_modules/` directory exists
- [ ] `config/default-config.php` and `config/portal-config.php` are present

---

## 9. Build Assets for Production

During development the assets can be watched for changes:

```bash
# From the project root
cd assets
yarn watch
```

When you are ready to deploy, build optimised assets:

```bash
cd assets
yarn build    # equivalent to: yarn encore production
```

The compiled files will be placed in the `views/` directory and served by the web server.

---

## 10. Troubleshooting

### Blank page / 500 error

- Set `'debug_mode' => true` in `config/default-config.php` to enable detailed error output.
- Check the log files in `log/` and `data/`.

### `Class not found` errors

Run `composer dump-autoload` to regenerate the autoloader:

```bash
composer dump-autoload -o
```

### Database connection refused

- Verify the credentials in `config/default-config.php`.
- Make sure the database server is running (`systemctl status mysql`).
- Test the connection manually: `mysql -u db_user -p my_project`.

### Yarn / npm errors during `yarn install`

- Make sure you are using Node.js 12 or newer: `node -v`.
- Delete the lockfile and try again:
  ```bash
  rm yarn.lock
  yarn install
  ```

### Cache issues

Clear the cache directory:

```bash
rm -rf data/cache/*
```

---

## Further Reading

| Document | Description |
|----------|-------------|
| [README.md](README.md) | General framework overview |
| [QUICK_START.md](QUICK_START.md) | Get started in 5 minutes |
| [docs/README.md](docs/README.md) | German documentation index |
| [EXAMPLES.md](EXAMPLES.md) | Comprehensive code examples |
| [EVALUATION.md](EVALUATION.md) | In-depth framework evaluation |
| [modules/TodoModule](modules/TodoModule/) | Full working CRUD example module |

---

*For questions or bug reports please open an issue on [GitHub](https://github.com/dwwe2017/php-orm-react-framework/issues).*
