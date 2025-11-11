#  GlutenFree+

Nowoczesna aplikacja webowa wspierająca osoby z celiakią i nietolerancją glutenu.

##  Technologie

- **Backend:** PHP 8.2, PostgreSQL
- **Frontend:** HTML5, CSS3, JavaScript 
- **DevOps:** Docker, Docker Compose
- **Architektura:** MVC

##  Instalacja

### Wymagania
- Docker Desktop
- Git

### Kroki

1. Sklonuj repozytorium:
```bash
git clone https://github.com/twoja-nazwa/glutenfree-plus.git
cd glutenfree-plus
```

2. Skopiuj plik środowiskowy:
```bash
cp .env.example .env
```

3. Uruchom Docker:
```bash
docker-compose up -d --build
```

4. Aplikacja dostępna na:
- **Web:** http://localhost:3000
- **pgAdmin:** http://localhost:5050

##  Struktura projektu
glutenfree-plus/
├── src/
│   ├── controllers/    # Kontrolery MVC
│   ├── models/         # Modele danych
│   ├── middleware/     # Middleware (auth, roles)
│   ├── services/       # Logika biznesowa
│   └── database/       # Połączenie z bazą
├── public/
│   ├── views/          # Widoki HTML
│   ├── styles/         # CSS
│   ├── scripts/        # JavaScript
├── database/
│   ├── schema.sql      # Schemat bazy danych
│   ├── seed.sql        # Dane testowe
│   └── views.sql       # Widoki SQL
├── tests/              # Testy PHPUnit
├── docker/             # Konfiguracja Docker
└── docs/               # Dokumentacja
└── index.php       # Entry point

##  Funkcjonalności

-  Autoryzacja (login/register)
-  Skaner produktów (kod kreskowy)
-  Mapa restauracji bezglutenowych
-  Społeczność i przepisy
-  System ról (Admin/Moderator/User)

##  Development
```bash
# Zatrzymaj kontenery
docker-compose down

# Rebuild kontenerów
docker-compose up -d --build

# Logi
docker-compose logs -f

# Dostęp do kontenera PHP
docker exec -it glutenfree-php bash

# Testy
docker exec -it glutenfree-php composer test
```
