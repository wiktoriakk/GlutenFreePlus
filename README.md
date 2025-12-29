# GlutenFree+ 

A comprehensive web application for the gluten-free community, providing tools for product scanning, restaurant discovery, recipe sharing, and community engagement.

---

##  Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Usage](#usage)
- [Testing](#testing)
- [Screenshots](#screenshots)
- [Security Features](#security-features)
- [User Roles](#user-roles)
- [API Endpoints](#api-endpoints)

---

##  Overview

**GlutenFree+** is a full-stack web application designed to support individuals living a gluten-free lifestyle. Whether you have celiac disease, gluten sensitivity, or simply choose to avoid gluten, this platform provides essential tools and community support.

### Key Objectives:
- **Product Safety**: Scan and verify gluten-free products
- **Community**: Connect with others on the same journey
- **Recipes**: Share and discover gluten-free recipes
- **Discovery**: Find gluten-free restaurants and shops
- **Education**: Access tips and guidance

---

##  Features

###  Authentication & Authorization
- Secure user registration and login
- Role-based access control (User, Moderator, Admin)
- Session management with secure cookies
- CSRF protection on all forms
- Rate limiting on sensitive endpoints
- Password hashing with bcrypt

###  User Management (Admin)
- View all registered users
- Manage user roles (User → Moderator → Admin)
- Block/unblock user accounts
- Delete users
- User statistics dashboard

###  Content Moderation (Moderator)
- Review community posts and comments
- Hide inappropriate content
- Delete posts/comments
- Moderation statistics
- Real-time content filtering

###  Product Scanner
- Search products by name or barcode
- View gluten-free certification status
- Ingredient list display
- Safe alternatives suggestions
- Favorite products

###  Discover Places
- Interactive map (Leaflet.js)
- Filter by type (Restaurants, Bakeries, Cafes)
- Certified gluten-free locations
- Distance calculation
- Place details and reviews

###  Recipes & Tips
- Browse gluten-free recipes with images
- Create and share recipes
- Recipe details (prep time, cook time, difficulty)
- Step-by-step instructions
- Interactive ingredient checklist
- Add recipes to favorites
- Tips section for gluten-free living
- Image upload for recipes

###  Community
- User profiles with avatars
- Filter by user type (Celiac, Nutritionist, Chef, Food Blogger)
- View member profiles
- User statistics

###  Dashboard
- Personalized welcome message
- Quick access to all features
- User profile button
- Responsive navigation

---

##  Technology Stack

### Backend
- **PHP 8.2** - Server-side logic
- **PostgreSQL 15** - Relational database
- **MVC Architecture** - Clean code organization
- **PDO** - Database abstraction layer
- **Composer** - Dependency management

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with variables
- **JavaScript (ES6+)** - Interactive functionality
- **Leaflet.js** - Interactive maps
- **Inter Font** - Clean typography

### DevOps
- **Docker** - Containerization
- **Docker Compose** - Multi-container orchestration
- **Nginx** - Web server
- **Git** - Version control

### Testing
- **PHPUnit** - Unit testing (22 tests)
- **Bash/cURL** - Integration testing (10 tests)
- **32 total tests** with 100% pass rate

---

##  Architecture

### MVC Pattern

```
GlutenFreePlus/
├── src/
│   ├── controllers/     # Request handling
│   │   ├── AppController.php
│   │   ├── SecurityController.php
│   │   ├── DashboardController.php
│   │   ├── CommunityController.php
│   │   ├── RecipeController.php
│   │   ├── AdminController.php
│   │   └── ModeratorController.php
│   ├── models/          # Business logic
│   │   ├── User.php
│   │   ├── Recipe.php
│   │   └── Post.php
│   ├── repository/      # Database access
│   │   ├── Repository.php
│   │   ├── UserRepository.php
│   │   └── RecipeRepository.php
│   ├── middleware/      # Request processing
│   │   ├── AuthMiddleware.php
│   │   ├── ErrorHandler.php
│   │   └── RateLimiter.php
│   └── database/
│       └── Database.php
├── public/
│   ├── views/           # HTML templates
│   ├── styles/          # CSS files
│   ├── scripts/         # JavaScript files
│   └── images/          # Static assets
├── tests/
│   ├── Unit/            # PHPUnit tests
│   └── Integration/     # API tests
├── docker/              # Docker configuration
├── Routing.php          # URL routing
└── index.php            # Entry point
```

### Request Flow

```
1. User Request → index.php
2. Routing.php → Parse URL
3. AuthMiddleware → Check authentication
4. Controller → Handle request
5. Repository → Database query
6. Model → Business logic
7. View → Render response
8. Response → User
```

---

##  Database Schema

### ERD (Entity Relationship Diagram)

```
┌─────────────────┐         ┌──────────────────┐
│     USERS       │         │     RECIPES      │
├─────────────────┤         ├──────────────────┤
│ id (PK)         │─────┐   │ id (PK)          │
│ email (UNIQUE)  │     └──→│ author_id (FK)   │
│ password        │         │ title            │
│ name            │         │ description      │
│ role            │         │ ingredients      │
│ user_type       │         │ instructions     │
│ avatar          │         │ prep_time        │
│ bio             │         │ cook_time        │
│ is_active       │         │ servings         │
│ created_at      │         │ difficulty       │
└─────────────────┘         │ image_url        │
         │                  │ recipe_type      │
         │                  │ likes_count      │
         │                  │ is_published     │
         │                  │ created_at       │
         │                  └──────────────────┘
         │                           │
         │                           │
         │                  ┌────────┴─────────┐
         │                  │                  │
         └─────────┬────────┴─────────┐        │
                   │                  │        │
         ┌─────────▼──────────┐ ┌─────▼────────▼─────┐
         │  RECIPE_FAVORITES  │ │  COMMUNITY_POSTS   │
         ├────────────────────┤ ├────────────────────┤
         │ id (PK)            │ │ id (PK)            │
         │ user_id (FK)       │ │ author_id (FK)     │
         │ recipe_id (FK)     │ │ title              │
         │ created_at         │ │ content            │
         └────────────────────┘ │ post_type          │
                                │ likes_count        │
                                │ created_at         │
                                └────────────────────┘
```

### Key Tables

#### Users
- Stores user authentication and profile data
- Roles: `user`, `moderator`, `admin`
- User types: `Celiac`, `Nutritionist`, `Chef`, `Food Blogger`

#### Recipes
- Recipe content and metadata
- Recipe types: `recipe`, `tip`
- Soft delete support (`is_published`)

#### Recipe_Favorites
- Many-to-many relationship
- Tracks user favorites
- Unique constraint on (user_id, recipe_id)

#### Community_Posts
- User-generated content
- Post types: `tip`, `question`, `story`
- Moderation support

---

##  Installation

### Prerequisites

- Docker Desktop
- Git
- Modern web browser

### Step 1: Clone Repository

```bash
git clone https://github.com/yourusername/GlutenFreePlus.git
cd GlutenFreePlus
```

### Step 2: Environment Configuration

Create `.env` file from template:

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
# Database Configuration
DB_HOST=glutenfreeplus-db-1
DB_PORT=5432
DB_NAME=glutenfree_db
DB_USER=postgres
DB_PASSWORD=your_secure_password

# Application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8081

# Security
SESSION_LIFETIME=7200
CSRF_TOKEN_LIFETIME=3600
```

### Step 3: Start Docker Containers

```bash
docker-compose up -d
```

This will start:
- **nginx** (port 8081) - Web server
- **php** - PHP-FPM 8.2
- **postgres** (port 5432) - Database

### Step 4: Initialize Database

```bash
# Import database schema
docker exec -i glutenfreeplus-db-1 psql -U postgres -d glutenfree_db < database/schema.sql

# Import sample data (optional)
docker exec -i glutenfreeplus-db-1 psql -U postgres -d glutenfree_db < database/seed.sql
```

### Step 5: Access Application

Open browser and navigate to:
```
http://localhost:8081
```

---

##  Usage

### Test Accounts

#### Admin Account
```
Email: admin@glutenfree.com
Password: password
Role: Admin (full access)
```

#### Moderator Account
```
Email: tommy.s@example.com
Password: admin123
Role: Moderator (content moderation)
```

#### Regular Users
```
Email: chris@gmail.com
Password: admin123
Role: User

Email: victoria1@gmail.com
Password: admin123
Role: User
```

### Admin Panel Access

1. Login with admin account
2. Click "User Management" in sidebar
3. Features:
   - View all users
   - Change user roles
   - Block/unblock users
   - Delete users
   - View statistics

### Moderator Panel Access

1. Login with moderator/admin account
2. Click "Content Moderation" in sidebar
3. Features:
   - Review posts and comments
   - Hide inappropriate content
   - Delete posts/comments
   - View moderation stats

### Creating Recipes

1. Navigate to "Recipes"
2. Click the green "+" button
3. Fill in recipe details:
   - Title (required)
   - Description
   - Ingredients (required)
   - Instructions (required)
   - Prep time, cook time, servings
   - Difficulty level
   - Upload image
4. Click "Create Recipe"

### Discovering Places

1. Navigate to "Discover places"
2. Use filters: Restaurants, Bakeries, Certified GF
3. Click map markers for details
4. View place information and directions

---

##  Testing

### Unit Tests (PHPUnit)

Run all unit tests:
```bash
docker exec -it glutenfree-php vendor/bin/phpunit
```

Run specific test suite:
```bash
docker exec -it glutenfree-php vendor/bin/phpunit tests/Unit/RecipeTest.php
docker exec -it glutenfree-php vendor/bin/phpunit tests/Unit/UserTest.php
```

**Test Coverage:**
- RecipeTest: 11 tests (model validation, getters/setters, serialization)
- UserTest: 11 tests (authentication, roles, data integrity)
- Total: 22 unit tests, 49 assertions

### Integration Tests (bash/cURL)

Run integration tests:
```bash
docker exec -it glutenfree-php bash tests/Integration/integration-tests.sh
```

**Test Coverage:**
- Authentication flows (login, register)
- Access control (dashboard, admin panel)
- Static asset delivery
- Error handling (400, 404, 500)
- CSRF protection
- Total: 10 integration tests

### Test Results

```
✓ 22/22 PHPUnit tests passing
✓ 10/10 Integration tests passing
✓ 32/32 total tests passing (100%)
```

---

##  Screenshots

### Login Page
![Login](screenshots/1-login.png)

### Dashboard
![Dashboard](screenshots/2-dashboard.png)

### Community
![Community](screenshots/3-community.png)

### Product Scanner
![Scanner](screenshots/4-scanner.png)

### Discover Places
![Discover](screenshots/5-discover.png)

### Recipes
![Recipes](screenshots/6-recipes.png)

### Recipe Detail
![Recipe Detail](screenshots/7-recipe-detail.png)

### Admin Panel
![Admin Panel](screenshots/8-admin-panel.png)

### Moderator Panel
![Moderator Panel](screenshots/9-moderator-panel.png)

---

##  Security Features

### Authentication
- Bcrypt password hashing (cost factor 12)
- Secure session management
- Remember me functionality
- Session timeout (2 hours default)
- Account lockout after failed attempts

### Authorization
- Role-based access control (RBAC)
- Route-level permissions
- Method-level guards
- Resource ownership validation

### Input Validation
- CSRF tokens on all forms
- XSS prevention (htmlspecialchars)
- SQL injection prevention (PDO prepared statements)
- Input sanitization
- File upload validation

### Rate Limiting
- Login attempts: 5 per 15 minutes
- Registration: 3 per hour per IP
- API endpoints: configurable limits
- Failed login tracking

### Headers & Cookies
- Secure cookie flags (HttpOnly, Secure, SameSite)
- Content Security Policy
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff

### Error Handling
- Custom error pages (400, 404, 500)
- Error logging
- No sensitive data in errors
- Stack trace hiding in production

---

##  User Roles

### User (Default)
- Browse community and recipes
- Use product scanner
- Discover places
- Create recipes
- Favorite content
- View own profile

### Moderator
- All User permissions
- **Plus:**
  - Access Content Moderation panel
  - Review community posts
  - Hide/delete inappropriate content
  - View moderation statistics
  - Cannot access Admin panel

### Admin
- All Moderator permissions
- **Plus:**
  - Access Admin panel
  - Manage all users
  - Change user roles
  - Block/unblock accounts
  - Delete users
  - View system statistics
  - Full access to all features

---

##  API Endpoints

### Authentication
```
POST   /register          - Create new account
POST   /login             - User login
GET    /logout            - User logout
```

### Recipes
```
GET    /recipes           - List all recipes
GET    /recipes/show?id=X - Recipe details
GET    /recipes/create    - Recipe form
POST   /recipes/store     - Create recipe
POST   /recipes/toggle-favorite - Add/remove favorite
POST   /recipes/delete    - Delete recipe
GET    /recipes/get?type=X - Get recipes by type (recipes/tips/favourites)
```

### Admin
```
GET    /admin/users       - User management page
GET    /admin/users/list  - Get users (AJAX)
POST   /admin/users/role  - Change user role
POST   /admin/users/block - Block/unblock user
POST   /admin/users/delete - Delete user
```

### Moderator
```
GET    /moderator/content - Content moderation page
GET    /moderator/posts/list - Get posts (AJAX)
GET    /moderator/comments/list - Get comments (AJAX)
POST   /moderator/posts/hide - Hide post
POST   /moderator/posts/delete - Delete post
POST   /moderator/comments/delete - Delete comment
```

### Community
```
GET    /community         - Community page
GET    /community/members - Get members (AJAX)
GET    /profile?id=X      - User profile
GET    /profile           - Current user profile
```

---

##  Environment Variables

Create `.env` file with these variables:

```env
# Database
DB_HOST=glutenfreeplus-db-1
DB_PORT=5432
DB_NAME=glutenfree_db
DB_USER=postgres
DB_PASSWORD=your_password_here

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=http://yourdomain.com

# Security
SESSION_LIFETIME=7200
CSRF_TOKEN_LIFETIME=3600
MAX_LOGIN_ATTEMPTS=5
RATE_LIMIT_WINDOW=900

# File Upload
MAX_UPLOAD_SIZE=5242880
ALLOWED_EXTENSIONS=jpg,jpeg,png,webp
UPLOAD_PATH=/public/images/recipes/
```


---

##  Acknowledgments

- Inter font by Google Fonts
- Leaflet.js for interactive maps
- Docker for containerization
- PostgreSQL for robust database
- PHPUnit for testing framework

