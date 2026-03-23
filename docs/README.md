# PHP ORM React Framework - Dokumentation

Diese Dokumentation bietet eine umfassende Übersicht über das PHP ORM React Framework (Phorm RF) mit detaillierten Beschreibungen aller Funktionen und praktischen Beispielen.

## Inhaltsverzeichnis

### Grundlagen
- [Schnellstart-Anleitung](../QUICK_START.md)
- [Installationsanleitung](../INSTALLATION.md)
- [Installation und Konfiguration](#installation)
- [Projektstruktur](#projektstruktur)

### API-Dokumentation
- [Controllers](api/CONTROLLERS.md) - Alle Controller-Typen und deren Verwendung
- [Services](api/SERVICES.md) - Verfügbare Services (Doctrine, Cache, Logger, etc.)
- [Handlers](api/HANDLERS.md) - Session, Flash Messages, Navigation, etc.
- [Annotations](api/ANNOTATIONS.md) - Routing, Navigation und Zugriffskontrolle
- [Entities](api/ENTITIES.md) - Doctrine ORM Entities

### Beispielprojekte
- [Einfache Webseite](examples/SIMPLE_WEBSITE.md) - Öffentliche Seiten erstellen
- [REST API](examples/REST_API.md) - API-Endpunkte implementieren
- [Admin Dashboard](examples/ADMIN_DASHBOARD.md) - Authentifiziertes Admin-Panel
- [Vollständige CRUD-Anwendung](examples/CRUD_APPLICATION.md) - Komplettes Beispiel

### Weitere Ressourcen
- [Code-Bewertung (DE)](../BEWERTUNG_DE.md)
- [Code Evaluation (EN)](../EVALUATION.md)
- [Ausführliche Beispiele](../EXAMPLES.md)

---

## Installation

### Voraussetzungen

- PHP 7.4 oder höher
- Composer
- Node.js und Yarn
- MySQL, PostgreSQL oder SQLite

### Neues Projekt erstellen

```bash
# Projekt erstellen
composer create-project dwwe/php-orm-react-framework mein-projekt

# In das Projektverzeichnis wechseln
cd mein-projekt

# Node.js-Abhängigkeiten installieren
yarn install

# Assets-Abhängigkeiten installieren
cd assets
yarn install
cd ..
```

### Konfiguration

```bash
# Konfigurationsdateien kopieren
cp config/default-config.php.dist config/default-config.php
cp config/portal-config.php.dist config/portal-config.php
```

Die Hauptkonfiguration befindet sich in `config/default-config.php`:

```php
<?php
return [
    'debug_mode' => true,  // Entwicklungsmodus aktivieren
    
    // Datenbankverbindung
    'connection_option' => 'default',
    'connection_options' => [
        'default' => [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'dbname' => 'mein_projekt',
            'user' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ]
    ],
    
    // Cache-Konfiguration
    'cache' => [
        'system' => [
            'driver' => 'Files',
            'path' => 'data/cache/system'
        ]
    ]
];
```

---

## Projektstruktur

```
projekt/
├── assets/                 # Frontend-Assets (React, CSS, JS)
│   ├── react/             # React-Komponenten
│   └── scss/              # SCSS-Stylesheets
├── config/                # Konfigurationsdateien
├── data/                  # Daten und Cache
│   ├── cache/            # Cache-Dateien
│   └── logs/             # Log-Dateien
├── inc/                   # Include-Dateien
├── locale/                # Übersetzungsdateien
├── log/                   # Zusätzliche Logs
├── modules/               # Anwendungsmodule
│   ├── ExampleModule/    # Beispielmodul
│   └── MeinModul/        # Eigene Module
├── system/                # Framework-Kern
│   ├── Annotations/      # Annotation-Klassen
│   ├── Controllers/      # Basis-Controller
│   ├── Entities/         # System-Entities
│   ├── Handlers/         # Handler-Klassen
│   ├── Helpers/          # Hilfsklassen
│   ├── Managers/         # Manager-Klassen
│   ├── Services/         # Service-Klassen
│   └── Traits/           # PHP Traits
├── templates/             # Twig-Vorlagen
├── views/                 # System-Views
├── composer.json          # PHP-Abhängigkeiten
├── package.json          # Node.js-Abhängigkeiten
└── index.php             # Einstiegspunkt
```

---

## Grundlegende Konzepte

### MVC-Architektur

Das Framework folgt dem Model-View-Controller-Muster:

1. **Model** - Doctrine ORM Entities für Datenbankzugriff
2. **View** - Twig-Templates für die Ausgabe
3. **Controller** - Logik und Request-Handling

### Modulsystem

Module sind eigenständige Anwendungsteile mit eigenen:
- Controllers
- Entities
- Views
- Assets

```
modules/MeinModul/
├── src/
│   ├── Controllers/
│   ├── Entities/
│   └── Repositories/
└── views/
    └── MeinController/
        └── indexAction.tpl.twig
```

### Routing

Das Routing erfolgt über URL-Parameter:

```
index.php?module=meinModul&controller=mein&action=liste
```

Wird aufgelöst zu:
- Modul: `MeinModul`
- Controller: `MeinController`
- Action: `listeAction()`

---

## Nächste Schritte

1. Lesen Sie die [API-Dokumentation](api/CONTROLLERS.md) für Details zu Controllers
2. Folgen Sie einem [Beispielprojekt](examples/SIMPLE_WEBSITE.md)
3. Studieren Sie das [TodoModule](../modules/TodoModule/) als Referenz

---

## Support

- [GitHub Issues](https://github.com/dwwe2017/php-orm-react-framework/issues)
- [Dokumentationsindex](../DOCUMENTATION_INDEX.md)
