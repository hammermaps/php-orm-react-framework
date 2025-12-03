# Annotations - API-Dokumentation

Diese Dokumentation beschreibt alle verfügbaren Annotations für Routing, Navigation und Zugriffskontrolle.

## Inhaltsverzeichnis

- [Übersicht](#übersicht)
- [Navigation](#navigation)
- [SubNavigation](#subnavigation)
- [SubRoute / SubRoutes](#subroute--subroutes)
- [Access](#access)
- [Redirect](#redirect)
- [Vollständiges Beispiel](#vollständiges-beispiel)

---

## Übersicht

Annotations werden verwendet, um Metadaten zu Controllern und Methoden hinzuzufügen:

| Annotation | Ziel | Beschreibung |
|------------|------|--------------|
| `@Navigation` | Klasse | Hauptnavigationspunkt für Controller |
| `@SubNavigation` | Methode | Untermenüpunkt für Action |
| `@SubRoute` | Methode | Dropdown-Menüeintrag |
| `@SubRoutes` | Methode | Container für mehrere SubRoutes |
| `@Access` | Klasse/Methode | Zugriffskontrolle |
| `@Redirect` | Methode | Automatische Weiterleitung |

---

## Navigation

Die `@Navigation`-Annotation definiert einen Hauptnavigationspunkt für einen Controller.

### Syntax

```php
/**
 * @Navigation(text="Menütext", position="sidebar", icon="cil-home")
 */
class MeinController extends PublicFrontController
```

### Parameter

| Parameter | Typ | Beschreibung | Pflicht |
|-----------|-----|--------------|---------|
| `text` | string | Anzeigetext im Menü | Ja |
| `position` | string | Position: `sidebar`, `top_left`, `top_right`, `misc` | Nein |
| `icon` | string | CoreUI Icon-Klasse | Nein |
| `class` | string | Zusätzliche CSS-Klasse | Nein |
| `hidden` | bool | Menüpunkt verstecken | Nein |
| `badge` | string | Badge-Text | Nein |
| `badgeClass` | string | Badge-Farbe: `info`, `success`, `warning`, `danger` | Nein |
| `requiredGetParams` | array | Erforderliche GET-Parameter | Nein |
| `isLabel` | bool | Als Label anzeigen | Nein |
| `labelClass` | string | Label-Farbe | Nein |
| `labelIcon` | string | Label-Icon | Nein |
| `title` | string | Tooltip-Text | Nein |
| `style` | string | Inline-CSS | Nein |
| `description` | string | Beschreibung | Nein |
| `href` | string | Benutzerdefinierter Link | Nein |
| `target` | string | Link-Ziel: `_blank`, `_self`, `_parent` | Nein |

### Beispiele

```php
/**
 * Einfacher Navigationsmenüpunkt
 * @Navigation(text="Dashboard", position="sidebar")
 */
class DashboardController extends RestrictedFrontController {}

/**
 * Mit Icon und Badge
 * @Navigation(
 *     text="Benachrichtigungen",
 *     position="top_right",
 *     icon="cil-bell",
 *     badge="5",
 *     badgeClass="danger"
 * )
 */
class BenachrichtigungController extends RestrictedFrontController {}

/**
 * Versteckter Menüpunkt (für interne Seiten)
 * @Navigation(text="Intern", hidden=true)
 */
class InternController extends RestrictedFrontController {}

/**
 * Als Label mit Beschreibung
 * @Navigation(
 *     text="VERWALTUNG",
 *     position="sidebar",
 *     isLabel=true,
 *     labelClass="info"
 * )
 */
class VerwaltungController extends RestrictedFrontController {}

/**
 * Externer Link
 * @Navigation(
 *     text="Hilfe",
 *     position="top_right",
 *     icon="cil-help",
 *     href="https://docs.example.com",
 *     target="_blank"
 * )
 */
class HilfeController extends PublicFrontController {}
```

---

## SubNavigation

Die `@SubNavigation`-Annotation definiert Untermenüpunkte für Controller-Actions.

### Syntax

```php
/**
 * @SubNavigation(text="Untermenü", icon="cil-list")
 */
public function listeAction(): void
```

### Parameter

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `text` | string | Anzeigetext |
| `icon` | string | CoreUI Icon-Klasse |
| `class` | string | CSS-Klasse |
| `hidden` | bool | Verstecken |
| `badge` | string | Badge-Text |
| `badgeClass` | string | Badge-Farbe |
| `requiredGetParams` | array | Erforderliche Parameter |
| `hrefQueryAddition` | array | Zusätzliche Query-Parameter |
| `title` | string | Tooltip |
| `style` | string | Inline-CSS |
| `isChild` | bool | Als Kind-Element |
| `target` | string | Link-Ziel |

### Beispiele

```php
class ProduktController extends RestrictedFrontController
{
    /**
     * Einfacher Untermenüpunkt
     * @SubNavigation(text="Alle Produkte", icon="cil-list")
     */
    public function indexAction(): void {}
    
    /**
     * Mit Badge
     * @SubNavigation(
     *     text="Neue Produkte",
     *     icon="cil-plus",
     *     badge="NEU",
     *     badgeClass="success"
     * )
     */
    public function neueAction(): void {}
    
    /**
     * Versteckt (nicht im Menü, aber aufrufbar)
     * @SubNavigation(text="Details", icon="cil-info", hidden=true)
     */
    public function detailsAction(): void {}
    
    /**
     * Nur anzeigen wenn ID vorhanden
     * @SubNavigation(
     *     text="Bearbeiten",
     *     icon="cil-pencil",
     *     requiredGetParams={"id"}
     * )
     */
    public function bearbeitenAction(): void {}
    
    /**
     * Mit zusätzlichen Query-Parametern
     * @SubNavigation(
     *     text="Aktive Produkte",
     *     icon="cil-check",
     *     hrefQueryAddition={"status": "aktiv"}
     * )
     */
    public function filterAction(): void {}
}
```

---

## SubRoute / SubRoutes

`@SubRoute` und `@SubRoutes` erstellen Dropdown-Menüs.

### Syntax

```php
/**
 * @SubNavigation(text="Filter", icon="cil-filter")
 * @SubRoutes(routes={
 *     @SubRoute(text="Option 1", icon="cil-check", hrefQueryAddition={"filter": "1"}),
 *     @SubRoute(text="Option 2", icon="cil-x", hrefQueryAddition={"filter": "2"})
 * })
 */
public function filterAction(): void
```

### SubRoute-Parameter

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `text` | string | Anzeigetext |
| `icon` | string | Icon-Klasse |
| `hrefQueryAddition` | array | Query-Parameter |
| `class` | string | CSS-Klasse |
| `style` | string | Inline-CSS |

### Beispiele

```php
class BestellungController extends RestrictedFrontController
{
    /**
     * Dropdown mit Filteroptionen
     * 
     * @SubNavigation(text="Status-Filter", icon="cil-filter")
     * @SubRoutes(routes={
     *     @SubRoute(text="Alle", icon="cil-list", hrefQueryAddition={"status": "alle"}),
     *     @SubRoute(text="Offen", icon="cil-clock", hrefQueryAddition={"status": "offen"}),
     *     @SubRoute(text="In Bearbeitung", icon="cil-cog", hrefQueryAddition={"status": "bearbeitung"}),
     *     @SubRoute(text="Abgeschlossen", icon="cil-check", hrefQueryAddition={"status": "abgeschlossen"}),
     *     @SubRoute(text="Storniert", icon="cil-x", hrefQueryAddition={"status": "storniert"})
     * })
     */
    public function listeAction(): void
    {
        $status = $_GET['status'] ?? 'alle';
        // Filter anwenden...
    }
}

class ProduktController extends RestrictedFrontController
{
    /**
     * Dropdown mit Kategorien
     * 
     * @SubNavigation(text="Kategorien", icon="cil-folder")
     * @SubRoutes(routes={
     *     @SubRoute(text="Elektronik", icon="cil-laptop", hrefQueryAddition={"kategorie": "elektronik"}),
     *     @SubRoute(text="Kleidung", icon="cil-shirt", hrefQueryAddition={"kategorie": "kleidung"}),
     *     @SubRoute(text="Bücher", icon="cil-book", hrefQueryAddition={"kategorie": "buecher"}),
     *     @SubRoute(text="Sport", icon="cil-running", hrefQueryAddition={"kategorie": "sport"})
     * })
     */
    public function kategorieAction(): void
    {
        $kategorie = $_GET['kategorie'] ?? 'alle';
        // Produkte nach Kategorie laden...
    }
}
```

---

## Access

Die `@Access`-Annotation kontrolliert den Zugriff basierend auf Benutzerrollen.

### Syntax

```php
/**
 * @Access(role=Group::ROLE_ADMIN)
 */
```

### Rollen

```php
use Entities\Group;

Group::ROLE_ROOT     // 1000 - Superadministrator
Group::ROLE_ADMIN    // 100  - Administrator
Group::ROLE_RESELLER // 50   - Reseller/Partner
Group::ROLE_USER     // 10   - Normaler Benutzer
Group::ROLE_ANY      // 0    - Jeder (Standard für öffentliche Seiten)
```

### Beispiele

```php
use Annotations\Access;
use Entities\Group;

/**
 * Gesamter Controller nur für Administratoren
 * @Access(role=Group::ROLE_ADMIN)
 */
class AdminController extends RestrictedFrontController
{
    public function indexAction(): void {}
    
    /**
     * Diese Action nur für Root-User
     * @Access(role=Group::ROLE_ROOT)
     */
    public function systemAction(): void {}
}

/**
 * Controller für normale Benutzer
 * @Access(role=Group::ROLE_USER)
 */
class ProfilController extends RestrictedFrontController
{
    // Normaler Benutzer kann zugreifen
    public function indexAction(): void {}
    
    // Ebenfalls für normale Benutzer
    public function einstellungenAction(): void {}
    
    /**
     * Nur Admins können Benutzer verwalten
     * @Access(role=Group::ROLE_ADMIN)
     */
    public function benutzerVerwaltungAction(): void {}
}

/**
 * Controller für Reseller und höher
 * @Access(role=Group::ROLE_RESELLER)
 */
class PartnerController extends RestrictedFrontController
{
    public function indexAction(): void {}
}
```

### Hierarchie

Die Zugriffsprüfung ist hierarchisch - höhere Rollen haben automatisch Zugriff auf niedrigere:

- `ROLE_ROOT` (1000) kann alles
- `ROLE_ADMIN` (100) kann auf RESELLER und USER zugreifen
- `ROLE_RESELLER` (50) kann auf USER zugreifen
- `ROLE_USER` (10) nur auf eigene Ebene

---

## Redirect

Die `@Redirect`-Annotation leitet automatisch zu einer anderen Action um.

### Syntax

```php
/**
 * @Redirect(module="meinModul", controller="start", action="index")
 */
```

### Parameter

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `module` | string | Ziel-Modul |
| `controller` | string | Ziel-Controller |
| `action` | string | Ziel-Action |
| `querys` | array | Query-Parameter |
| `tab` | string | Anchor-Fragment |

### Beispiele

```php
class AltController extends PublicFrontController
{
    /**
     * Alte URL leitet zu neuer um
     * @Redirect(module="shop", controller="produkt", action="liste")
     */
    public function produkteAction(): void {}
    
    /**
     * Mit Query-Parametern
     * @Redirect(
     *     module="dashboard",
     *     controller="start",
     *     action="index",
     *     querys={"willkommen": "1"}
     * )
     */
    public function startAction(): void {}
    
    /**
     * Mit Anchor
     * @Redirect(
     *     module="hilfe",
     *     controller="faq",
     *     action="index",
     *     tab="#installation"
     * )
     */
    public function hilfeInstallationAction(): void {}
}
```

---

## Vollständiges Beispiel

Hier ein komplettes Beispiel mit allen Annotations:

```php
<?php
namespace Modules\ShopModule\Controllers;

use Annotations\Access;
use Annotations\Navigation;
use Annotations\SubNavigation;
use Annotations\SubRoute;
use Annotations\SubRoutes;
use Controllers\RestrictedFrontController;
use Entities\Group;
use Modules\ShopModule\Entities\Produkt;

/**
 * Produktverwaltung
 * 
 * @Navigation(
 *     text="Produkte",
 *     position="sidebar",
 *     icon="cil-cart",
 *     badge="Shop",
 *     badgeClass="info"
 * )
 * @Access(role=Group::ROLE_USER)
 */
class ProduktController extends RestrictedFrontController
{
    /**
     * Produktliste mit Filtermöglichkeiten
     * 
     * @SubNavigation(text="Alle Produkte", icon="cil-list")
     */
    public function indexAction(): void
    {
        $em = $this->getDoctrineService()->getEntityManager();
        $produkte = $em->getRepository(Produkt::class)->findAll();
        
        $this->getView()->assign('produkte', $produkte);
        parent::indexAction();
    }
    
    /**
     * Filter nach Status
     * 
     * @SubNavigation(text="Status", icon="cil-filter")
     * @SubRoutes(routes={
     *     @SubRoute(text="Aktiv", icon="cil-check-circle", hrefQueryAddition={"status": "aktiv"}),
     *     @SubRoute(text="Inaktiv", icon="cil-x-circle", hrefQueryAddition={"status": "inaktiv"}),
     *     @SubRoute(text="Ausverkauft", icon="cil-warning", hrefQueryAddition={"status": "ausverkauft"})
     * })
     */
    public function statusAction(): void
    {
        $status = $_GET['status'] ?? 'alle';
        
        $em = $this->getDoctrineService()->getEntityManager();
        
        if ($status === 'alle') {
            $produkte = $em->getRepository(Produkt::class)->findAll();
        } else {
            $produkte = $em->getRepository(Produkt::class)->findBy(['status' => $status]);
        }
        
        $this->getView()->assign('produkte', $produkte);
        $this->getView()->assign('aktuellerStatus', $status);
        parent::indexAction();
    }
    
    /**
     * Kategorien-Filter
     * 
     * @SubNavigation(text="Kategorien", icon="cil-folder")
     * @SubRoutes(routes={
     *     @SubRoute(text="Elektronik", icon="cil-laptop", hrefQueryAddition={"kategorie": "elektronik"}),
     *     @SubRoute(text="Mode", icon="cil-shirt", hrefQueryAddition={"kategorie": "mode"}),
     *     @SubRoute(text="Sport", icon="cil-running", hrefQueryAddition={"kategorie": "sport"})
     * })
     */
    public function kategorieAction(): void
    {
        $kategorie = $_GET['kategorie'] ?? '';
        
        // Produkte laden...
        parent::indexAction();
    }
    
    /**
     * Neues Produkt anlegen - nur für Admins
     * 
     * @SubNavigation(text="Neu anlegen", icon="cil-plus")
     * @Access(role=Group::ROLE_ADMIN)
     */
    public function erstellenAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->speichereProdukt();
            return;
        }
        
        parent::indexAction();
    }
    
    /**
     * Produkt bearbeiten - versteckt, nur mit ID
     * 
     * @SubNavigation(
     *     text="Bearbeiten",
     *     icon="cil-pencil",
     *     hidden=true,
     *     requiredGetParams={"id"}
     * )
     * @Access(role=Group::ROLE_ADMIN)
     */
    public function bearbeitenAction(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        
        $em = $this->getDoctrineService()->getEntityManager();
        $produkt = $em->find(Produkt::class, $id);
        
        if (!$produkt) {
            $this->render404();
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->aktualisiereProdukt($produkt);
            return;
        }
        
        $this->getView()->assign('produkt', $produkt);
        parent::indexAction();
    }
    
    /**
     * Produkt löschen - nur Root
     * 
     * @SubNavigation(text="Löschen", hidden=true, requiredGetParams={"id"})
     * @Access(role=Group::ROLE_ROOT)
     */
    public function loeschenAction(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        
        $em = $this->getDoctrineService()->getEntityManager();
        $produkt = $em->find(Produkt::class, $id);
        
        if ($produkt) {
            $em->remove($produkt);
            $em->flush();
            
            $this->getFlashHandler()->addFlashMessage('success', 'Produkt gelöscht');
        }
        
        $this->redirect('shopModule', 'produkt', 'index');
    }
    
    private function speichereProdukt(): void
    {
        // Speicherlogik...
    }
    
    private function aktualisiereProdukt(Produkt $produkt): void
    {
        // Update-Logik...
    }
}
```

---

## Verfügbare Icons

Das Framework verwendet CoreUI Icons. Eine vollständige Liste finden Sie unter:
https://icons.coreui.io/icons/

Häufig verwendete Icons:
- `cil-home` - Home
- `cil-user` - Benutzer
- `cil-settings` - Einstellungen
- `cil-list` - Liste
- `cil-plus` - Hinzufügen
- `cil-pencil` - Bearbeiten
- `cil-trash` - Löschen
- `cil-check` - Häkchen
- `cil-x` - X/Schließen
- `cil-search` - Suche
- `cil-filter` - Filter
- `cil-folder` - Ordner
- `cil-cart` - Warenkorb
- `cil-bell` - Benachrichtigung
- `cil-envelope-closed` - E-Mail

---

## Weitere Informationen

- [Controller-Dokumentation](CONTROLLERS.md)
- [Services-Dokumentation](SERVICES.md)
- [Beispielprojekte](../examples/SIMPLE_WEBSITE.md)
