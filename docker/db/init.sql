-- GlutenFree+ Database Schema

-- Rozszerzenie do obsługi UUID
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Tabela użytkowników
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    bio TEXT,
    avatar_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela członków społeczności
CREATE TABLE IF NOT EXISTS community_members (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    member_type VARCHAR(50) NOT NULL, -- celiac, nutritionist, food_blogger, chef
    specialty TEXT,
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela produktów
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(255),
    barcode VARCHAR(50) UNIQUE,
    is_gluten_free BOOLEAN DEFAULT FALSE,
    certification VARCHAR(100),
    description TEXT,
    image_url VARCHAR(500),
    ingredients TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela alternatywnych produktów
CREATE TABLE IF NOT EXISTS product_alternatives (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    alternative_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela miejsc (restauracje, piekarnie, sklepy)
CREATE TABLE IF NOT EXISTS places (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL, -- restaurant, bakery, store, cafe
    address TEXT NOT NULL,
    city VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    phone VARCHAR(20),
    website VARCHAR(500),
    rating DECIMAL(3, 2) DEFAULT 0.0,
    is_certified_gf BOOLEAN DEFAULT FALSE,
    description TEXT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela opinii o miejscach
CREATE TABLE IF NOT EXISTS place_reviews (
    id SERIAL PRIMARY KEY,
    place_id INTEGER REFERENCES places(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela przepisów
CREATE TABLE IF NOT EXISTS recipes (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    author_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    category VARCHAR(50), -- breakfast, lunch, dinner, dessert, snack
    prep_time INTEGER, -- w minutach
    cook_time INTEGER, -- w minutach
    servings INTEGER,
    difficulty VARCHAR(20), -- easy, medium, hard
    ingredients TEXT NOT NULL,
    instructions TEXT NOT NULL,
    image_url VARCHAR(500),
    likes_count INTEGER DEFAULT 0,
    comments_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela ulubionych przepisów
CREATE TABLE IF NOT EXISTS favorite_recipes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    recipe_id INTEGER REFERENCES recipes(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, recipe_id)
);

-- Tabela polubień przepisów
CREATE TABLE IF NOT EXISTS recipe_likes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    recipe_id INTEGER REFERENCES recipes(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, recipe_id)
);

-- Tabela komentarzy do przepisów
CREATE TABLE IF NOT EXISTS recipe_comments (
    id SERIAL PRIMARY KEY,
    recipe_id INTEGER REFERENCES recipes(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela wskazówek (tips)
CREATE TABLE IF NOT EXISTS tips (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    category VARCHAR(50),
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indeksy dla lepszej wydajności
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_products_gluten_free ON products(is_gluten_free);
CREATE INDEX idx_places_type ON places(type);
CREATE INDEX idx_places_certified ON places(is_certified_gf);
CREATE INDEX idx_recipes_author ON recipes(author_id);
CREATE INDEX idx_recipes_category ON recipes(category);
CREATE INDEX idx_community_type ON community_members(member_type);

-- Trigger do aktualizacji updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_products_updated_at BEFORE UPDATE ON products
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_places_updated_at BEFORE UPDATE ON places
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_recipes_updated_at BEFORE UPDATE ON recipes
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Przykładowe dane testowe
-- Użytkownicy (hasło: password123)
INSERT INTO users (email, password, name, role, bio) VALUES
('admin@glutenfree.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin', 'Administrator aplikacji GlutenFree+'),
('tommy@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tommy S.', 'user', 'Living with celiac disease for 5 years'),
('marco@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marco P.', 'user', 'Certified nutritionist specializing in gluten-free diets'),
('sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah M.', 'user', 'Professional chef and food blogger'),
('alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice T.', 'user', 'Food blogger sharing gluten-free recipes');

-- Członkowie społeczności
INSERT INTO community_members (user_id, member_type, specialty, verified) VALUES
(2, 'celiac', 'Living with celiac disease', TRUE),
(3, 'nutritionist', 'Gluten-free nutrition expert', TRUE),
(4, 'chef', 'Gluten-free cuisine specialist', TRUE),
(5, 'food_blogger', 'Recipe development and food photography', TRUE);

-- Produkty
INSERT INTO products (name, brand, barcode, is_gluten_free, certification, description, ingredients) VALUES
('Organic Wholewheat Bred', 'Nature''s Bakery', '1234567890123', TRUE, 'Certified Gluten-Free', 'Organic wholewheat bread made without gluten', 'Rice flour, water, yeast, salt, xanthan gum'),
('GF Yeasted Puff Pastry', 'Simple Mills', '2345678901234', TRUE, 'Certified Gluten-Free', 'Light and flaky gluten-free puff pastry', 'Almond flour, tapioca starch, butter, eggs'),
('Multigrain GF Bread', 'Canyon Bakehouse', '3456789012345', TRUE, 'Certified Gluten-Free', 'Nutritious multigrain gluten-free bread', 'Brown rice flour, quinoa, millet, flax seeds'),
('Chocolate Cookies', 'Enjoy Life', '4567890123456', TRUE, 'Certified Gluten-Free', 'Delicious chocolate chip cookies', 'Rice flour, chocolate chips, sugar, vanilla'),
('Regular Wheat Bread', 'Standard Bakery', '5678901234567', FALSE, NULL, 'Traditional wheat bread', 'Wheat flour, water, yeast, salt');

-- Alternatywne produkty
INSERT INTO product_alternatives (product_id, alternative_id) VALUES
(5, 1),
(5, 3);

-- Miejsca
INSERT INTO places (name, type, address, city, latitude, longitude, rating, is_certified_gf, description) VALUES
('Ristorante My Heart 2', 'restaurant', 'Via Filippo Juvara 12', 'Milano', 45.4773, 9.1815, 4.5, TRUE, 'Italian restaurant with extensive gluten-free menu'),
('Toast to Coast Città Studi', 'cafe', 'Via Carlo Goldoni 8', 'Milano', 45.4785, 9.2310, 4.2, TRUE, 'Cozy cafe with gluten-free toasts and pastries'),
('GluFree Bakery', 'bakery', 'Via Pino 15', 'Milano', 45.4692, 9.1885, 4.8, TRUE, '100% gluten-free bakery with fresh daily products'),
('Mama Eat - Gluten-free Restaurant & Pizzeria', 'restaurant', 'Viale Romagna 44', 'Milano', 45.4668, 9.2165, 4.6, TRUE, 'Dedicated gluten-free restaurant and pizzeria');

-- Przepisy
INSERT INTO recipes (title, description, author_id, category, prep_time, cook_time, servings, difficulty, ingredients, instructions, likes_count, comments_count) VALUES
('Gluten free fluffy Pancakes', 'Light and fluffy gluten-free pancakes perfect for breakfast', 5, 'breakfast', 10, 15, 4, 'easy', 
'1 cup gluten-free flour mix, 1 tbsp sugar, 2 tsp baking powder, 1/2 tsp salt, 1 cup milk, 1 egg, 2 tbsp melted butter',
'1. Mix dry ingredients. 2. Whisk wet ingredients separately. 3. Combine and let rest 5 minutes. 4. Cook on medium heat until bubbles form. 5. Flip and cook until golden.',
60, 41),
('Gluten free Lasagna', 'Classic Italian lasagna made with gluten-free pasta', 4, 'lunch', 30, 60, 6, 'medium',
'Gluten-free lasagna sheets, ground beef, tomato sauce, ricotta cheese, mozzarella, parmesan, herbs',
'1. Prepare meat sauce. 2. Cook lasagna sheets. 3. Layer sauce, pasta, and cheese. 4. Repeat layers. 5. Bake at 180°C for 45 minutes.',
13, 5),
('Gluten free banana bread', 'Moist and delicious banana bread', 5, 'dessert', 15, 50, 8, 'easy',
'3 ripe bananas, 2 eggs, 1/3 cup honey, 1/4 cup coconut oil, 2 cups gluten-free flour, 1 tsp baking soda',
'1. Mash bananas. 2. Mix wet ingredients. 3. Add dry ingredients. 4. Pour into loaf pan. 5. Bake at 175°C for 50 minutes.',
45, 18);

-- Tips
INSERT INTO tips (title, content, author_id, category) VALUES
('Reading Labels', 'Always check for "gluten-free" certification. Look for hidden sources of gluten in ingredients like malt, modified food starch, and hydrolyzed vegetable protein.', 3, 'shopping'),
('Cross-Contamination', 'When cooking gluten-free at home, use separate cutting boards, toasters, and cooking utensils to avoid cross-contamination.', 3, 'cooking'),
('Dining Out', 'When eating at restaurants, always inform the server about your gluten-free requirements and ask about preparation methods.', 4, 'lifestyle');

-- Polubienia przepisów
INSERT INTO recipe_likes (user_id, recipe_id) VALUES
(2, 1), (3, 1), (4, 1),
(2, 2), (5, 2);

-- Ulubione przepisy
INSERT INTO favorite_recipes (user_id, recipe_id) VALUES
(2, 1), (2, 3),
(3, 2);

COMMIT;