# Appointment API

Laravel alapú orvosi időpontfoglaló REST API.

## Követelmények

- PHP 8.3+
- Composer
- SQLite 3

## Telepítés

Repository klónozása:

```bash
git clone https://github.com/vencsimre-ev/appointment-api.git

cd appointment-api
```

Függőségek telepítése:

```bash
composer install

cp .env.example .env

php artisan key:generate
```

`.env` konfiguráció:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Migrációk és demo adatok futtatása:

```bash
php artisan migrate:fresh --seed
```

Alkalmazás indítása:

```bash
php artisan serve
```

Az API alapértelmezett címe:

```text
http://127.0.0.1:8000/api
```

## Projekt struktúra

Az üzleti logika elkülönítve került a Service rétegbe.

```text
app/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
└── Services/
```

### Rétegek felelőssége

* **Requests**: input validáció
* **Controllers**: HTTP réteg és válaszok
* **Services**: üzleti logika
* **Models**: adatkapcsolatok és ORM
* **BusinessRuleException**: üzleti szabály kivételek kezelése

## Megvalósított funkciók

### Doctors

* Orvos létrehozása
* Orvos listázása
* Orvos lekérdezése
* Orvos módosítása
* Orvos törlése

### Patients

* Páciens létrehozása
* Páciens listázása
* Páciens lekérdezése
* Páciens módosítása
* Páciens törlése

### Availabilities

Rendelési időablakok kezelése.

Üzleti szabályok:

* A rendelési idő csak jövőbeli lehet
* Minimum 30 perces időablak hozható létre
* Egy orvos rendelési időablakai nem fedhetik egymást

### Appointments

Foglalások kezelése és státuszváltások.

Üzleti szabályok:

* Csak jövőbeli időpontra lehet foglalni
* A foglalásnak egy rendelési időablakon belül kell lennie
* Már foglalt időpont nem foglalható újra
* Egy páciensnek nem lehet átfedő foglalása
* A foglalás kezdeti állapota: `pending`

### Slot alapú foglalás

A rendszer slot alapú foglalást használ.

A foglalás végidejét a rendszer automatikusan számolja a rendelési időhöz tartozó `slot_duration_minutes` alapján.

A foglalás:

- csak slot elején kezdődhet,
- több egymást követő slotot is lefoglalhat (`slot_count`),
- nem lóghat ki az orvos rendelési idejéből.

### Állapotátmenetek

```text
pending -> confirmed
pending -> cancelled

confirmed -> completed
confirmed -> cancelled

completed -> végállapot
cancelled -> végállapot
```

További szabály:

* Confirmed foglalás csak legalább 24 órával a kezdési időpont előtt mondható le.

## API végpontok

### Doctors

```http
GET    /api/doctors
POST   /api/doctors
GET    /api/doctors/{doctor}
PUT    /api/doctors/{doctor}
PATCH  /api/doctors/{doctor}
DELETE /api/doctors/{doctor}
```

### Patients

```http
GET    /api/patients
POST   /api/patients
GET    /api/patients/{patient}
PUT    /api/patients/{patient}
PATCH  /api/patients/{patient}
DELETE /api/patients/{patient}
```

### Availabilities

```http
POST   /api/availabilities
GET    /api/doctors/{doctor}/availabilities
```

### Appointments

```http
POST   /api/appointments

PATCH  /api/appointments/{appointment}/confirm
PATCH  /api/appointments/{appointment}/complete
PATCH  /api/appointments/{appointment}/cancel

GET    /api/patients/{patient}/appointments
```

Páciens foglalásainak szűrése státusz szerint:

```http
GET /api/patients/{patient}/appointments?status=confirmed
```

Lehetséges státuszok:

```text
pending
confirmed
completed
cancelled
```

## Példa requestek

### Orvos létrehozása

POST /api/doctors

```json
{
    "name": "Dr. House",
    "email": "house@example.com",
    "specialization": "Diagnosztika"
}
```

### Páciens létrehozása

POST /api/patients

```json
{
    "name": "Teszt Páciens",
    "email": "patient@example.com",
    "phone": "+36301234567"
}
```

### Rendelési idő létrehozása

POST /api/availabilities

```json
{
    "doctor_id": 1,
    "starts_at": "2026-06-20 09:00:00",
    "ends_at": "2026-06-20 12:00:00",
    "slot_duration_minutes": 30
}
```

### Időpont foglalása

POST /api/appointments

```json
{
    "patient_id": 1,
    "doctor_id": 1,
    "start_time": "2026-06-20 10:00:00"
}
```

Több slot foglalása:

```json
{
    "patient_id": 1,
    "doctor_id": 1,
    "start_time": "2026-06-20 10:00:00",
    "slot_count": 2
}
```

### Foglalás megerősítése

PATCH /api/appointments/{appointment}/confirm

Nincs request body.

### Foglalás lezárása

PATCH /api/appointments/{appointment}/complete

Nincs request body.

### Foglalás lemondása

PATCH /api/appointments/{appointment}/cancel

```json
{
    "reason": "Patient requested cancellation."
}
```

## Példa válaszok

### Sikeres válasz

```json
{
    "status": "success",
    "message": "Doctor created successfully.",
    "data": {
        "id": 1,
        "name": "Dr. Teszt Elek",
        "email": "teszt.elek@example.com",
        "specialization": "Háziorvos"
    }
}
```

### Validációs hiba

```json
{
    "status": "error",
    "message": "Validation failed.",
    "errors": {
        "email": [
            "The email field must be a valid email address."
        ]
    }
}
```

### Nem található erőforrás

```json
{
    "status": "error",
    "message": "Resource not found."
}
```

### Üzleti szabály sérülése

```json
{
    "status": "error",
    "message": "Availability overlaps an existing availability."
}
```

## Postman

A projekt tartalmaz Postman collectiont a végpontok teszteléséhez.

```text
postman/appointment-api.postman_collection.json
```

## Tesztek

A projekt Feature teszteket tartalmaz a kritikus üzleti szabályok ellenőrzésére.

Teszt futtatása:

```bash
php artisan test --filter=AppointmentBusinessRulesTest
```

A tesztek az alábbi üzleti szabályokat ellenőrzik:

- múltbeli rendelési idő nem hozható létre
- átfedő rendelési idők nem engedélyezettek
- múltbeli időpontra nem foglalható vizit
- foglalt slot nem foglalható újra
- confirmed foglalás 24 órán belül nem mondható le

## Megjegyzés

Az alkalmazás publikus API-ként működik, autentikáció nélkül, a feladatkiírásnak megfelelően.
