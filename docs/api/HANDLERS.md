# Handlers - API-Dokumentation

Diese Dokumentation beschreibt alle verfügbaren Handler im PHP ORM React Framework.

## Inhaltsverzeichnis

- [Übersicht](#übersicht)
- [SessionHandler](#sessionhandler)
- [FlashHandler](#flashhandler)
- [RequestHandler](#requesthandler)
- [CacheHandler](#cachehandler)
- [NavigationHandler](#navigationhandler)
- [ErrorHandler](#errorhandler)
- [BufferHandler](#bufferhandler)

---

## Übersicht

Handler sind spezialisierte Komponenten für bestimmte Aufgaben:

| Handler | Zugriff | Beschreibung |
|---------|---------|--------------|
| SessionHandler | `$this->getSessionHandler()` | Benutzersitzung und Authentifizierung |
| FlashHandler | `$this->getFlashHandler()` | Flash-Nachrichten |
| RequestHandler | `$this->getRequestHandler()` | HTTP-Request-Daten |
| CacheHandler | `$this->getModuleCacheHandler()` | Caching-Operationen |
| NavigationHandler | `$this->getNavigationHandler()` | Navigationsmenü |
| ErrorHandler | Automatisch | Fehlerbehandlung |
| BufferHandler | `$this->getBufferHandler()` | Ausgabe-Pufferung |

---

## SessionHandler

Der SessionHandler verwaltet Benutzersitzungen und Authentifizierung.

### Zugriff

```php
$session = $this->getSessionHandler();
```

### Authentifizierung prüfen

```php
// Ist der Benutzer eingeloggt?
if ($session->isRegistered()) {
    // Benutzer ist authentifiziert
    $benutzer = $session->getUser();
    echo "Hallo " . $benutzer->getName();
} else {
    // Nicht eingeloggt
    $this->redirect('system', 'publicFront', 'login');
}
```

### Benutzerinformationen abrufen

```php
// User-Entity
$benutzer = $session->getUser();
$benutzerName = $benutzer->getName();
$benutzerEmail = $benutzer->getEmail();

// Gruppe
$gruppe = $session->getGroup();
$gruppenName = $gruppe->getName();

// Rolle
$rolle = $session->getRole();           // Numerischer Wert (10, 50, 100, 1000)
$rollenName = $session->getRoleName();  // "USER", "RESELLER", "ADMIN", "ROOT"
```

### Rollen prüfen

```php
use Entities\Group;

// Prüfen ob mindestens eine bestimmte Rolle
if ($session->hasRequiredRole(Group::ROLE_ADMIN)) {
    // Benutzer ist Admin oder höher
    $this->zeigeAdminFunktionen();
}

// Root-Prüfung
if ($session->isRoot()) {
    // Nur für Superadministratoren
    $this->zeigeSystemeinstellungen();
}
```

### Benutzer abmelden

```php
// Einfaches Logout
$session->signOut();

// Logout mit Weiterleitung
$session->signOut('?module=system&controller=publicFront&action=login');
```

### Benutzer-Entity aktualisieren

```php
$benutzer = $session->getUser();
$benutzer->setEmail('neue@email.de');
$benutzer->setLocale('de_DE');

// Änderungen speichern
$session->flush();
```

### Untergeordnete Benutzer abrufen

```php
// Alle Benutzer die vom aktuellen Benutzer angelegt wurden
$unterBenutzer = $session->getUsers();

// Als Array (für JSON oder Tabellen)
$benutzerArray = $session->getUsersArray();

// Übergeordneter Benutzer
$elternBenutzer = $session->getParentUser();
```

### Cookies löschen

```php
// Login-Cookies löschen ("Angemeldet bleiben")
$session->clearCookies();
```

---

## FlashHandler

Der FlashHandler verwaltet Flash-Nachrichten (einmalig angezeigte Benachrichtigungen).

### Zugriff

```php
$flash = $this->getFlashHandler();
```

### Nachrichten hinzufügen

```php
// Erfolgsmeldung (grün)
$flash->addFlashMessage('success', 'Änderungen erfolgreich gespeichert!');

// Fehlermeldung (rot)
$flash->addFlashMessage('danger', 'Es ist ein Fehler aufgetreten.');

// Warnung (gelb)
$flash->addFlashMessage('warning', 'Bitte beachten Sie die Änderungen.');

// Information (blau)
$flash->addFlashMessage('info', 'Neue Funktion verfügbar.');
```

### Verwendung im Workflow

```php
public function speichernAction(): void
{
    try {
        // Daten speichern
        $produkt = new Produkt();
        $produkt->setName($_POST['name']);
        
        $em = $this->getDoctrineService()->getEntityManager();
        $em->persist($produkt);
        $em->flush();
        
        // Erfolgsmeldung
        $this->getFlashHandler()->addFlashMessage(
            'success', 
            'Produkt "' . $produkt->getName() . '" wurde erstellt.'
        );
        
        // Weiterleitung zur Liste
        $this->redirect('shop', 'produkt', 'liste');
        
    } catch (\Exception $e) {
        // Fehlermeldung
        $this->getFlashHandler()->addFlashMessage(
            'danger', 
            'Speichern fehlgeschlagen: ' . $e->getMessage()
        );
        
        // Zurück zum Formular
        parent::indexAction();
    }
}
```

### Flash-Nachrichten in Templates

Flash-Nachrichten werden automatisch im Template angezeigt. Sie können auch manuell darauf zugreifen:

```twig
{% if message is defined and message is not empty %}
    {% for type, messages in message %}
        {% for msg in messages %}
            <div class="alert alert-{{ type }} alert-dismissible fade show" role="alert">
                {{ msg }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        {% endfor %}
    {% endfor %}
{% endif %}
```

---

## RequestHandler

Der RequestHandler bietet Zugriff auf HTTP-Request-Daten.

### Zugriff

```php
$request = $this->getRequestHandler();
```

### GET-Parameter

```php
// Alle GET-Parameter
$query = $request->getQuery();

// Einzelner Parameter mit Standardwert
$seite = $query->get('seite', 1);
$filter = $query->get('filter', 'alle');
$suche = $query->get('suche', '');
```

### POST-Parameter

```php
// Alle POST-Parameter
$post = $request->getPost();

// Einzelner Parameter
$name = $post->get('name', '');
$email = $post->get('email', '');
```

### Request-Typ prüfen

```php
// XML/JSON-Request?
if ($request->isXml()) {
    // Antwortet als JSON
}

// API-Request?
if ($request->isApi()) {
    // API-spezifische Logik
}

// Ist es ein AJAX-Request?
if ($request->isXmlRequest()) {
    // AJAX-Antwort
}
```

### URLs

```php
// Aktuelle Request-URL
$aktuelleUrl = $request->getRequestUrl();

// Basis-URL der Anwendung
$basisUrl = $request->getBaseUrl();
```

### Weiterleitung

```php
// Standard-Weiterleitung nach Login
$request->doRedirect();
```

---

## CacheHandler

Der CacheHandler ermöglicht effizientes Caching von Daten.

### Zugriff

```php
// Modul-Cache (für Anwendungsdaten)
$cache = $this->getModuleCacheHandler();

// System-Cache (für Framework-Daten)
$systemCache = $this->getSystemCacheHandler();
```

### Grundlegende Operationen

```php
// Wert speichern (TTL in Sekunden)
$cache->set('mein_schluessel', $wert, 3600);  // 1 Stunde

// Wert abrufen
$wert = $cache->get('mein_schluessel');

// Prüfen ob vorhanden
if ($cache->has('mein_schluessel')) {
    // Cache-Hit
    $wert = $cache->get('mein_schluessel');
} else {
    // Cache-Miss - neu berechnen
    $wert = $this->berechneAufwendig();
    $cache->set('mein_schluessel', $wert, 3600);
}

// Löschen
$cache->delete('mein_schluessel');

// Alles löschen
$cache->clear();
```

### Cache-Pattern für teure Operationen

```php
public function getStatistik(): array
{
    $cache = $this->getModuleCacheHandler();
    $schluessel = 'dashboard_statistik_' . date('Y-m-d-H');
    
    $statistik = $cache->get($schluessel);
    
    if ($statistik === null) {
        // Nicht im Cache - berechnen
        $em = $this->getDoctrineService()->getEntityManager();
        
        $statistik = [
            'bestellungen_heute' => $this->zaehleBestellungenHeute($em),
            'umsatz_heute' => $this->berechneUmsatzHeute($em),
            'neue_kunden' => $this->zaehleNeueKunden($em),
            'bestand_kritisch' => $this->findeKritischenBestand($em)
        ];
        
        // 15 Minuten cachen
        $cache->set($schluessel, $statistik, 900);
    }
    
    return $statistik;
}
```

### Cache invalidieren

```php
public function bestellungSpeichern(Bestellung $bestellung): void
{
    $em = $this->getDoctrineService()->getEntityManager();
    $em->persist($bestellung);
    $em->flush();
    
    // Cache invalidieren
    $cache = $this->getModuleCacheHandler();
    $cache->delete('dashboard_statistik_' . date('Y-m-d-H'));
}
```

---

## NavigationHandler

Der NavigationHandler verwaltet die Navigationsstruktur der Anwendung.

### Zugriff

```php
$navigation = $this->getNavigationHandler();
```

### Navigation abrufen

```php
// Alle Routen für eine Position
$sidebarRouten = $navigation->getRoutes('sidebar');
$topLeftRouten = $navigation->getRoutes('top_left');
$topRightRouten = $navigation->getRoutes('top_right');
```

### Navigation in Templates

```twig
{# In layout.default.body.tpl.twig #}
<nav class="sidebar">
    <ul class="nav">
        {% for route in navigation_routes %}
            <li class="nav-item">
                <a class="nav-link" href="{{ route.href }}">
                    {% if route.icon %}
                        <i class="{{ route.icon }}"></i>
                    {% endif %}
                    {{ route.text }}
                    {% if route.badge %}
                        <span class="badge badge-{{ route.badgeClass }}">
                            {{ route.badge }}
                        </span>
                    {% endif %}
                </a>
                
                {# Untermenü #}
                {% if route.children is defined and route.children is not empty %}
                    <ul class="nav-dropdown-items">
                        {% for child in route.children %}
                            <li class="nav-item">
                                <a class="nav-link" href="{{ child.href }}">
                                    <i class="{{ child.icon }}"></i>
                                    {{ child.text }}
                                </a>
                            </li>
                        {% endfor %}
                    </ul>
                {% endif %}
            </li>
        {% endfor %}
    </ul>
</nav>
```

---

## ErrorHandler

Der ErrorHandler wird automatisch initialisiert und behandelt Fehler.

### Konfiguration

```php
// In config/default-config.php
return [
    'debug_mode' => true,  // Detaillierte Fehlerseiten im Entwicklungsmodus
    // ...
];
```

### Verhalten

- **Debug-Modus AN**: Whoops-Fehlerseiten mit Stack-Trace
- **Debug-Modus AUS**: Benutzerfreundliche Fehlerseiten

### Manuelle Fehlerbehandlung

```php
try {
    $this->gefaehrlicheOperation();
} catch (\Exception $e) {
    // Fehler loggen
    $this->getLoggerService()->error($e->getMessage(), [
        'exception' => get_class($e),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Benutzerfreundliche Meldung
    $this->getFlashHandler()->addFlashMessage(
        'danger', 
        'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.'
    );
    
    // Optional: Weiterleitung
    $this->redirect('system', 'publicFront', 'fehler');
}
```

### Fehlerseiten

```php
// 404 - Nicht gefunden
$this->render404();

// 403 - Zugriff verweigert
$this->render403();

// 403 mit Login-Weiterleitung
$this->render403(true);  // Leitet zur Login-Seite
```

---

## BufferHandler

Der BufferHandler verwaltet die Ausgabe-Pufferung für optimierte Responses.

### Zugriff

```php
$buffer = $this->getBufferHandler();
```

### Verwendung

Der BufferHandler wird hauptsächlich intern verwendet, um:
- Ausgaben zu puffern
- Komprimierung zu ermöglichen
- Caching von generierten Inhalten

```php
// Puffer starten
$buffer->start();

// Inhalt generieren...
echo "Mein Inhalt";

// Puffer beenden und Inhalt abrufen
$inhalt = $buffer->end();
```

---

## Zusammenfassung: Häufige Muster

### Vollständige Controller-Action mit allen Handlers

```php
public function bearbeitenAction(): void
{
    // 1. Request-Daten abrufen
    $id = $this->getRequestHandler()->getQuery()->get('id');
    
    // 2. Authentifizierung prüfen
    if (!$this->getSessionHandler()->isRegistered()) {
        $this->render403(true);  // Zur Login-Seite
        return;
    }
    
    // 3. Berechtigungen prüfen
    if (!$this->getSessionHandler()->hasRequiredRole(Group::ROLE_ADMIN)) {
        $this->getFlashHandler()->addFlashMessage('danger', 'Keine Berechtigung');
        $this->redirect('meinModul', 'start', 'index');
        return;
    }
    
    // 4. Daten aus Cache oder Datenbank
    $cache = $this->getModuleCacheHandler();
    $cacheKey = 'produkt_' . $id;
    
    $produkt = $cache->get($cacheKey);
    if (!$produkt) {
        $em = $this->getDoctrineService()->getEntityManager();
        $produkt = $em->find(Produkt::class, $id);
        
        if ($produkt) {
            $cache->set($cacheKey, $produkt, 300);  // 5 Minuten
        }
    }
    
    if (!$produkt) {
        $this->render404();
        return;
    }
    
    // 5. Formular verarbeiten
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $produkt->setName($_POST['name'] ?? $produkt->getName());
            $produkt->setPreis((float)($_POST['preis'] ?? $produkt->getPreis()));
            
            $em->flush();
            
            // Cache invalidieren
            $cache->delete($cacheKey);
            
            $this->getFlashHandler()->addFlashMessage('success', 'Gespeichert!');
            
            // Loggen
            $this->getLoggerService()->info('Produkt aktualisiert', [
                'produkt_id' => $produkt->getId(),
                'benutzer_id' => $this->getSessionHandler()->getUser()->getId()
            ]);
            
            $this->redirect('meinModul', 'produkt', 'index');
            return;
            
        } catch (\Exception $e) {
            $this->getLoggerService()->error('Speichern fehlgeschlagen', [
                'error' => $e->getMessage()
            ]);
            
            $this->getFlashHandler()->addFlashMessage('danger', 'Fehler beim Speichern');
        }
    }
    
    // 6. View vorbereiten
    $this->getView()->assign('produkt', $produkt);
    parent::indexAction();
}
```

---

## Weitere Informationen

- [Controller-Dokumentation](CONTROLLERS.md)
- [Services-Dokumentation](SERVICES.md)
- [Beispielprojekte](../examples/SIMPLE_WEBSITE.md)
