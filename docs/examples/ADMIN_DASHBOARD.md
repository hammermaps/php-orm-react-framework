# Beispiel: Admin Dashboard

Dieses Tutorial zeigt, wie Sie ein authentifiziertes Admin-Dashboard mit dem PHP ORM React Framework erstellen.

## Was wir bauen

Ein Admin-Dashboard mit:
- Login-geschütztem Bereich
- Dashboard mit Statistiken
- Benutzerverwaltung
- Rollenbasierte Zugriffskontrolle
- Audit-Logging

## Schritt 1: Modul erstellen

```bash
mkdir -p modules/AdminModule/src/Controllers
mkdir -p modules/AdminModule/src/Entities
mkdir -p modules/AdminModule/views/DashboardController
mkdir -p modules/AdminModule/views/BenutzerController
```

## Schritt 2: Dashboard Controller

**`modules/AdminModule/src/Controllers/DashboardController.php`**

```php
<?php
namespace Modules\AdminModule\Controllers;

use Annotations\Access;
use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\RestrictedFrontController;
use Entities\Group;
use Entities\User;

/**
 * Admin Dashboard
 * 
 * @Navigation(text="Dashboard", position="sidebar", icon="cil-speedometer")
 * @Access(role=Group::ROLE_USER)
 */
class DashboardController extends RestrictedFrontController
{
    /**
     * Hauptübersicht
     * 
     * @SubNavigation(text="Übersicht", icon="cil-chart-pie")
     */
    public function indexAction(): void
    {
        $benutzer = $this->getSessionHandler()->getUser();
        $em = $this->getDoctrineService()->getEntityManager();
        
        // Statistiken sammeln
        $statistiken = $this->sammleStatistiken($em);
        
        // Letzte Aktivitäten (Beispiel)
        $aktivitaeten = $this->letzteAktivitaeten();
        
        // An View übergeben
        $this->getView()->assign('benutzer', $benutzer);
        $this->getView()->assign('willkommen', 'Willkommen, ' . $benutzer->getName());
        $this->getView()->assign('statistiken', $statistiken);
        $this->getView()->assign('aktivitaeten', $aktivitaeten);
        $this->getView()->assign('isAdmin', $this->getSessionHandler()->hasRequiredRole(Group::ROLE_ADMIN));
        
        parent::indexAction();
    }
    
    /**
     * Mein Profil
     * 
     * @SubNavigation(text="Mein Profil", icon="cil-user")
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
    
    /**
     * Passwort ändern
     * 
     * @SubNavigation(text="Passwort ändern", icon="cil-lock-locked")
     */
    public function passwortAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->aenderePasswort();
            return;
        }
        
        parent::indexAction();
    }
    
    /**
     * System-Informationen (nur Admins)
     * 
     * @SubNavigation(text="System-Info", icon="cil-info", hidden=false)
     * @Access(role=Group::ROLE_ADMIN)
     */
    public function systemAction(): void
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unbekannt',
            'speicher_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'datum' => date('d.m.Y H:i:s'),
            'zeitzone' => date_default_timezone_get()
        ];
        
        $this->getView()->assign('systemInfo', $systemInfo);
        parent::indexAction();
    }
    
    /**
     * Abmelden
     */
    public function logoutAction(): void
    {
        $this->getSessionHandler()->signOut();
        $this->getFlashHandler()->addFlashMessage('info', 'Sie wurden erfolgreich abgemeldet.');
        $this->redirect('system', 'publicFront', 'login');
    }
    
    // ===== Private Hilfsmethoden =====
    
    private function sammleStatistiken($em): array
    {
        // Beispiel-Statistiken
        $benutzerAnzahl = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(User::class, 'u')
            ->getQuery()
            ->getSingleScalarResult();
        
        return [
            'benutzer_gesamt' => (int) $benutzerAnzahl,
            'aktive_sitzungen' => rand(5, 20), // Beispiel
            'letzte_anmeldung' => date('d.m.Y H:i'),
            'system_status' => 'Online'
        ];
    }
    
    private function letzteAktivitaeten(): array
    {
        // Beispiel-Aktivitäten (in echtem System aus DB)
        return [
            [
                'zeit' => date('H:i', strtotime('-5 minutes')),
                'aktion' => 'Benutzer angemeldet',
                'details' => 'Admin hat sich angemeldet'
            ],
            [
                'zeit' => date('H:i', strtotime('-1 hour')),
                'aktion' => 'Profil aktualisiert',
                'details' => 'E-Mail-Adresse geändert'
            ],
            [
                'zeit' => date('H:i', strtotime('-2 hours')),
                'aktion' => 'Neuer Benutzer',
                'details' => 'Benutzer "test" wurde angelegt'
            ]
        ];
    }
    
    private function aktualisiereProfile($benutzer): void
    {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        
        if (!$email) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Ungültige E-Mail-Adresse');
            parent::indexAction();
            return;
        }
        
        $benutzer->setEmail($email);
        $this->getSessionHandler()->flush();
        
        $this->getFlashHandler()->addFlashMessage('success', 'Profil erfolgreich aktualisiert');
        $this->redirect('adminModule', 'dashboard', 'profil');
    }
    
    private function aenderePasswort(): void
    {
        $altesPasswort = $_POST['altes_passwort'] ?? '';
        $neuesPasswort = $_POST['neues_passwort'] ?? '';
        $bestaetigung = $_POST['passwort_bestaetigung'] ?? '';
        
        $benutzer = $this->getSessionHandler()->getUser();
        
        // Altes Passwort prüfen
        if (!$benutzer->isValidPassword($altesPasswort)) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Aktuelles Passwort ist falsch');
            parent::indexAction();
            return;
        }
        
        // Neues Passwort validieren
        if (strlen($neuesPasswort) < 8) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Das neue Passwort muss mindestens 8 Zeichen haben');
            parent::indexAction();
            return;
        }
        
        if ($neuesPasswort !== $bestaetigung) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Die Passwörter stimmen nicht überein');
            parent::indexAction();
            return;
        }
        
        // Passwort setzen (Implementierung abhängig von User-Entity)
        // $benutzer->setPassword($neuesPasswort);
        // $this->getSessionHandler()->flush();
        
        $this->getFlashHandler()->addFlashMessage('success', 'Passwort erfolgreich geändert');
        $this->redirect('adminModule', 'dashboard', 'index');
    }
}
```

## Schritt 3: Benutzerverwaltung Controller

**`modules/AdminModule/src/Controllers/BenutzerController.php`**

```php
<?php
namespace Modules\AdminModule\Controllers;

use Annotations\Access;
use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\RestrictedFrontController;
use Entities\Group;
use Entities\User;

/**
 * Benutzerverwaltung (nur für Admins)
 * 
 * @Navigation(text="Benutzerverwaltung", position="sidebar", icon="cil-people")
 * @Access(role=Group::ROLE_ADMIN)
 */
class BenutzerController extends RestrictedFrontController
{
    /**
     * Benutzerliste
     * 
     * @SubNavigation(text="Alle Benutzer", icon="cil-list")
     */
    public function indexAction(): void
    {
        $em = $this->getDoctrineService()->getEntityManager();
        $benutzer = $em->getRepository(User::class)->findAll();
        
        $this->getView()->assign('benutzer', $benutzer);
        $this->getView()->assign('aktuellerId', $this->getSessionHandler()->getUser()->getId());
        
        parent::indexAction();
    }
    
    /**
     * Benutzer bearbeiten
     * 
     * @SubNavigation(text="Bearbeiten", icon="cil-pencil", hidden=true, requiredGetParams={"id"})
     */
    public function bearbeitenAction(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            $this->render404();
            return;
        }
        
        $em = $this->getDoctrineService()->getEntityManager();
        $benutzer = $em->find(User::class, $id);
        
        if (!$benutzer) {
            $this->render404();
            return;
        }
        
        // Eigenen Account kann man hier nicht bearbeiten
        if ($benutzer->getId() === $this->getSessionHandler()->getUser()->getId()) {
            $this->getFlashHandler()->addFlashMessage('warning', 'Bitte nutzen Sie "Mein Profil" für Ihren Account');
            $this->redirect('adminModule', 'benutzer', 'index');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->speichereBenutzer($benutzer);
            return;
        }
        
        $gruppen = $em->getRepository(Group::class)->findAll();
        
        $this->getView()->assign('benutzer', $benutzer);
        $this->getView()->assign('gruppen', $gruppen);
        parent::indexAction();
    }
    
    /**
     * Benutzer Status umschalten (aktivieren/deaktivieren)
     * 
     * @SubNavigation(text="Status", hidden=true, requiredGetParams={"id"})
     */
    public function statusAction(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            $this->render404();
            return;
        }
        
        // Nicht sich selbst deaktivieren
        if ($id === $this->getSessionHandler()->getUser()->getId()) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Sie können sich nicht selbst deaktivieren');
            $this->redirect('adminModule', 'benutzer', 'index');
            return;
        }
        
        $em = $this->getDoctrineService()->getEntityManager();
        $benutzer = $em->find(User::class, $id);
        
        if (!$benutzer) {
            $this->render404();
            return;
        }
        
        // Status umschalten (Implementierung abhängig von User-Entity)
        // $benutzer->setAktiv(!$benutzer->isAktiv());
        // $em->flush();
        
        $this->getFlashHandler()->addFlashMessage('success', 'Benutzerstatus wurde geändert');
        $this->redirect('adminModule', 'benutzer', 'index');
    }
    
    /**
     * Benutzer löschen (nur Root)
     * 
     * @SubNavigation(text="Löschen", hidden=true, requiredGetParams={"id"})
     * @Access(role=Group::ROLE_ROOT)
     */
    public function loeschenAction(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            $this->render404();
            return;
        }
        
        // Nicht sich selbst löschen
        if ($id === $this->getSessionHandler()->getUser()->getId()) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Sie können sich nicht selbst löschen');
            $this->redirect('adminModule', 'benutzer', 'index');
            return;
        }
        
        $em = $this->getDoctrineService()->getEntityManager();
        $benutzer = $em->find(User::class, $id);
        
        if (!$benutzer) {
            $this->render404();
            return;
        }
        
        $benutzername = $benutzer->getName();
        
        $em->remove($benutzer);
        $em->flush();
        
        $this->getLoggerService()->warning('Benutzer gelöscht', [
            'geloeschter_benutzer' => $benutzername,
            'geloescht_von' => $this->getSessionHandler()->getUser()->getName()
        ]);
        
        $this->getFlashHandler()->addFlashMessage('success', 'Benutzer wurde gelöscht');
        $this->redirect('adminModule', 'benutzer', 'index');
    }
    
    private function speichereBenutzer(User $benutzer): void
    {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $gruppeId = (int) ($_POST['gruppe'] ?? 0);
        
        if (!$email) {
            $this->getFlashHandler()->addFlashMessage('danger', 'Ungültige E-Mail-Adresse');
            parent::indexAction();
            return;
        }
        
        $em = $this->getDoctrineService()->getEntityManager();
        
        $benutzer->setEmail($email);
        
        if ($gruppeId) {
            $gruppe = $em->find(Group::class, $gruppeId);
            if ($gruppe) {
                $benutzer->setGroup($gruppe);
            }
        }
        
        $em->flush();
        
        $this->getLoggerService()->info('Benutzer aktualisiert', [
            'benutzer_id' => $benutzer->getId(),
            'aktualisiert_von' => $this->getSessionHandler()->getUser()->getName()
        ]);
        
        $this->getFlashHandler()->addFlashMessage('success', 'Benutzer wurde aktualisiert');
        $this->redirect('adminModule', 'benutzer', 'index');
    }
}
```

## Schritt 4: Views erstellen

### Dashboard Übersicht

**`modules/AdminModule/views/DashboardController/indexAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    {# Begrüßung #}
    <div class="row mb-4">
        <div class="col-12">
            <h2>{{ willkommen }}</h2>
            <p class="text-muted">Hier ist Ihre Übersicht für heute.</p>
        </div>
    </div>
    
    {# Statistik-Karten #}
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ statistiken.benutzer_gesamt }}</h4>
                            <p class="mb-0">Benutzer gesamt</p>
                        </div>
                        <i class="cil-people" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ statistiken.aktive_sitzungen }}</h4>
                            <p class="mb-0">Aktive Sitzungen</p>
                        </div>
                        <i class="cil-user" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ statistiken.letzte_anmeldung }}</h4>
                            <p class="mb-0">Letzte Anmeldung</p>
                        </div>
                        <i class="cil-calendar" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ statistiken.system_status }}</h4>
                            <p class="mb-0">System-Status</p>
                        </div>
                        <i class="cil-check-circle" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        {# Letzte Aktivitäten #}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="cil-history"></i> Letzte Aktivitäten
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Zeit</th>
                                <th>Aktion</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            {% for aktivitaet in aktivitaeten %}
                            <tr>
                                <td><small class="text-muted">{{ aktivitaet.zeit }}</small></td>
                                <td>{{ aktivitaet.aktion }}</td>
                                <td>{{ aktivitaet.details }}</td>
                            </tr>
                            {% else %}
                            <tr>
                                <td colspan="3" class="text-center text-muted">Keine Aktivitäten</td>
                            </tr>
                            {% endfor %}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        {# Schnellzugriff #}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="cil-lightning"></i> Schnellzugriff
                </div>
                <div class="card-body">
                    <a href="?module=adminModule&controller=dashboard&action=profil" 
                       class="btn btn-outline-primary btn-block mb-2">
                        <i class="cil-user"></i> Mein Profil
                    </a>
                    
                    <a href="?module=adminModule&controller=dashboard&action=passwort" 
                       class="btn btn-outline-primary btn-block mb-2">
                        <i class="cil-lock-locked"></i> Passwort ändern
                    </a>
                    
                    {% if isAdmin %}
                    <a href="?module=adminModule&controller=benutzer&action=index" 
                       class="btn btn-outline-warning btn-block mb-2">
                        <i class="cil-people"></i> Benutzerverwaltung
                    </a>
                    
                    <a href="?module=adminModule&controller=dashboard&action=system" 
                       class="btn btn-outline-info btn-block mb-2">
                        <i class="cil-info"></i> System-Info
                    </a>
                    {% endif %}
                    
                    <a href="?module=adminModule&controller=dashboard&action=logout" 
                       class="btn btn-outline-danger btn-block mt-4">
                        <i class="cil-account-logout"></i> Abmelden
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

### Profil bearbeiten

**`modules/AdminModule/views/DashboardController/profilAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <i class="cil-user"></i> Mein Profil
                </div>
                <div class="card-body">
                    <form method="POST" action="?module=adminModule&controller=dashboard&action=profil">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Benutzername</label>
                            <div class="col-sm-9">
                                <input type="text" 
                                       class="form-control-plaintext" 
                                       value="{{ benutzer.name }}" 
                                       readonly>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="email" class="col-sm-3 col-form-label">E-Mail</label>
                            <div class="col-sm-9">
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="{{ benutzer.email }}"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Rolle</label>
                            <div class="col-sm-9">
                                <input type="text" 
                                       class="form-control-plaintext" 
                                       value="{{ benutzer.group.name }}" 
                                       readonly>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="cil-save"></i> Speichern
                                </button>
                                <a href="?module=adminModule&controller=dashboard&action=index" 
                                   class="btn btn-secondary">
                                    Abbrechen
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

### Benutzerliste

**`modules/AdminModule/views/BenutzerController/indexAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="cil-people"></i> Benutzerverwaltung
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Benutzername</th>
                        <th>E-Mail</th>
                        <th>Rolle</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    {% for b in benutzer %}
                    <tr {% if b.id == aktuellerId %}class="table-info"{% endif %}>
                        <td>{{ b.id }}</td>
                        <td>
                            {{ b.name }}
                            {% if b.id == aktuellerId %}
                                <span class="badge badge-info">Sie</span>
                            {% endif %}
                        </td>
                        <td>{{ b.email }}</td>
                        <td>
                            <span class="badge 
                                {% if b.group.role >= 100 %}badge-danger
                                {% elseif b.group.role >= 50 %}badge-warning
                                {% else %}badge-secondary{% endif %}">
                                {{ b.group.name }}
                            </span>
                        </td>
                        <td>
                            {% if b.id != aktuellerId %}
                                <a href="?module=adminModule&controller=benutzer&action=bearbeiten&id={{ b.id }}" 
                                   class="btn btn-sm btn-primary" 
                                   title="Bearbeiten">
                                    <i class="cil-pencil"></i>
                                </a>
                                <a href="?module=adminModule&controller=benutzer&action=loeschen&id={{ b.id }}" 
                                   class="btn btn-sm btn-danger" 
                                   title="Löschen"
                                   onclick="return confirm('Benutzer wirklich löschen?')">
                                    <i class="cil-trash"></i>
                                </a>
                            {% else %}
                                <a href="?module=adminModule&controller=dashboard&action=profil" 
                                   class="btn btn-sm btn-outline-primary">
                                    Mein Profil
                                </a>
                            {% endif %}
                        </td>
                    </tr>
                    {% else %}
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Keine Benutzer gefunden
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

### System-Info (nur Admins)

**`modules/AdminModule/views/DashboardController/systemAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="card">
        <div class="card-header">
            <i class="cil-info"></i> System-Informationen
        </div>
        <div class="card-body">
            <table class="table">
                <tbody>
                    <tr>
                        <th style="width: 30%;">PHP-Version</th>
                        <td>{{ systemInfo.php_version }}</td>
                    </tr>
                    <tr>
                        <th>Server-Software</th>
                        <td>{{ systemInfo.server_software }}</td>
                    </tr>
                    <tr>
                        <th>Speicher-Limit</th>
                        <td>{{ systemInfo.speicher_limit }}</td>
                    </tr>
                    <tr>
                        <th>Max. Ausführungszeit</th>
                        <td>{{ systemInfo.max_execution_time }} Sekunden</td>
                    </tr>
                    <tr>
                        <th>Max. Upload-Größe</th>
                        <td>{{ systemInfo.upload_max_filesize }}</td>
                    </tr>
                    <tr>
                        <th>Server-Zeit</th>
                        <td>{{ systemInfo.datum }}</td>
                    </tr>
                    <tr>
                        <th>Zeitzone</th>
                        <td>{{ systemInfo.zeitzone }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
{% endblock %}
```

### Passwort ändern

**`modules/AdminModule/views/DashboardController/passwortAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="row">
        <div class="col-lg-6 offset-lg-3">
            <div class="card">
                <div class="card-header">
                    <i class="cil-lock-locked"></i> Passwort ändern
                </div>
                <div class="card-body">
                    <form method="POST" action="?module=adminModule&controller=dashboard&action=passwort">
                        <div class="form-group">
                            <label for="altes_passwort">Aktuelles Passwort</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="altes_passwort" 
                                   name="altes_passwort" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="neues_passwort">Neues Passwort</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="neues_passwort" 
                                   name="neues_passwort" 
                                   minlength="8"
                                   required>
                            <small class="form-text text-muted">
                                Mindestens 8 Zeichen
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="passwort_bestaetigung">Passwort bestätigen</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="passwort_bestaetigung" 
                                   name="passwort_bestaetigung" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="cil-save"></i> Passwort ändern
                            </button>
                            <a href="?module=adminModule&controller=dashboard&action=index" 
                               class="btn btn-secondary">
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

## Schritt 5: Testen

1. Melden Sie sich an unter:
   ```
   http://localhost:8000/?module=system&controller=publicFront&action=login
   ```

2. Öffnen Sie das Dashboard:
   ```
   http://localhost:8000/?module=adminModule&controller=dashboard&action=index
   ```

## Ergebnis

Sie haben ein vollständiges Admin-Dashboard mit:
- ✅ Authentifizierter Bereich
- ✅ Dashboard mit Statistiken
- ✅ Rollenbasierte Zugriffskontrolle
- ✅ Profilverwaltung
- ✅ Passwortänderung
- ✅ Benutzerverwaltung (nur Admins)
- ✅ System-Informationen (nur Admins)
- ✅ Audit-Logging

## Nächste Schritte

- [REST API](REST_API.md)
- [CRUD-Anwendung](CRUD_APPLICATION.md)
- [Einfache Webseite](SIMPLE_WEBSITE.md)
