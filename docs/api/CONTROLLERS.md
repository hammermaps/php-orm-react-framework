# Controllers - API-Dokumentation

Diese Dokumentation beschreibt alle verfügbaren Controller-Typen und deren Verwendung im PHP ORM React Framework.

## Inhaltsverzeichnis

- [Controller-Hierarchie](#controller-hierarchie)
- [PublicFrontController](#publicfrontcontroller)
- [RestrictedFrontController](#restrictedfrontcontroller)
- [ApiController](#apicontroller)
- [Controller-Methoden](#controller-methoden)
- [Verfügbare Services](#verfügbare-services)

---

## Controller-Hierarchie

```
AbstractBase
├── PublicController
│   ├── PublicFrontController      # Öffentliche Webseiten
│   ├── PublicInvokeController     # Öffentliche CLI-Aufrufe
│   └── PublicXmlController        # Öffentliche XML/JSON-Ausgabe
├── RestrictedController
│   ├── RestrictedFrontController  # Authentifizierte Webseiten
│   ├── RestrictedInvokeController # Authentifizierte CLI-Aufrufe
│   └── RestrictedXmlController    # Authentifizierte XML/JSON
│       └── ApiController          # REST-API-Endpunkte
├── SettingsController
│   └── SettingsFrontController    # Einstellungsseiten
└── DispatchController             # Haupt-Routing
```

---

## PublicFrontController

Für öffentlich zugängliche Webseiten ohne Authentifizierung.

### Verwendung

```php
<?php
namespace Modules\MeinModul\Controllers;

use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\PublicFrontController;

/**
 * @Navigation(text="Startseite", position="sidebar")
 */
class StartseiteController extends PublicFrontController
{
    /**
     * @SubNavigation(text="Home", icon="cil-home")
     */
    public function indexAction(): void
    {
        // Daten an View übergeben
        $this->getView()->assign('titel', 'Willkommen');
        $this->getView()->assign('inhalt', 'Das ist meine Seite');
        
        // Basis-Aktion aufrufen (rendert das Template)
        parent::indexAction();
    }
    
    /**
     * @SubNavigation(text="Über uns", icon="cil-info")
     */
    public function ueberAction(): void
    {
        $this->getView()->assign('team', [
            ['name' => 'Max', 'rolle' => 'Entwickler'],
            ['name' => 'Anna', 'rolle' => 'Designer']
        ]);
        parent::indexAction();
    }
    
    /**
     * @SubNavigation(text="Kontakt", icon="cil-envelope-closed")
     */
    public function kontaktAction(): void
    {
        // Formular verarbeiten
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verarbeiteKontaktformular();
            return;
        }
        
        parent::indexAction();
    }
    
    private function verarbeiteKontaktformular(): void
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $nachricht = $_POST['nachricht'] ?? '';
        
        // Validierung
        if (empty($name) || empty($email) || empty($nachricht)) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Bitte alle Felder ausfüllen');
            parent::indexAction();
            return;
        }
        
        // E-Mail senden oder speichern...
        
        $this->getFlashHandler()->addFlashMessage('success', 'Nachricht gesendet!');
        $this->redirect('meinModul', 'startseite', 'kontakt');
    }
}
```

### Entsprechende Views

**`modules/MeinModul/views/StartseiteController/indexAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="card">
        <div class="card-header">
            <h2>{{ titel }}</h2>
        </div>
        <div class="card-body">
            <p>{{ inhalt }}</p>
        </div>
    </div>
</div>
{% endblock %}
```

---

## RestrictedFrontController

Für Seiten, die Authentifizierung erfordern.

### Verwendung

```php
<?php
namespace Modules\MeinModul\Controllers;

use Annotations\Access;
use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\RestrictedFrontController;
use Entities\Group;

/**
 * Admin-Bereich - erfordert mindestens USER-Rolle
 * 
 * @Navigation(text="Dashboard", position="sidebar")
 * @Access(role=Group::ROLE_USER)
 */
class DashboardController extends RestrictedFrontController
{
    /**
     * @SubNavigation(text="Übersicht", icon="cil-speedometer")
     */
    public function indexAction(): void
    {
        // Benutzerinformationen abrufen
        $benutzer = $this->getSessionHandler()->getUser();
        $benutzerId = $this->getSessionHandler()->getUserId();
        
        $this->getView()->assign('benutzer', $benutzer);
        $this->getView()->assign('willkommen', 'Willkommen, ' . $benutzer->getName());
        
        parent::indexAction();
    }
    
    /**
     * Nur für Administratoren
     * 
     * @SubNavigation(text="Einstellungen", icon="cil-settings")
     * @Access(role=Group::ROLE_ADMIN)
     */
    public function einstellungenAction(): void
    {
        // Nur Admins können diese Seite sehen
        parent::indexAction();
    }
    
    /**
     * Benutzerprofil bearbeiten
     * 
     * @SubNavigation(text="Profil", icon="cil-user")
     */
    public function profilAction(): void
    {
        $benutzer = $this->getSessionHandler()->getUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->aktualisiereProfile($benutzer);
            return;
        }
        
        $this->getView()->assign('benutzer', $benutzer);
        parent::indexAction();
    }
    
    private function aktualisiereProfile($benutzer): void
    {
        $email = $_POST['email'] ?? '';
        
        if (!empty($email)) {
            $benutzer->setEmail($email);
            $this->getSessionHandler()->flush();
            
            $this->getFlashHandler()->addFlashMessage('success', 'Profil aktualisiert');
        }
        
        $this->redirect('meinModul', 'dashboard', 'profil');
    }
}
```

### Rollen-Konstanten

```php
use Entities\Group;

Group::ROLE_ROOT     // = 1000 - Superadmin
Group::ROLE_ADMIN    // = 100  - Administrator
Group::ROLE_RESELLER // = 50   - Reseller
Group::ROLE_USER     // = 10   - Normaler Benutzer
Group::ROLE_ANY      // = 0    - Jeder (auch nicht eingeloggt)
```

### Rollen programmatisch prüfen

```php
// Prüfen ob Benutzer mindestens die erforderliche Rolle hat
if ($this->getSessionHandler()->hasRequiredRole(Group::ROLE_ADMIN)) {
    // Benutzer ist Admin oder höher
}

// Prüfen ob Benutzer Root ist
if ($this->getSessionHandler()->isRoot()) {
    // Benutzer ist Superadmin
}

// Aktuelle Rolle abrufen
$rolle = $this->getSessionHandler()->getRole();
$rollenName = $this->getSessionHandler()->getRoleName(); // "ADMIN", "USER", etc.
```

---

## ApiController

Für REST-API-Endpunkte mit JSON-Ausgabe.

### Verwendung

```php
<?php
namespace Modules\MeinModul\Controllers;

use Controllers\ApiController;
use Modules\MeinModul\Entities\Produkt;

class ProduktApiController extends ApiController
{
    /**
     * GET /api/produkte
     * URL: ?module=meinModul&controller=produktApi&action=index
     */
    public function indexAction(): void
    {
        $entityManager = $this->getDoctrineService()->getEntityManager();
        $produkte = $entityManager->getRepository(Produkt::class)->findAll();
        
        $daten = array_map(function(Produkt $produkt) {
            return [
                'id' => $produkt->getId(),
                'name' => $produkt->getName(),
                'preis' => $produkt->getPreis(),
                'erstellt_am' => $produkt->getErstelltAm()->format('Y-m-d H:i:s')
            ];
        }, $produkte);
        
        $this->jsonAntwort(['erfolg' => true, 'daten' => $daten]);
    }
    
    /**
     * GET /api/produkte/zeigen?id=1
     * URL: ?module=meinModul&controller=produktApi&action=zeigen&id=1
     */
    public function zeigenAction(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id) {
            $this->jsonAntwort(['erfolg' => false, 'fehler' => 'ID erforderlich'], 400);
            return;
        }
        
        $entityManager = $this->getDoctrineService()->getEntityManager();
        $produkt = $entityManager->find(Produkt::class, $id);
        
        if (!$produkt) {
            $this->jsonAntwort(['erfolg' => false, 'fehler' => 'Produkt nicht gefunden'], 404);
            return;
        }
        
        $this->jsonAntwort([
            'erfolg' => true,
            'daten' => [
                'id' => $produkt->getId(),
                'name' => $produkt->getName(),
                'beschreibung' => $produkt->getBeschreibung(),
                'preis' => $produkt->getPreis()
            ]
        ]);
    }
    
    /**
     * POST /api/produkte/erstellen
     */
    public function erstellenAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonAntwort(['erfolg' => false, 'fehler' => 'Nur POST erlaubt'], 405);
            return;
        }
        
        // JSON-Body lesen
        $eingabe = json_decode(file_get_contents('php://input'), true);
        
        $name = $eingabe['name'] ?? '';
        $preis = $eingabe['preis'] ?? 0;
        
        if (empty($name)) {
            $this->jsonAntwort(['erfolg' => false, 'fehler' => 'Name erforderlich'], 400);
            return;
        }
        
        $produkt = new Produkt();
        $produkt->setName($name);
        $produkt->setPreis($preis);
        
        try {
            $entityManager = $this->getDoctrineService()->getEntityManager();
            $entityManager->persist($produkt);
            $entityManager->flush();
            
            $this->jsonAntwort([
                'erfolg' => true,
                'daten' => ['id' => $produkt->getId()],
                'nachricht' => 'Produkt erstellt'
            ], 201);
        } catch (\Exception $e) {
            $this->jsonAntwort(['erfolg' => false, 'fehler' => 'Erstellung fehlgeschlagen'], 500);
        }
    }
    
    /**
     * DELETE /api/produkte/loeschen?id=1
     */
    public function loeschenAction(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        
        $entityManager = $this->getDoctrineService()->getEntityManager();
        $produkt = $entityManager->find(Produkt::class, $id);
        
        if (!$produkt) {
            $this->jsonAntwort(['erfolg' => false, 'fehler' => 'Produkt nicht gefunden'], 404);
            return;
        }
        
        try {
            $entityManager->remove($produkt);
            $entityManager->flush();
            
            $this->jsonAntwort(['erfolg' => true, 'nachricht' => 'Produkt gelöscht']);
        } catch (\Exception $e) {
            $this->jsonAntwort(['erfolg' => false, 'fehler' => 'Löschen fehlgeschlagen'], 500);
        }
    }
    
    /**
     * Hilfsmethode für JSON-Antworten
     */
    private function jsonAntwort(array $daten, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($daten, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
```

---

## Controller-Methoden

### Basis-Methoden (AbstractBase)

```php
// View/Template
$this->getView()->assign('variable', $wert);     // Variable an View übergeben
$this->setTemplate('meinTemplate');               // Template setzen

// Umleitung
$this->redirect('modul', 'controller', 'action', ['param' => 'wert']);
$this->historyBack();                             // Zur vorherigen Seite

// Fehlerseiten
$this->render404();                               // 404 Not Found
$this->render403();                               // 403 Forbidden

// Kontext
$this->addContext('key', $value);                 // Kontext hinzufügen
$this->contextPush(['key1' => $val1, 'key2' => $val2]);
```

### Session-Handler

```php
$session = $this->getSessionHandler();

$session->isRegistered();           // Ist eingeloggt?
$session->getUser();                // User-Entity
$session->getGroup();               // Gruppe des Users
$session->getRole();                // Rollen-Level (int)
$session->getRoleName();            // Rollen-Name ("ADMIN" etc.)
$session->hasRequiredRole($level);  // Hat mindestens Rolle?
$session->isRoot();                 // Ist Superadmin?
$session->signOut();                // Abmelden
$session->flush();                  // Änderungen speichern
```

### Flash-Handler

```php
$flash = $this->getFlashHandler();

$flash->addFlashMessage('success', 'Erfolgreich!');
$flash->addFlashMessage('danger', 'Fehler aufgetreten');
$flash->addFlashMessage('warning', 'Warnung');
$flash->addFlashMessage('info', 'Information');
```

### Doctrine-Service

```php
$doctrine = $this->getDoctrineService();
$em = $doctrine->getEntityManager();

// Entity finden
$entity = $em->find(MeineEntity::class, $id);

// Repository verwenden
$repo = $em->getRepository(MeineEntity::class);
$alle = $repo->findAll();
$gefiltert = $repo->findBy(['status' => 'aktiv']);
$eines = $repo->findOneBy(['name' => 'Test']);

// Entity speichern
$em->persist($entity);
$em->flush();

// Entity löschen
$em->remove($entity);
$em->flush();
```

### Request-Handler

```php
$request = $this->getRequestHandler();

$request->getQuery();           // $_GET als Collection
$request->getPost();            // $_POST als Collection
$request->isXml();              // Ist XML/JSON-Request?
$request->isApi();              // Ist API-Request?
$request->getRequestUrl();      // Aktuelle URL
$request->getBaseUrl();         // Basis-URL
```

### Cache-Handler

```php
$cache = $this->getModuleCacheHandler();

$cache->set('schluessel', $wert, 3600);  // 1 Stunde TTL
$wert = $cache->get('schluessel');
$cache->has('schluessel');
$cache->delete('schluessel');
$cache->clear();
```

---

## Verfügbare Services

| Service | Methode | Beschreibung |
|---------|---------|--------------|
| Doctrine | `getDoctrineService()` | Datenbank-ORM |
| Cache | `getCacheService()` | Caching-System |
| Logger | `getLoggerService()` | Logging (Monolog) |
| Template | `getTemplateService()` | Twig-Templates |
| Locale | `getLocaleService()` | Übersetzungen |

---

## Lebenszyklus einer Anfrage

1. **index.php** - Einstiegspunkt
2. **DispatchController** - Routing
3. **AbstractBase::__construct()** - Initialisierung
4. **AbstractBase::run($action)** - Action ausführen
5. **preRun()** - Vor-Verarbeitung
6. **betRun()** - Zugriffskontrollen
7. **postRun()** - Annotationen verarbeiten
8. **{action}Action()** - Controller-Methode
9. **render()** - Template rendern

---

## Weitere Informationen

- [Services-Dokumentation](SERVICES.md)
- [Annotations-Dokumentation](ANNOTATIONS.md)
- [Beispiele](../examples/SIMPLE_WEBSITE.md)
