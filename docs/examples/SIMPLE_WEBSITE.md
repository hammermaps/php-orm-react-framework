# Beispiel: Einfache Webseite

Dieses Tutorial zeigt, wie Sie eine einfache öffentliche Webseite mit dem PHP ORM React Framework erstellen.

## Was wir bauen

Eine Unternehmenswebseite mit:
- Startseite
- Über uns Seite
- Kontaktformular
- Impressum

## Schritt 1: Modul erstellen

Erstellen Sie die Verzeichnisstruktur:

```bash
mkdir -p modules/WebsiteModule/src/Controllers
mkdir -p modules/WebsiteModule/views/SeiteController
```

## Schritt 2: Controller erstellen

**`modules/WebsiteModule/src/Controllers/SeiteController.php`**

```php
<?php
namespace Modules\WebsiteModule\Controllers;

use Annotations\Navigation;
use Annotations\SubNavigation;
use Controllers\PublicFrontController;

/**
 * Öffentliche Webseiten
 * 
 * @Navigation(text="Webseite", position="sidebar", icon="cil-globe-alt")
 */
class SeiteController extends PublicFrontController
{
    /**
     * Startseite
     * 
     * @SubNavigation(text="Startseite", icon="cil-home")
     */
    public function indexAction(): void
    {
        $this->getView()->assign('titel', 'Willkommen bei Firma GmbH');
        $this->getView()->assign('untertitel', 'Ihr Partner für digitale Lösungen');
        
        $this->getView()->assign('features', [
            [
                'icon' => 'cil-code',
                'titel' => 'Webentwicklung',
                'text' => 'Moderne Webanwendungen nach Maß'
            ],
            [
                'icon' => 'cil-mobile',
                'titel' => 'Mobile Apps',
                'text' => 'Native und hybride App-Entwicklung'
            ],
            [
                'icon' => 'cil-cloud',
                'titel' => 'Cloud Services',
                'text' => 'Sichere und skalierbare Cloud-Lösungen'
            ]
        ]);
        
        parent::indexAction();
    }
    
    /**
     * Über uns Seite
     * 
     * @SubNavigation(text="Über uns", icon="cil-people")
     */
    public function ueberAction(): void
    {
        $this->getView()->assign('titel', 'Über uns');
        
        $this->getView()->assign('team', [
            [
                'name' => 'Max Mustermann',
                'position' => 'Geschäftsführer',
                'bild' => 'team-1.jpg',
                'beschreibung' => 'Über 15 Jahre Erfahrung in der IT-Branche'
            ],
            [
                'name' => 'Anna Schmidt',
                'position' => 'Technische Leitung',
                'bild' => 'team-2.jpg',
                'beschreibung' => 'Expertin für Softwarearchitektur'
            ],
            [
                'name' => 'Peter Müller',
                'position' => 'Lead Developer',
                'bild' => 'team-3.jpg',
                'beschreibung' => 'Full-Stack Entwickler'
            ]
        ]);
        
        $this->getView()->assign('historie', [
            ['jahr' => '2010', 'ereignis' => 'Gründung als Einzelunternehmen'],
            ['jahr' => '2015', 'ereignis' => 'Umwandlung in GmbH'],
            ['jahr' => '2018', 'ereignis' => 'Erweiterung auf 10 Mitarbeiter'],
            ['jahr' => '2023', 'ereignis' => 'Eröffnung zweiter Standort']
        ]);
        
        parent::indexAction();
    }
    
    /**
     * Kontaktseite mit Formular
     * 
     * @SubNavigation(text="Kontakt", icon="cil-envelope-closed")
     */
    public function kontaktAction(): void
    {
        $this->getView()->assign('titel', 'Kontaktieren Sie uns');
        
        // Formular verarbeiten
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verarbeiteKontaktformular();
            return;
        }
        
        // Kontaktdaten
        $this->getView()->assign('adresse', [
            'firma' => 'Firma GmbH',
            'strasse' => 'Musterstraße 123',
            'ort' => '12345 Musterstadt',
            'telefon' => '+49 123 456789',
            'email' => 'info@firma.de'
        ]);
        
        $this->getView()->assign('oeffnungszeiten', [
            'Mo-Fr' => '09:00 - 18:00 Uhr',
            'Sa' => '10:00 - 14:00 Uhr',
            'So' => 'Geschlossen'
        ]);
        
        parent::indexAction();
    }
    
    /**
     * Kontaktformular verarbeiten
     */
    private function verarbeiteKontaktformular(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $betreff = trim($_POST['betreff'] ?? '');
        $nachricht = trim($_POST['nachricht'] ?? '');
        
        // Validierung
        $fehler = [];
        
        if (empty($name)) {
            $fehler[] = 'Bitte geben Sie Ihren Namen ein.';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fehler[] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
        }
        
        if (empty($nachricht)) {
            $fehler[] = 'Bitte geben Sie eine Nachricht ein.';
        }
        
        if (!empty($fehler)) {
            foreach ($fehler as $f) {
                $this->getFlashHandler()->addFlashMessage('danger', $f);
            }
            
            // Formulardaten zurückgeben
            $this->getView()->assign('formDaten', [
                'name' => $name,
                'email' => $email,
                'betreff' => $betreff,
                'nachricht' => $nachricht
            ]);
            
            parent::indexAction();
            return;
        }
        
        // E-Mail senden (Beispiel)
        $this->sendeKontaktEmail($name, $email, $betreff, $nachricht);
        
        // Erfolgsmeldung
        $this->getFlashHandler()->addFlashMessage(
            'success', 
            'Vielen Dank für Ihre Nachricht! Wir werden uns schnellstmöglich bei Ihnen melden.'
        );
        
        // Logging
        $this->getLoggerService()->info('Kontaktformular gesendet', [
            'name' => $name,
            'email' => $email,
            'betreff' => $betreff
        ]);
        
        $this->redirect('websiteModule', 'seite', 'kontakt');
    }
    
    /**
     * Kontakt-E-Mail senden (Beispiel-Implementation)
     */
    private function sendeKontaktEmail(
        string $name, 
        string $email, 
        string $betreff, 
        string $nachricht
    ): bool {
        $an = 'info@firma.de';
        $emailBetreff = "Kontaktformular: " . $betreff;
        
        $body = "Neue Kontaktanfrage:\n\n";
        $body .= "Name: {$name}\n";
        $body .= "E-Mail: {$email}\n";
        $body .= "Betreff: {$betreff}\n\n";
        $body .= "Nachricht:\n{$nachricht}";
        
        $headers = "From: {$name} <{$email}>\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        return mail($an, $emailBetreff, $body, $headers);
    }
    
    /**
     * Impressum
     * 
     * @SubNavigation(text="Impressum", icon="cil-description")
     */
    public function impressumAction(): void
    {
        $this->getView()->assign('titel', 'Impressum');
        
        $this->getView()->assign('impressum', [
            'firma' => 'Firma GmbH',
            'vertreter' => 'Max Mustermann (Geschäftsführer)',
            'adresse' => 'Musterstraße 123, 12345 Musterstadt',
            'telefon' => '+49 123 456789',
            'email' => 'info@firma.de',
            'registergericht' => 'Amtsgericht Musterstadt',
            'registernummer' => 'HRB 12345',
            'ustid' => 'DE123456789'
        ]);
        
        parent::indexAction();
    }
    
    /**
     * Datenschutzerklärung
     * 
     * @SubNavigation(text="Datenschutz", icon="cil-shield-alt")
     */
    public function datenschutzAction(): void
    {
        $this->getView()->assign('titel', 'Datenschutzerklärung');
        parent::indexAction();
    }
}
```

## Schritt 3: Views erstellen

### Startseite

**`modules/WebsiteModule/views/SeiteController/indexAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    {# Hero-Bereich #}
    <div class="jumbotron bg-primary text-white mb-4">
        <div class="container">
            <h1 class="display-4">{{ titel }}</h1>
            <p class="lead">{{ untertitel }}</p>
            <hr class="my-4">
            <p>Entdecken Sie unsere Leistungen und lassen Sie uns gemeinsam Ihre digitale Zukunft gestalten.</p>
            <a class="btn btn-light btn-lg" href="?module=websiteModule&controller=seite&action=kontakt" role="button">
                Kontakt aufnehmen
            </a>
        </div>
    </div>
    
    {# Features #}
    <div class="row">
        {% for feature in features %}
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="{{ feature.icon }}" style="font-size: 3rem; color: #321fdb;"></i>
                    <h4 class="card-title mt-3">{{ feature.titel }}</h4>
                    <p class="card-text">{{ feature.text }}</p>
                </div>
            </div>
        </div>
        {% endfor %}
    </div>
    
    {# Call-to-Action #}
    <div class="card bg-light mt-4">
        <div class="card-body text-center">
            <h3>Bereit für Ihr nächstes Projekt?</h3>
            <p class="mb-4">Kontaktieren Sie uns für ein unverbindliches Beratungsgespräch.</p>
            <a href="?module=websiteModule&controller=seite&action=kontakt" class="btn btn-primary btn-lg">
                <i class="cil-envelope-closed mr-2"></i> Jetzt anfragen
            </a>
        </div>
    </div>
</div>
{% endblock %}
```

### Über uns

**`modules/WebsiteModule/views/SeiteController/ueberAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <h1 class="mb-4">{{ titel }}</h1>
    
    {# Unternehmensvorstellung #}
    <div class="card mb-4">
        <div class="card-body">
            <h3>Unsere Geschichte</h3>
            <p>
                Seit unserer Gründung im Jahr 2010 haben wir uns zu einem führenden Anbieter 
                digitaler Lösungen entwickelt. Unser Fokus liegt auf innovativen Technologien 
                und erstklassigem Kundenservice.
            </p>
            
            <div class="timeline mt-4">
                {% for h in historie %}
                <div class="row mb-3">
                    <div class="col-md-2">
                        <span class="badge badge-primary p-2">{{ h.jahr }}</span>
                    </div>
                    <div class="col-md-10">
                        {{ h.ereignis }}
                    </div>
                </div>
                {% endfor %}
            </div>
        </div>
    </div>
    
    {# Team #}
    <h3 class="mb-3">Unser Team</h3>
    <div class="row">
        {% for mitarbeiter in team %}
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-secondary mx-auto mb-3" 
                         style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
                        <i class="cil-user" style="font-size: 3rem; color: white;"></i>
                    </div>
                    <h5 class="card-title">{{ mitarbeiter.name }}</h5>
                    <p class="text-muted">{{ mitarbeiter.position }}</p>
                    <p class="card-text small">{{ mitarbeiter.beschreibung }}</p>
                </div>
            </div>
        </div>
        {% endfor %}
    </div>
</div>
{% endblock %}
```

### Kontakt

**`modules/WebsiteModule/views/SeiteController/kontaktAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <h1 class="mb-4">{{ titel }}</h1>
    
    <div class="row">
        {# Kontaktformular #}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="cil-envelope-letter"></i> Schreiben Sie uns
                </div>
                <div class="card-body">
                    <form method="POST" action="?module=websiteModule&controller=seite&action=kontakt">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="name">Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="{{ formDaten.name ?? '' }}"
                                       required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">E-Mail *</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="{{ formDaten.email ?? '' }}"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="betreff">Betreff</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="betreff" 
                                   name="betreff"
                                   value="{{ formDaten.betreff ?? '' }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="nachricht">Nachricht *</label>
                            <textarea class="form-control" 
                                      id="nachricht" 
                                      name="nachricht" 
                                      rows="6" 
                                      required>{{ formDaten.nachricht ?? '' }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="datenschutz" required>
                                <label class="form-check-label" for="datenschutz">
                                    Ich habe die <a href="?module=websiteModule&controller=seite&action=datenschutz" target="_blank">Datenschutzerklärung</a> gelesen und akzeptiere sie. *
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="cil-send"></i> Nachricht senden
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        {# Kontaktinformationen #}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="cil-location-pin"></i> Kontaktdaten
                </div>
                <div class="card-body">
                    <address>
                        <strong>{{ adresse.firma }}</strong><br>
                        {{ adresse.strasse }}<br>
                        {{ adresse.ort }}<br><br>
                        <i class="cil-phone"></i> {{ adresse.telefon }}<br>
                        <i class="cil-envelope-closed"></i> 
                        <a href="mailto:{{ adresse.email }}">{{ adresse.email }}</a>
                    </address>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <i class="cil-clock"></i> Öffnungszeiten
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        {% for tag, zeit in oeffnungszeiten %}
                        <tr>
                            <td><strong>{{ tag }}</strong></td>
                            <td>{{ zeit }}</td>
                        </tr>
                        {% endfor %}
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

### Impressum

**`modules/WebsiteModule/views/SeiteController/impressumAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="card">
        <div class="card-header">
            <h1 class="mb-0">{{ titel }}</h1>
        </div>
        <div class="card-body">
            <h4>Angaben gemäß § 5 TMG</h4>
            
            <p>
                <strong>{{ impressum.firma }}</strong><br>
                {{ impressum.adresse }}
            </p>
            
            <p>
                <strong>Vertreten durch:</strong><br>
                {{ impressum.vertreter }}
            </p>
            
            <h5>Kontakt</h5>
            <p>
                Telefon: {{ impressum.telefon }}<br>
                E-Mail: <a href="mailto:{{ impressum.email }}">{{ impressum.email }}</a>
            </p>
            
            <h5>Registereintrag</h5>
            <p>
                Eintragung im Handelsregister.<br>
                Registergericht: {{ impressum.registergericht }}<br>
                Registernummer: {{ impressum.registernummer }}
            </p>
            
            <h5>Umsatzsteuer-ID</h5>
            <p>
                Umsatzsteuer-Identifikationsnummer gemäß § 27a Umsatzsteuergesetz:<br>
                {{ impressum.ustid }}
            </p>
            
            <h5>Haftungsausschluss</h5>
            <p>
                Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. 
                Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte 
                können wir jedoch keine Gewähr übernehmen.
            </p>
        </div>
    </div>
</div>
{% endblock %}
```

### Datenschutz

**`modules/WebsiteModule/views/SeiteController/datenschutzAction.tpl.twig`**

```twig
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    <div class="card">
        <div class="card-header">
            <h1 class="mb-0">{{ titel }}</h1>
        </div>
        <div class="card-body">
            <h4>1. Datenschutz auf einen Blick</h4>
            <p>
                Die folgenden Hinweise geben einen einfachen Überblick darüber, 
                was mit Ihren personenbezogenen Daten passiert, wenn Sie diese Website besuchen.
            </p>
            
            <h5>Datenerfassung auf dieser Website</h5>
            <p>
                <strong>Wer ist verantwortlich für die Datenerfassung auf dieser Website?</strong><br>
                Die Datenverarbeitung auf dieser Website erfolgt durch den Websitebetreiber.
            </p>
            
            <h5>Wie erfassen wir Ihre Daten?</h5>
            <p>
                Ihre Daten werden zum einen dadurch erhoben, dass Sie uns diese mitteilen. 
                Hierbei kann es sich z.B. um Daten handeln, die Sie in ein Kontaktformular eingeben.
            </p>
            
            <h4>2. Kontaktformular</h4>
            <p>
                Wenn Sie uns per Kontaktformular Anfragen zukommen lassen, werden Ihre Angaben 
                aus dem Anfrageformular inklusive der von Ihnen dort angegebenen Kontaktdaten 
                zwecks Bearbeitung der Anfrage und für den Fall von Anschlussfragen bei uns 
                gespeichert.
            </p>
            
            <h4>3. Cookies</h4>
            <p>
                Unsere Internetseiten verwenden teilweise sogenannte Cookies. Cookies richten 
                auf Ihrem Rechner keinen Schaden an und enthalten keine Viren.
            </p>
            
            {# Weitere Abschnitte nach Bedarf #}
        </div>
    </div>
</div>
{% endblock %}
```

## Schritt 4: Testen

Öffnen Sie im Browser:

```
http://localhost:8000/?module=websiteModule&controller=seite&action=index
```

## Ergebnis

Sie haben eine vollständige öffentliche Webseite mit:
- ✅ Responsive Design mit CoreUI
- ✅ Navigation automatisch aus Annotations generiert
- ✅ Kontaktformular mit Validierung
- ✅ Flash-Nachrichten für Feedback
- ✅ Logging für Analyse
- ✅ Rechtliche Seiten (Impressum, Datenschutz)

## Nächste Schritte

- [REST API erstellen](REST_API.md)
- [Admin Dashboard](ADMIN_DASHBOARD.md)
- [CRUD-Anwendung](CRUD_APPLICATION.md)
