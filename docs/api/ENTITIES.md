# Entities - API-Dokumentation

Diese Dokumentation beschreibt die Erstellung und Verwendung von Doctrine ORM Entities im PHP ORM React Framework.

## Inhaltsverzeichnis

- [Übersicht](#übersicht)
- [Basis-Entity erstellen](#basis-entity-erstellen)
- [Datentypen und Mapping](#datentypen-und-mapping)
- [Beziehungen (Relationships)](#beziehungen-relationships)
- [Gedmo Extensions](#gedmo-extensions)
- [Custom Repositories](#custom-repositories)
- [System-Entities](#system-entities)
- [Vollständiges Beispiel](#vollständiges-beispiel)

---

## Übersicht

Entities sind PHP-Klassen, die Datenbanktabellen repräsentieren. Das Framework verwendet Doctrine ORM mit Annotations für das Object-Relational-Mapping.

### Dateistruktur

```
modules/MeinModul/
└── src/
    ├── Entities/
    │   ├── Produkt.php
    │   ├── Kategorie.php
    │   └── Bestellung.php
    └── Repositories/
        ├── ProduktRepository.php
        └── BestellungRepository.php
```

---

## Basis-Entity erstellen

### Einfache Entity

```php
<?php
namespace Modules\MeinModul\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="produkte")
 */
class Produkt
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
    private string $name;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $beschreibung = null;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $preis;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $aktiv = true;

    // Getter und Setter

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getBeschreibung(): ?string
    {
        return $this->beschreibung;
    }

    public function setBeschreibung(?string $beschreibung): self
    {
        $this->beschreibung = $beschreibung;
        return $this;
    }

    public function getPreis(): float
    {
        return $this->preis;
    }

    public function setPreis(float $preis): self
    {
        $this->preis = $preis;
        return $this;
    }

    public function isAktiv(): bool
    {
        return $this->aktiv;
    }

    public function setAktiv(bool $aktiv): self
    {
        $this->aktiv = $aktiv;
        return $this;
    }
}
```

---

## Datentypen und Mapping

### Verfügbare Spaltentypen

| Doctrine-Typ | PHP-Typ | MySQL-Typ | Beschreibung |
|--------------|---------|-----------|--------------|
| `integer` | int | INT | Ganzzahl |
| `smallint` | int | SMALLINT | Kleine Ganzzahl |
| `bigint` | string | BIGINT | Große Ganzzahl |
| `decimal` | string | DECIMAL | Dezimalzahl (exakt) |
| `float` | float | DOUBLE | Fließkommazahl |
| `string` | string | VARCHAR | Kurzer Text |
| `text` | string | LONGTEXT | Langer Text |
| `boolean` | bool | TINYINT(1) | Wahrheitswert |
| `datetime` | DateTime | DATETIME | Datum und Zeit |
| `date` | DateTime | DATE | Nur Datum |
| `time` | DateTime | TIME | Nur Zeit |
| `json` | array | JSON | JSON-Daten |
| `array` | array | LONGTEXT | Serialisiertes Array |
| `blob` | resource | BLOB | Binärdaten |

### Spalten-Optionen

```php
/**
 * @ORM\Column(
 *     type="string",
 *     length=100,              // Maximale Länge
 *     nullable=false,          // NOT NULL
 *     unique=true,             // UNIQUE Index
 *     options={
 *         "default": "wert",   // Standardwert
 *         "comment": "Kommentar für die Spalte"
 *     }
 * )
 */
private string $artikelnummer;

/**
 * @ORM\Column(
 *     type="decimal",
 *     precision=10,            // Gesamtstellen
 *     scale=2                  // Nachkommastellen
 * )
 */
private float $preis;
```

### Indizes

```php
/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="produkte",
 *     indexes={
 *         @ORM\Index(name="idx_name", columns={"name"}),
 *         @ORM\Index(name="idx_kategorie", columns={"kategorie_id"}),
 *         @ORM\Index(name="idx_preis", columns={"preis"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="uq_artikelnummer", columns={"artikelnummer"})
 *     }
 * )
 */
class Produkt
```

---

## Beziehungen (Relationships)

### One-to-Many

Eine Kategorie hat viele Produkte:

```php
// Kategorie.php
namespace Modules\MeinModul\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="kategorien")
 */
class Kategorie
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
     * @ORM\Column(type="string", length=100)
     */
    private string $name;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Produkt", mappedBy="kategorie", cascade={"persist", "remove"})
     */
    private Collection $produkte;

    public function __construct()
    {
        $this->produkte = new ArrayCollection();
    }

    // Getter und Setter

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getProdukte(): Collection
    {
        return $this->produkte;
    }

    public function addProdukt(Produkt $produkt): self
    {
        if (!$this->produkte->contains($produkt)) {
            $this->produkte->add($produkt);
            $produkt->setKategorie($this);
        }
        return $this;
    }

    public function removeProdukt(Produkt $produkt): self
    {
        if ($this->produkte->contains($produkt)) {
            $this->produkte->removeElement($produkt);
        }
        return $this;
    }
}
```

### Many-to-One

Ein Produkt gehört zu einer Kategorie:

```php
// Produkt.php (ergänzt)

/**
 * @var Kategorie|null
 * @ORM\ManyToOne(targetEntity="Kategorie", inversedBy="produkte")
 * @ORM\JoinColumn(name="kategorie_id", referencedColumnName="id", onDelete="SET NULL")
 */
private ?Kategorie $kategorie = null;

public function getKategorie(): ?Kategorie
{
    return $this->kategorie;
}

public function setKategorie(?Kategorie $kategorie): self
{
    $this->kategorie = $kategorie;
    return $this;
}
```

### Many-to-Many

Produkte haben viele Tags und Tags gehören zu vielen Produkten:

```php
// Tag.php
/**
 * @ORM\Entity
 * @ORM\Table(name="tags")
 */
class Tag
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
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private string $name;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Produkt", mappedBy="tags")
     */
    private Collection $produkte;

    public function __construct()
    {
        $this->produkte = new ArrayCollection();
    }

    // Getter und Setter...
}

// Produkt.php (ergänzt)

/**
 * @var Collection
 * @ORM\ManyToMany(targetEntity="Tag", inversedBy="produkte")
 * @ORM\JoinTable(
 *     name="produkt_tags",
 *     joinColumns={@ORM\JoinColumn(name="produkt_id", referencedColumnName="id")},
 *     inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
 * )
 */
private Collection $tags;

public function __construct()
{
    $this->tags = new ArrayCollection();
}

public function getTags(): Collection
{
    return $this->tags;
}

public function addTag(Tag $tag): self
{
    if (!$this->tags->contains($tag)) {
        $this->tags->add($tag);
    }
    return $this;
}

public function removeTag(Tag $tag): self
{
    $this->tags->removeElement($tag);
    return $this;
}
```

### One-to-One

```php
// Benutzer.php
/**
 * @var Profil|null
 * @ORM\OneToOne(targetEntity="Profil", mappedBy="benutzer", cascade={"persist", "remove"})
 */
private ?Profil $profil = null;

// Profil.php
/**
 * @var Benutzer
 * @ORM\OneToOne(targetEntity="Benutzer", inversedBy="profil")
 * @ORM\JoinColumn(name="benutzer_id", referencedColumnName="id", nullable=false)
 */
private Benutzer $benutzer;
```

---

## Gedmo Extensions

Das Framework nutzt Gedmo Doctrine Extensions für erweiterte Funktionalität.

### Timestampable (Automatische Zeitstempel)

```php
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @var DateTime
 * @Gedmo\Timestampable(on="create")
 * @ORM\Column(type="datetime")
 */
private DateTime $erstelltAm;

/**
 * @var DateTime
 * @Gedmo\Timestampable(on="update")
 * @ORM\Column(type="datetime")
 */
private DateTime $aktualisiertAm;

public function getErstelltAm(): DateTime
{
    return $this->erstelltAm;
}

public function getAktualisiertAm(): DateTime
{
    return $this->aktualisiertAm;
}
```

### Sluggable (Automatische URL-Slugs)

```php
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @var string
 * @Gedmo\Slug(fields={"name"})
 * @ORM\Column(type="string", length=255, unique=true)
 */
private string $slug;

public function getSlug(): string
{
    return $this->slug;
}
```

### SoftDeleteable (Soft-Delete)

```php
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="produkte")
 * @Gedmo\SoftDeleteable(fieldName="geloeschtAm", timeAware=false)
 */
class Produkt
{
    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $geloeschtAm = null;

    public function getGeloeschtAm(): ?DateTime
    {
        return $this->geloeschtAm;
    }
}
```

---

## Custom Repositories

### Repository erstellen

```php
<?php
namespace Modules\MeinModul\Repositories;

use Doctrine\ORM\EntityRepository;
use Modules\MeinModul\Entities\Produkt;

class ProduktRepository extends EntityRepository
{
    /**
     * Alle aktiven Produkte nach Preis sortiert
     */
    public function findAktiveNachPreis(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.aktiv = :aktiv')
            ->setParameter('aktiv', true)
            ->orderBy('p.preis', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Produkte in einer Preisspanne
     */
    public function findInPreisspanne(float $min, float $max): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.preis >= :min')
            ->andWhere('p.preis <= :max')
            ->andWhere('p.aktiv = true')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->orderBy('p.preis', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Suche nach Name oder Beschreibung
     */
    public function sucheNachText(string $suchbegriff): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :such')
            ->orWhere('p.beschreibung LIKE :such')
            ->setParameter('such', '%' . $suchbegriff . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Produkte einer Kategorie mit Pagination
     */
    public function findNachKategoriePaginiert(
        int $kategorieId, 
        int $seite = 1, 
        int $proSeite = 10
    ): array {
        return $this->createQueryBuilder('p')
            ->where('p.kategorie = :katId')
            ->andWhere('p.aktiv = true')
            ->setParameter('katId', $kategorieId)
            ->setFirstResult(($seite - 1) * $proSeite)
            ->setMaxResults($proSeite)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Anzahl Produkte in Kategorie
     */
    public function zaehleInKategorie(int $kategorieId): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.kategorie = :katId')
            ->andWhere('p.aktiv = true')
            ->setParameter('katId', $kategorieId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Durchschnittspreis berechnen
     */
    public function berechneDurchschnittspreis(): float
    {
        return (float) $this->createQueryBuilder('p')
            ->select('AVG(p.preis)')
            ->where('p.aktiv = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Neu hinzugefügte Produkte (letzte 7 Tage)
     */
    public function findNeue(): array
    {
        $vorWoche = new \DateTime('-7 days');
        
        return $this->createQueryBuilder('p')
            ->where('p.erstelltAm > :datum')
            ->andWhere('p.aktiv = true')
            ->setParameter('datum', $vorWoche)
            ->orderBy('p.erstelltAm', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
```

### Repository in Entity verknüpfen

```php
/**
 * @ORM\Entity(repositoryClass="Modules\MeinModul\Repositories\ProduktRepository")
 * @ORM\Table(name="produkte")
 */
class Produkt
{
    // ...
}
```

### Repository verwenden

```php
// Im Controller
$em = $this->getDoctrineService()->getEntityManager();

/** @var ProduktRepository $repo */
$repo = $em->getRepository(Produkt::class);

// Custom-Methoden aufrufen
$aktiveProdukte = $repo->findAktiveNachPreis();
$suchergebnisse = $repo->sucheNachText('laptop');
$preisProdukte = $repo->findInPreisspanne(100, 500);
```

---

## System-Entities

Das Framework enthält vordefinierte System-Entities:

### User

```php
namespace Entities;

// Verfügbare Eigenschaften:
$user->getId();           // int
$user->getName();         // string (Benutzername)
$user->getEmail();        // string
$user->getLocale();       // string (z.B. "de_DE")
$user->getGroup();        // Group Entity
$user->getBy();           // User (übergeordneter Benutzer)
$user->getUsers();        // Collection (untergeordnete Benutzer)

// Passwort prüfen
$user->isValidPassword($passwort);
```

### Group

```php
namespace Entities;

// Rollen-Konstanten
Group::ROLE_ROOT     // 1000
Group::ROLE_ADMIN    // 100
Group::ROLE_RESELLER // 50
Group::ROLE_USER     // 10
Group::ROLE_ANY      // 0

// Eigenschaften
$group->getId();
$group->getName();
$group->getRole();   // int (Rollen-Level)
```

---

## Vollständiges Beispiel

### Bestellsystem

```php
<?php
// Kunde.php
namespace Modules\ShopModule\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="Modules\ShopModule\Repositories\KundeRepository")
 * @ORM\Table(name="kunden")
 */
class Kunde
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $vorname;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $nachname;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private ?string $telefon = null;

    /**
     * @ORM\OneToMany(targetEntity="Bestellung", mappedBy="kunde", cascade={"persist"})
     * @ORM\OrderBy({"bestelltAm" = "DESC"})
     */
    private Collection $bestellungen;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private DateTime $registriertAm;

    public function __construct()
    {
        $this->bestellungen = new ArrayCollection();
    }

    // Getter, Setter...
    
    public function getVollstaendigerName(): string
    {
        return $this->vorname . ' ' . $this->nachname;
    }
    
    public function getAnzahlBestellungen(): int
    {
        return $this->bestellungen->count();
    }
}

// Bestellung.php
/**
 * @ORM\Entity(repositoryClass="Modules\ShopModule\Repositories\BestellungRepository")
 * @ORM\Table(name="bestellungen")
 */
class Bestellung
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private string $bestellnummer;

    /**
     * @ORM\ManyToOne(targetEntity="Kunde", inversedBy="bestellungen")
     * @ORM\JoinColumn(nullable=false)
     */
    private Kunde $kunde;

    /**
     * @ORM\OneToMany(targetEntity="Bestellposition", mappedBy="bestellung", cascade={"persist", "remove"})
     */
    private Collection $positionen;

    /**
     * @ORM\Column(type="string", length=20, options={"default": "offen"})
     */
    private string $status = 'offen';

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $gesamtbetrag = 0;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private DateTime $bestelltAm;

    public function __construct()
    {
        $this->positionen = new ArrayCollection();
        $this->bestellnummer = 'B-' . strtoupper(uniqid());
    }

    public function addPosition(Bestellposition $position): self
    {
        if (!$this->positionen->contains($position)) {
            $this->positionen->add($position);
            $position->setBestellung($this);
            $this->berechneGesamtbetrag();
        }
        return $this;
    }

    public function berechneGesamtbetrag(): void
    {
        $this->gesamtbetrag = 0;
        foreach ($this->positionen as $position) {
            $this->gesamtbetrag += $position->getZwischensumme();
        }
    }

    // Getter, Setter...
}

// Bestellposition.php
/**
 * @ORM\Entity
 * @ORM\Table(name="bestellpositionen")
 */
class Bestellposition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Bestellung", inversedBy="positionen")
     * @ORM\JoinColumn(nullable=false)
     */
    private Bestellung $bestellung;

    /**
     * @ORM\ManyToOne(targetEntity="Produkt")
     * @ORM\JoinColumn(nullable=false)
     */
    private Produkt $produkt;

    /**
     * @ORM\Column(type="integer")
     */
    private int $menge;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $einzelpreis;

    public function getZwischensumme(): float
    {
        return $this->menge * $this->einzelpreis;
    }

    // Getter, Setter...
}
```

---

## Weitere Informationen

- [Controller-Dokumentation](CONTROLLERS.md)
- [Services-Dokumentation](SERVICES.md)
- [Doctrine ORM Dokumentation](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/index.html)
