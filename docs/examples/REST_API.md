# Beispiel: REST API

Dieses Tutorial zeigt, wie Sie eine REST-API mit dem PHP ORM React Framework erstellen.

## Was wir bauen

Eine vollständige REST-API für ein Produktverwaltungssystem mit:
- CRUD-Operationen (Create, Read, Update, Delete)
- JSON-Responses
- Authentifizierung
- Fehlerbehandlung
- Pagination

## Schritt 1: Entity erstellen

**`modules/ApiModule/src/Entities/Produkt.php`**

```php
<?php
namespace Modules\ApiModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="Modules\ApiModule\Repositories\ProduktRepository")
 * @ORM\Table(name="api_produkte", indexes={
 *     @ORM\Index(name="idx_aktiv", columns={"aktiv"}),
 *     @ORM\Index(name="idx_kategorie", columns={"kategorie"})
 * })
 */
class Produkt
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private string $artikelnummer;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $beschreibung = null;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private float $preis;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $bestand = 0;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $kategorie = null;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $aktiv = true;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private DateTime $erstelltAm;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private DateTime $aktualisiertAm;

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

    public function getArtikelnummer(): string
    {
        return $this->artikelnummer;
    }

    public function setArtikelnummer(string $artikelnummer): self
    {
        $this->artikelnummer = $artikelnummer;
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

    public function getBestand(): int
    {
        return $this->bestand;
    }

    public function setBestand(int $bestand): self
    {
        $this->bestand = $bestand;
        return $this;
    }

    public function getKategorie(): ?string
    {
        return $this->kategorie;
    }

    public function setKategorie(?string $kategorie): self
    {
        $this->kategorie = $kategorie;
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

    public function getErstelltAm(): DateTime
    {
        return $this->erstelltAm;
    }

    public function getAktualisiertAm(): DateTime
    {
        return $this->aktualisiertAm;
    }

    /**
     * Konvertiert die Entity in ein Array für JSON
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'artikelnummer' => $this->artikelnummer,
            'beschreibung' => $this->beschreibung,
            'preis' => (float) $this->preis,
            'bestand' => $this->bestand,
            'kategorie' => $this->kategorie,
            'aktiv' => $this->aktiv,
            'erstellt_am' => $this->erstelltAm->format('Y-m-d H:i:s'),
            'aktualisiert_am' => $this->aktualisiertAm->format('Y-m-d H:i:s')
        ];
    }
}
```

## Schritt 2: Repository erstellen

**`modules/ApiModule/src/Repositories/ProduktRepository.php`**

```php
<?php
namespace Modules\ApiModule\Repositories;

use Doctrine\ORM\EntityRepository;
use Modules\ApiModule\Entities\Produkt;

class ProduktRepository extends EntityRepository
{
    /**
     * Produkte mit Pagination abrufen
     */
    public function findPaginiert(
        int $seite = 1,
        int $proSeite = 10,
        ?string $kategorie = null,
        ?bool $nurAktive = true
    ): array {
        $qb = $this->createQueryBuilder('p');
        
        if ($nurAktive) {
            $qb->where('p.aktiv = :aktiv')
               ->setParameter('aktiv', true);
        }
        
        if ($kategorie) {
            $qb->andWhere('p.kategorie = :kategorie')
               ->setParameter('kategorie', $kategorie);
        }
        
        $qb->orderBy('p.name', 'ASC')
           ->setFirstResult(($seite - 1) * $proSeite)
           ->setMaxResults($proSeite);
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Gesamtanzahl für Pagination
     */
    public function zaehle(?string $kategorie = null, ?bool $nurAktive = true): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)');
        
        if ($nurAktive) {
            $qb->where('p.aktiv = :aktiv')
               ->setParameter('aktiv', true);
        }
        
        if ($kategorie) {
            $qb->andWhere('p.kategorie = :kategorie')
               ->setParameter('kategorie', $kategorie);
        }
        
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    
    /**
     * Produkt nach Artikelnummer finden
     */
    public function findByArtikelnummer(string $artikelnummer): ?Produkt
    {
        return $this->findOneBy(['artikelnummer' => $artikelnummer]);
    }
    
    /**
     * Produkte durchsuchen
     */
    public function suche(string $suchbegriff, int $limit = 20): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :such')
            ->orWhere('p.artikelnummer LIKE :such')
            ->orWhere('p.beschreibung LIKE :such')
            ->andWhere('p.aktiv = true')
            ->setParameter('such', '%' . $suchbegriff . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Alle Kategorien abrufen
     */
    public function findKategorien(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('DISTINCT p.kategorie')
            ->where('p.kategorie IS NOT NULL')
            ->andWhere('p.aktiv = true')
            ->orderBy('p.kategorie', 'ASC')
            ->getQuery()
            ->getResult();
        
        return array_column($result, 'kategorie');
    }
}
```

## Schritt 3: API Controller erstellen

**`modules/ApiModule/src/Controllers/ProduktApiController.php`**

```php
<?php
namespace Modules\ApiModule\Controllers;

use Controllers\ApiController;
use Modules\ApiModule\Entities\Produkt;
use Modules\ApiModule\Repositories\ProduktRepository;

/**
 * REST API für Produktverwaltung
 * 
 * Endpoints:
 * - GET    /api/produkte          - Alle Produkte (paginiert)
 * - GET    /api/produkte/zeigen   - Einzelnes Produkt
 * - GET    /api/produkte/suche    - Produkte suchen
 * - POST   /api/produkte/erstellen - Neues Produkt
 * - PUT    /api/produkte/aktualisieren - Produkt aktualisieren
 * - DELETE /api/produkte/loeschen - Produkt löschen
 */
class ProduktApiController extends ApiController
{
    /**
     * GET /api/produkte
     * 
     * Query-Parameter:
     * - seite (int): Seitennummer (Standard: 1)
     * - pro_seite (int): Einträge pro Seite (Standard: 10, Max: 100)
     * - kategorie (string): Nach Kategorie filtern
     */
    public function indexAction(): void
    {
        $seite = max(1, (int) ($_GET['seite'] ?? 1));
        $proSeite = min(100, max(1, (int) ($_GET['pro_seite'] ?? 10)));
        $kategorie = $_GET['kategorie'] ?? null;
        
        try {
            $em = $this->getDoctrineService()->getEntityManager();
            /** @var ProduktRepository $repo */
            $repo = $em->getRepository(Produkt::class);
            
            $produkte = $repo->findPaginiert($seite, $proSeite, $kategorie);
            $gesamt = $repo->zaehle($kategorie);
            $seiten = ceil($gesamt / $proSeite);
            
            $daten = array_map(fn(Produkt $p) => $p->toArray(), $produkte);
            
            $this->jsonAntwort([
                'erfolg' => true,
                'daten' => $daten,
                'meta' => [
                    'seite' => $seite,
                    'pro_seite' => $proSeite,
                    'gesamt' => $gesamt,
                    'seiten' => $seiten
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->fehlerAntwort('Fehler beim Abrufen der Produkte', 500, $e);
        }
    }
    
    /**
     * GET /api/produkte/zeigen?id=1
     * oder
     * GET /api/produkte/zeigen?artikelnummer=ABC123
     */
    public function zeigenAction(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $artikelnummer = $_GET['artikelnummer'] ?? null;
        
        if (!$id && !$artikelnummer) {
            $this->jsonAntwort([
                'erfolg' => false,
                'fehler' => 'ID oder Artikelnummer erforderlich'
            ], 400);
            return;
        }
        
        try {
            $em = $this->getDoctrineService()->getEntityManager();
            /** @var ProduktRepository $repo */
            $repo = $em->getRepository(Produkt::class);
            
            if ($id) {
                $produkt = $em->find(Produkt::class, $id);
            } else {
                $produkt = $repo->findByArtikelnummer($artikelnummer);
            }
            
            if (!$produkt) {
                $this->jsonAntwort([
                    'erfolg' => false,
                    'fehler' => 'Produkt nicht gefunden'
                ], 404);
                return;
            }
            
            $this->jsonAntwort([
                'erfolg' => true,
                'daten' => $produkt->toArray()
            ]);
            
        } catch (\Exception $e) {
            $this->fehlerAntwort('Fehler beim Abrufen des Produkts', 500, $e);
        }
    }
    
    /**
     * GET /api/produkte/suche?q=suchbegriff
     */
    public function sucheAction(): void
    {
        $suchbegriff = trim($_GET['q'] ?? '');
        $limit = min(50, max(1, (int) ($_GET['limit'] ?? 20)));
        
        if (strlen($suchbegriff) < 2) {
            $this->jsonAntwort([
                'erfolg' => false,
                'fehler' => 'Suchbegriff muss mindestens 2 Zeichen lang sein'
            ], 400);
            return;
        }
        
        try {
            $em = $this->getDoctrineService()->getEntityManager();
            /** @var ProduktRepository $repo */
            $repo = $em->getRepository(Produkt::class);
            
            $produkte = $repo->suche($suchbegriff, $limit);
            $daten = array_map(fn(Produkt $p) => $p->toArray(), $produkte);
            
            $this->jsonAntwort([
                'erfolg' => true,
                'daten' => $daten,
                'meta' => [
                    'suchbegriff' => $suchbegriff,
                    'anzahl' => count($daten)
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->fehlerAntwort('Fehler bei der Suche', 500, $e);
        }
    }
    
    /**
     * GET /api/produkte/kategorien
     */
    public function kategorienAction(): void
    {
        try {
            $em = $this->getDoctrineService()->getEntityManager();
            /** @var ProduktRepository $repo */
            $repo = $em->getRepository(Produkt::class);
            
            $kategorien = $repo->findKategorien();
            
            $this->jsonAntwort([
                'erfolg' => true,
                'daten' => $kategorien
            ]);
            
        } catch (\Exception $e) {
            $this->fehlerAntwort('Fehler beim Abrufen der Kategorien', 500, $e);
        }
    }
    
    /**
     * POST /api/produkte/erstellen
     * 
     * Body (JSON):
     * {
     *     "name": "Produktname",
     *     "artikelnummer": "ABC123",
     *     "beschreibung": "Optional",
     *     "preis": 29.99,
     *     "bestand": 100,
     *     "kategorie": "Elektronik"
     * }
     */
    public function erstellenAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonAntwort([
                'erfolg' => false,
                'fehler' => 'Nur POST-Requests erlaubt'
            ], 405);
            return;
        }
        
        // JSON-Body parsen
        $eingabe = $this->getJsonInput();
        
        // Validierung
        $fehler = $this->validiereProduktDaten($eingabe, true);
        if (!empty($fehler)) {
            $this->jsonAntwort([
                'erfolg' => false,
                'fehler' => 'Validierungsfehler',
                'details' => $fehler
            ], 400);
            return;
        }
        
        try {
            $em = $this->getDoctrineService()->getEntityManager();
            
            // Prüfen ob Artikelnummer bereits existiert
            $vorhandenes = $em->getRepository(Produkt::class)
                ->findOneBy(['artikelnummer' => $eingabe['artikelnummer']]);
            
            if ($vorhandenes) {
                $this->jsonAntwort([
                    'erfolg' => false,
                    'fehler' => 'Artikelnummer bereits vergeben'
                ], 409);
                return;
            }
            
            // Produkt erstellen
            $produkt = new Produkt();
            $produkt->setName($eingabe['name']);
            $produkt->setArtikelnummer($eingabe['artikelnummer']);
            $produkt->setBeschreibung($eingabe['beschreibung'] ?? null);
            $produkt->setPreis((float) $eingabe['preis']);
            $produkt->setBestand((int) ($eingabe['bestand'] ?? 0));
            $produkt->setKategorie($eingabe['kategorie'] ?? null);
            $produkt->setAktiv($eingabe['aktiv'] ?? true);
            
            $em->persist($produkt);
            $em->flush();
            
            // Logging
            $this->getLoggerService()->info('Produkt erstellt via API', [
                'produkt_id' => $produkt->getId(),
                'artikelnummer' => $produkt->getArtikelnummer()
            ]);
            
            $this->jsonAntwort([
                'erfolg' => true,
                'nachricht' => 'Produkt erfolgreich erstellt',
                'daten' => $produkt->toArray()
            ], 201);
            
        } catch (\Exception $e) {
            $this->fehlerAntwort('Fehler beim Erstellen des Produkts', 500, $e);
        }
    }
    
    /**
     * PUT /api/produkte/aktualisieren?id=1
     * 
     * Body (JSON): Felder zum Aktualisieren
     */
    public function aktualisierenAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonAntwort([
                'erfolg' => false,
                'fehler' => 'Nur PUT/POST-Requests erlaubt'
            ], 405);
            return;
        }
        
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            $this->jsonAntwort([
                'erfolg' => false,
                'fehler' => 'ID erforderlich'
            ], 400);
            return;
        }
        
        $eingabe = $this->getJsonInput();
        
        // Validierung (optional, da Update)
        $fehler = $this->validiereProduktDaten($eingabe, false);
        if (!empty($fehler)) {
            $this->jsonAntwort([
                'erfolg' => false,
                'fehler' => 'Validierungsfehler',
                'details' => $fehler
            ], 400);
            return;
        }
        
        try {
            $em = $this->getDoctrineService()->getEntityManager();
            $produkt = $em->find(Produkt::class, $id);
            
            if (!$produkt) {
                $this->jsonAntwort([
                    'erfolg' => false,
                    'fehler' => 'Produkt nicht gefunden'
                ], 404);
                return;
            }
            
            // Felder aktualisieren (nur wenn vorhanden)
            if (isset($eingabe['name'])) {
                $produkt->setName($eingabe['name']);
            }
            if (isset($eingabe['beschreibung'])) {
                $produkt->setBeschreibung($eingabe['beschreibung']);
            }
            if (isset($eingabe['preis'])) {
                $produkt->setPreis((float) $eingabe['preis']);
            }
            if (isset($eingabe['bestand'])) {
                $produkt->setBestand((int) $eingabe['bestand']);
            }
            if (isset($eingabe['kategorie'])) {
                $produkt->setKategorie($eingabe['kategorie']);
            }
            if (isset($eingabe['aktiv'])) {
                $produkt->setAktiv((bool) $eingabe['aktiv']);
            }
            
            $em->flush();
            
            $this->getLoggerService()->info('Produkt aktualisiert via API', [
                'produkt_id' => $produkt->getId()
            ]);
            
            $this->jsonAntwort([
                'erfolg' => true,
                'nachricht' => 'Produkt erfolgreich aktualisiert',
                'daten' => $produkt->toArray()
            ]);
            
        } catch (\Exception $e) {
            $this->fehlerAntwort('Fehler beim Aktualisieren des Produkts', 500, $e);
        }
    }
    
    /**
     * DELETE /api/produkte/loeschen?id=1
     */
    public function loeschenAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonAntwort([
                'erfolg' => false,
                'fehler' => 'Nur DELETE/POST-Requests erlaubt'
            ], 405);
            return;
        }
        
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            $this->jsonAntwort([
                'erfolg' => false,
                'fehler' => 'ID erforderlich'
            ], 400);
            return;
        }
        
        try {
            $em = $this->getDoctrineService()->getEntityManager();
            $produkt = $em->find(Produkt::class, $id);
            
            if (!$produkt) {
                $this->jsonAntwort([
                    'erfolg' => false,
                    'fehler' => 'Produkt nicht gefunden'
                ], 404);
                return;
            }
            
            $artikelnummer = $produkt->getArtikelnummer();
            
            $em->remove($produkt);
            $em->flush();
            
            $this->getLoggerService()->info('Produkt gelöscht via API', [
                'produkt_id' => $id,
                'artikelnummer' => $artikelnummer
            ]);
            
            $this->jsonAntwort([
                'erfolg' => true,
                'nachricht' => 'Produkt erfolgreich gelöscht'
            ]);
            
        } catch (\Exception $e) {
            $this->fehlerAntwort('Fehler beim Löschen des Produkts', 500, $e);
        }
    }
    
    // ===== Hilfsmethoden =====
    
    /**
     * JSON-Input aus Request-Body parsen
     */
    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Produktdaten validieren
     */
    private function validiereProduktDaten(array $daten, bool $pflichtfelder): array
    {
        $fehler = [];
        
        if ($pflichtfelder) {
            if (empty($daten['name'])) {
                $fehler['name'] = 'Name ist erforderlich';
            }
            if (empty($daten['artikelnummer'])) {
                $fehler['artikelnummer'] = 'Artikelnummer ist erforderlich';
            }
            if (!isset($daten['preis']) || !is_numeric($daten['preis'])) {
                $fehler['preis'] = 'Preis ist erforderlich und muss eine Zahl sein';
            }
        }
        
        // Optionale Validierung
        if (isset($daten['preis']) && $daten['preis'] < 0) {
            $fehler['preis'] = 'Preis darf nicht negativ sein';
        }
        
        if (isset($daten['bestand']) && $daten['bestand'] < 0) {
            $fehler['bestand'] = 'Bestand darf nicht negativ sein';
        }
        
        if (isset($daten['artikelnummer']) && strlen($daten['artikelnummer']) > 100) {
            $fehler['artikelnummer'] = 'Artikelnummer darf maximal 100 Zeichen haben';
        }
        
        return $fehler;
    }
    
    /**
     * JSON-Antwort senden
     */
    private function jsonAntwort(array $daten, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        echo json_encode($daten, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Fehler-Antwort senden
     */
    private function fehlerAntwort(string $nachricht, int $statusCode, ?\Exception $e = null): void
    {
        // In Debug-Modus mehr Details
        if ($this->isDebugMode() && $e) {
            $this->getLoggerService()->error($nachricht, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        $this->jsonAntwort([
            'erfolg' => false,
            'fehler' => $nachricht
        ], $statusCode);
    }
}
```

## Schritt 4: API-Endpunkte testen

### Alle Produkte abrufen

```bash
curl -X GET "http://localhost:8000/?module=apiModule&controller=produktApi&action=index"
```

**Antwort:**
```json
{
    "erfolg": true,
    "daten": [
        {
            "id": 1,
            "name": "Laptop",
            "artikelnummer": "LAP001",
            "beschreibung": "High-End Laptop",
            "preis": 999.99,
            "bestand": 50,
            "kategorie": "Elektronik",
            "aktiv": true,
            "erstellt_am": "2024-01-15 10:30:00",
            "aktualisiert_am": "2024-01-15 10:30:00"
        }
    ],
    "meta": {
        "seite": 1,
        "pro_seite": 10,
        "gesamt": 1,
        "seiten": 1
    }
}
```

### Einzelnes Produkt abrufen

```bash
curl -X GET "http://localhost:8000/?module=apiModule&controller=produktApi&action=zeigen&id=1"
```

### Produkt erstellen

```bash
curl -X POST "http://localhost:8000/?module=apiModule&controller=produktApi&action=erstellen" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Neuer Laptop",
    "artikelnummer": "LAP002",
    "beschreibung": "Ein weiterer Laptop",
    "preis": 799.99,
    "bestand": 25,
    "kategorie": "Elektronik"
  }'
```

### Produkt aktualisieren

```bash
curl -X PUT "http://localhost:8000/?module=apiModule&controller=produktApi&action=aktualisieren&id=1" \
  -H "Content-Type: application/json" \
  -d '{
    "preis": 899.99,
    "bestand": 45
  }'
```

### Produkt löschen

```bash
curl -X DELETE "http://localhost:8000/?module=apiModule&controller=produktApi&action=loeschen&id=1"
```

### Produkte suchen

```bash
curl -X GET "http://localhost:8000/?module=apiModule&controller=produktApi&action=suche&q=laptop"
```

## Schritt 5: Authentifizierung hinzufügen (Optional)

Für geschützte API-Endpunkte erstellen Sie einen authentifizierten Controller:

**`modules/ApiModule/src/Controllers/GeschuetzterApiController.php`**

```php
<?php
namespace Modules\ApiModule\Controllers;

use Annotations\Access;
use Controllers\ApiController;
use Entities\Group;

/**
 * Geschützte API-Endpunkte (erfordern Authentifizierung)
 * 
 * @Access(role=Group::ROLE_USER)
 */
class GeschuetzterApiController extends ApiController
{
    /**
     * Benutzerinformationen abrufen
     */
    public function meAction(): void
    {
        $benutzer = $this->getSessionHandler()->getUser();
        
        $this->jsonAntwort([
            'erfolg' => true,
            'daten' => [
                'id' => $benutzer->getId(),
                'name' => $benutzer->getName(),
                'email' => $benutzer->getEmail(),
                'rolle' => $this->getSessionHandler()->getRoleName()
            ]
        ]);
    }
    
    /**
     * Nur für Admins
     * 
     * @Access(role=Group::ROLE_ADMIN)
     */
    public function adminDatenAction(): void
    {
        // Admin-spezifische Daten...
        $this->jsonAntwort([
            'erfolg' => true,
            'daten' => ['geheim' => 'Admin-Daten']
        ]);
    }
    
    private function jsonAntwort(array $daten, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($daten, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
```

## API-Dokumentation (OpenAPI/Swagger-Style)

```yaml
openapi: 3.0.0
info:
  title: Produkt API
  version: 1.0.0
  description: REST API für Produktverwaltung

servers:
  - url: http://localhost:8000/

paths:
  /?module=apiModule&controller=produktApi&action=index:
    get:
      summary: Alle Produkte abrufen
      parameters:
        - name: seite
          in: query
          type: integer
          default: 1
        - name: pro_seite
          in: query
          type: integer
          default: 10
        - name: kategorie
          in: query
          type: string
      responses:
        200:
          description: Liste der Produkte

  /?module=apiModule&controller=produktApi&action=zeigen:
    get:
      summary: Einzelnes Produkt abrufen
      parameters:
        - name: id
          in: query
          type: integer
        - name: artikelnummer
          in: query
          type: string
      responses:
        200:
          description: Produktdetails
        404:
          description: Produkt nicht gefunden

  /?module=apiModule&controller=produktApi&action=erstellen:
    post:
      summary: Neues Produkt erstellen
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - name
                - artikelnummer
                - preis
              properties:
                name:
                  type: string
                artikelnummer:
                  type: string
                beschreibung:
                  type: string
                preis:
                  type: number
                bestand:
                  type: integer
                kategorie:
                  type: string
      responses:
        201:
          description: Produkt erstellt
        400:
          description: Validierungsfehler
        409:
          description: Artikelnummer bereits vergeben
```

## Ergebnis

Sie haben eine vollständige REST-API mit:
- ✅ CRUD-Operationen
- ✅ Pagination
- ✅ Suche und Filter
- ✅ JSON-Responses
- ✅ Fehlerbehandlung
- ✅ Validierung
- ✅ Logging
- ✅ Optionale Authentifizierung

## Nächste Schritte

- [Admin Dashboard](ADMIN_DASHBOARD.md)
- [CRUD-Anwendung](CRUD_APPLICATION.md)
- [Einfache Webseite](SIMPLE_WEBSITE.md)
