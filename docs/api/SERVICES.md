# Services - API-Dokumentation

Diese Dokumentation beschreibt alle verfügbaren Services im PHP ORM React Framework.

## Inhaltsverzeichnis

- [Übersicht](#übersicht)
- [DoctrineService](#doctrineservice)
- [CacheService](#cacheservice)
- [LoggerService](#loggerservice)
- [TemplateService](#templateservice)
- [LocaleService](#localeservice)

---

## Übersicht

Services sind zentrale Komponenten, die Funktionalität bereitstellen:

| Service | Zugriff | Beschreibung |
|---------|---------|--------------|
| DoctrineService | `$this->getDoctrineService()` | Datenbank-ORM mit Doctrine |
| CacheService | `$this->getCacheService()` | Caching mit PhpFastCache |
| LoggerService | `$this->getLoggerService()` | Logging mit Monolog |
| TemplateService | `$this->getTemplateService()` | Twig-Template-Engine |
| LocaleService | `$this->getLocaleService()` | Internationalisierung |

---

## DoctrineService

Der DoctrineService stellt die Verbindung zur Datenbank her und ermöglicht ORM-Operationen.

### EntityManager abrufen

```php
// Im Controller
$em = $this->getDoctrineService()->getEntityManager();
```

### Entities finden

```php
// Nach ID finden
$benutzer = $em->find(User::class, 1);

// Repository verwenden
$repo = $em->getRepository(Produkt::class);

// Alle finden
$produkte = $repo->findAll();

// Nach Kriterien finden
$aktiveProdukte = $repo->findBy(['aktiv' => true]);
$sortiert = $repo->findBy(
    ['kategorie' => 'Elektronik'],    // Kriterien
    ['preis' => 'ASC'],               // Sortierung
    10,                                // Limit
    0                                  // Offset
);

// Eines finden
$produkt = $repo->findOneBy(['artikelnummer' => 'ABC123']);
```

### Entities erstellen und speichern

```php
$produkt = new Produkt();
$produkt->setName('Neues Produkt');
$produkt->setPreis(29.99);
$produkt->setAktiv(true);

$em->persist($produkt);
$em->flush();

// ID ist jetzt verfügbar
echo $produkt->getId();
```

### Entities aktualisieren

```php
$produkt = $em->find(Produkt::class, 1);
$produkt->setPreis(24.99);
$em->flush();
```

### Entities löschen

```php
$produkt = $em->find(Produkt::class, 1);
$em->remove($produkt);
$em->flush();
```

### QueryBuilder verwenden

```php
$qb = $em->createQueryBuilder();

$produkte = $qb->select('p')
    ->from(Produkt::class, 'p')
    ->where('p.preis > :mindestpreis')
    ->andWhere('p.aktiv = :aktiv')
    ->setParameter('mindestpreis', 10)
    ->setParameter('aktiv', true)
    ->orderBy('p.preis', 'DESC')
    ->setMaxResults(20)
    ->getQuery()
    ->getResult();
```

### Komplexe Abfragen

```php
// Mit JOIN
$bestellungen = $em->createQueryBuilder()
    ->select('b', 'k')
    ->from(Bestellung::class, 'b')
    ->leftJoin('b.kunde', 'k')
    ->where('b.datum > :datum')
    ->setParameter('datum', new \DateTime('-30 days'))
    ->getQuery()
    ->getResult();

// Aggregat-Funktionen
$durchschnitt = $em->createQueryBuilder()
    ->select('AVG(p.preis) as durchschnitt')
    ->from(Produkt::class, 'p')
    ->where('p.kategorie = :kategorie')
    ->setParameter('kategorie', 'Elektronik')
    ->getQuery()
    ->getSingleScalarResult();

// COUNT
$anzahl = $em->createQueryBuilder()
    ->select('COUNT(p.id)')
    ->from(Produkt::class, 'p')
    ->where('p.aktiv = true')
    ->getQuery()
    ->getSingleScalarResult();
```

### Transaktionen

```php
$em->beginTransaction();

try {
    $bestellung = new Bestellung();
    $bestellung->setKunde($kunde);
    $em->persist($bestellung);
    
    foreach ($warenkorb as $artikel) {
        $position = new Bestellposition();
        $position->setBestellung($bestellung);
        $position->setProdukt($artikel['produkt']);
        $position->setMenge($artikel['menge']);
        $em->persist($position);
    }
    
    $em->flush();
    $em->commit();
    
} catch (\Exception $e) {
    $em->rollback();
    throw $e;
}
```

### Custom Repository

```php
// Repository-Klasse
namespace Modules\MeinModul\Repositories;

use Doctrine\ORM\EntityRepository;

class ProduktRepository extends EntityRepository
{
    public function findAktiveNachKategorie(string $kategorie): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.kategorie = :kategorie')
            ->andWhere('p.aktiv = true')
            ->setParameter('kategorie', $kategorie)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    public function findPreiswert(float $maxPreis): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.preis <= :maxPreis')
            ->andWhere('p.aktiv = true')
            ->setParameter('maxPreis', $maxPreis)
            ->getQuery()
            ->getResult();
    }
}
```

---

## CacheService

Der CacheService nutzt PhpFastCache für effizientes Caching.

### Verfügbare Cache-Instanzen

```php
// System-Cache (für Framework-interne Daten)
$systemCache = $this->getSystemCacheService();

// Modul-Cache (für Anwendungsdaten)
$modulCache = $this->getModuleCacheService();
```

### Cache-Handler verwenden

```php
$cache = $this->getModuleCacheHandler();

// Wert setzen (mit TTL in Sekunden)
$cache->set('mein_schluessel', $wert, 3600);  // 1 Stunde

// Wert abrufen
$wert = $cache->get('mein_schluessel');

// Prüfen ob vorhanden
if ($cache->has('mein_schluessel')) {
    // Cache-Hit
}

// Löschen
$cache->delete('mein_schluessel');

// Alles löschen
$cache->clear();
```

### Cache-Strategie für teure Abfragen

```php
public function getProduktstatistik(): array
{
    $cache = $this->getModuleCacheHandler();
    $schluessel = 'produkt_statistik';
    
    // Aus Cache holen
    $statistik = $cache->get($schluessel);
    
    if ($statistik === null) {
        // Nicht im Cache - berechnen
        $em = $this->getDoctrineService()->getEntityManager();
        
        $statistik = [
            'gesamt' => $em->createQueryBuilder()
                ->select('COUNT(p.id)')
                ->from(Produkt::class, 'p')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'aktiv' => $em->createQueryBuilder()
                ->select('COUNT(p.id)')
                ->from(Produkt::class, 'p')
                ->where('p.aktiv = true')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'durchschnittspreis' => $em->createQueryBuilder()
                ->select('AVG(p.preis)')
                ->from(Produkt::class, 'p')
                ->getQuery()
                ->getSingleScalarResult(),
        ];
        
        // Im Cache speichern (5 Minuten)
        $cache->set($schluessel, $statistik, 300);
    }
    
    return $statistik;
}
```

### Cache invalidieren

```php
// Nach Änderungen Cache löschen
public function produktSpeichern(Produkt $produkt): void
{
    $em = $this->getDoctrineService()->getEntityManager();
    $em->persist($produkt);
    $em->flush();
    
    // Statistik-Cache invalidieren
    $this->getModuleCacheHandler()->delete('produkt_statistik');
}
```

---

## LoggerService

Der LoggerService nutzt Monolog für strukturiertes Logging.

### Logger abrufen

```php
$logger = $this->getLoggerService();
```

### Log-Level

```php
// Debug-Informationen
$logger->debug('Detaillierte Debug-Info', ['kontext' => $daten]);

// Allgemeine Informationen
$logger->info('Benutzer hat sich angemeldet', ['benutzer_id' => $id]);

// Warnungen
$logger->warning('Veraltete Funktion aufgerufen', ['funktion' => __METHOD__]);

// Fehler
$logger->error('Datenbankverbindung fehlgeschlagen', [
    'host' => $host,
    'exception' => $e->getMessage()
]);

// Kritische Fehler
$logger->critical('Zahlungsabwicklung fehlgeschlagen', [
    'bestellung_id' => $bestellungId,
    'betrag' => $betrag
]);

// Notfälle
$logger->emergency('System nicht erreichbar');
```

### Strukturiertes Logging

```php
try {
    $this->verarbeiteBestellung($bestellung);
    
    $logger->info('Bestellung verarbeitet', [
        'bestellung_id' => $bestellung->getId(),
        'kunde_id' => $bestellung->getKunde()->getId(),
        'betrag' => $bestellung->getGesamtbetrag()
    ]);
    
} catch (\Exception $e) {
    $logger->error('Bestellverarbeitung fehlgeschlagen', [
        'bestellung_id' => $bestellung->getId(),
        'fehler' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    throw $e;
}
```

### Performance-Logging

```php
$startzeit = microtime(true);

// Teure Operation
$ergebnis = $this->komplexeBerechnung();

$dauer = microtime(true) - $startzeit;

$logger->info('Operation abgeschlossen', [
    'operation' => 'komplexeBerechnung',
    'dauer_sekunden' => round($dauer, 4),
    'ergebnis_anzahl' => count($ergebnis)
]);
```

---

## TemplateService

Der TemplateService verwaltet Twig-Templates.

### Template-Variablen

```php
// Im Controller
$this->getView()->assign('variable', $wert);

// Mehrere auf einmal
$this->getView()->assign('produkte', $produkte);
$this->getView()->assign('kategorien', $kategorien);
$this->getView()->assign('filter', $aktuellerFilter);
```

### Template setzen

```php
// Automatisch basierend auf Action
public function listeAction(): void
{
    // Template: views/MeinController/listeAction.tpl.twig
    parent::indexAction();
}

// Manuell setzen
public function spezielleAction(): void
{
    $this->setTemplate('andereAction');
    // Template: views/MeinController/andereAction.tpl.twig
    parent::indexAction();
}
```

### Twig-Funktionen in Templates

```twig
{# Layout erweitern #}
{% extends "layout.default.body.tpl.twig" %}

{% block page_content %}
<div class="fade-in">
    {# Variablen ausgeben #}
    <h1>{{ titel }}</h1>
    
    {# Schleifen #}
    {% for produkt in produkte %}
        <div class="card mb-3">
            <div class="card-body">
                <h5>{{ produkt.name }}</h5>
                <p>{{ produkt.beschreibung | nl2br }}</p>
                <span class="preis">{{ produkt.preis | number_format(2, ',', '.') }} €</span>
            </div>
        </div>
    {% else %}
        <p>Keine Produkte gefunden.</p>
    {% endfor %}
    
    {# Bedingungen #}
    {% if benutzer %}
        <p>Hallo {{ benutzer.name }}!</p>
    {% endif %}
    
    {# Datum formatieren #}
    <p>Erstellt: {{ produkt.erstelltAm | date('d.m.Y H:i') }}</p>
    
    {# HTML escapen #}
    <p>{{ unsichererText | e }}</p>
    
    {# Raw HTML (Vorsicht!) #}
    {{ sichererHtmlContent | raw }}
</div>
{% endblock %}
```

---

## LocaleService

Der LocaleService ermöglicht Internationalisierung.

### Sprache setzen

```php
$locale = $this->getLocaleService();

// Sprache setzen
$locale->setLanguage('de_DE');

// Aktuelle Sprache abrufen
$aktuelleSpache = $locale->getLanguageCode();
```

### Übersetzungen in Twig

```twig
{# Einfache Übersetzung #}
<p>{{ __('Willkommen auf unserer Seite') }}</p>

{# Mit Platzhaltern #}
<p>{{ __('Hallo %name%, willkommen zurück!') | replace({'%name%': benutzer.name}) }}</p>

{# Pluralisierung #}
{% if anzahl == 1 %}
    <p>{{ __('1 Produkt gefunden') }}</p>
{% else %}
    <p>{{ __('%count% Produkte gefunden') | replace({'%count%': anzahl}) }}</p>
{% endif %}
```

### Übersetzungsdateien

Übersetzungen werden in `locale/` gespeichert:

```
locale/
├── de_DE/
│   └── messages.po
├── en_US/
│   └── messages.po
└── fr_FR/
    └── messages.po
```

**Beispiel `messages.po`:**

```po
msgid "Welcome to our site"
msgstr "Willkommen auf unserer Seite"

msgid "Add to cart"
msgstr "In den Warenkorb"

msgid "Product details"
msgstr "Produktdetails"
```

---

## Service-Konfiguration

### config/default-config.php

```php
<?php
return [
    'debug_mode' => true,
    
    // Datenbankverbindung
    'connection_option' => 'default',
    'connection_options' => [
        'default' => [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'dbname' => 'meine_app',
            'user' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ],
        // Weitere Verbindungen
        'extern' => [
            'driver' => 'pdo_pgsql',
            'host' => 'externe-db.example.com',
            'dbname' => 'andere_db',
            'user' => 'app_user',
            'password' => 'geheim'
        ]
    ],
    
    // Doctrine-Optionen
    'doctrine_options' => [
        'system' => [
            'entity_dir' => 'system/Entities',
            'entity_namespace' => 'Entities',
            'proxy_dir' => 'data/doctrine/proxies'
        ],
        'module' => [
            'entity_dir' => 'modules/%MODULE%/src/Entities',
            'entity_namespace' => 'Modules\\%MODULE%\\Entities'
        ]
    ],
    
    // Cache-Konfiguration
    'cache' => [
        'system' => [
            'driver' => 'Files',
            'path' => 'data/cache/system'
        ],
        'module' => [
            'driver' => 'Redis',
            'host' => 'localhost',
            'port' => 6379
        ]
    ],
    
    // Logging
    'logging' => [
        'path' => 'log/',
        'level' => 'debug'
    ]
];
```

---

## Weitere Informationen

- [Controller-Dokumentation](CONTROLLERS.md)
- [Handler-Dokumentation](HANDLERS.md)
- [Beispielprojekte](../examples/SIMPLE_WEBSITE.md)
