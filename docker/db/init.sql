-- ==================== GLUTENFREE+ DATABASE SCHEMA ====================

-- Drop existing tables if exists (for development)
DROP TABLE IF EXISTS ratings CASCADE;
DROP TABLE IF EXISTS favorites CASCADE;
DROP TABLE IF EXISTS comments CASCADE;
DROP TABLE IF EXISTS posts CASCADE;
DROP TABLE IF EXISTS recipes CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS places CASCADE;
DROP TABLE IF EXISTS user_profiles CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Drop existing types
DROP TYPE IF EXISTS user_role CASCADE;
DROP TYPE IF EXISTS community_user_type CASCADE;
DROP TYPE IF EXISTS place_type CASCADE;
DROP TYPE IF EXISTS favorite_type CASCADE;

-- ==================== EXTENSIONS ====================
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ==================== ENUM TYPES ====================
CREATE TYPE user_role AS ENUM ('user', 'moderator', 'admin');
CREATE TYPE community_user_type AS ENUM ('Celiac', 'Nutritionist', 'Food Blogger', 'Chef');
CREATE TYPE place_type AS ENUM ('restaurant', 'bakery', 'store', 'cafe');
CREATE TYPE favorite_type AS ENUM ('recipe', 'product', 'place');

-- ==================== TABLE: users ====================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role user_role DEFAULT 'user',
    user_type community_user_type,
    avatar VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    
    CONSTRAINT email_valid CHECK (email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$')
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_user_type ON users(user_type);
CREATE INDEX idx_users_is_active ON users(is_active);
CREATE INDEX idx_users_role ON users(role);

-- ==================== TABLE: user_profiles (1-to-1 with users) ====================
CREATE TABLE user_profiles (
    user_id INTEGER PRIMARY KEY,
    bio TEXT,
    phone VARCHAR(20),
    location VARCHAR(255),
    website VARCHAR(500),
    dietary_restrictions TEXT,
    celiac_diagnosed_date DATE,
    preferences JSONB,
    
    CONSTRAINT fk_user_profile 
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);

CREATE INDEX idx_profiles_user_id ON user_profiles(user_id);

-- ==================== TABLE: categories ====================
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_categories_name ON categories(name);

-- ==================== TABLE: products ====================
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(255) NOT NULL,
    barcode VARCHAR(50) UNIQUE,
    category_id INTEGER,
    is_gluten_free BOOLEAN DEFAULT false,
    is_certified BOOLEAN DEFAULT false,
    ingredients TEXT,
    allergens TEXT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER,
    
    CONSTRAINT fk_product_category 
        FOREIGN KEY (category_id) 
        REFERENCES categories(id) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_product_creator 
        FOREIGN KEY (created_by) 
        REFERENCES users(id) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
);

CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_gluten_free ON products(is_gluten_free);

-- ==================== TABLE: recipes ====================
CREATE TABLE recipes (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    ingredients TEXT NOT NULL,
    instructions TEXT NOT NULL,
    prep_time INTEGER, -- minutes
    cook_time INTEGER, -- minutes
    servings INTEGER DEFAULT 1,
    difficulty VARCHAR(20) CHECK (difficulty IN ('easy', 'medium', 'hard')),
    image_url VARCHAR(500),
    author_id INTEGER NOT NULL,
    likes_count INTEGER DEFAULT 0,
    comments_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_published BOOLEAN DEFAULT true,
    
    CONSTRAINT fk_recipe_author 
        FOREIGN KEY (author_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT valid_times CHECK (prep_time >= 0 AND cook_time >= 0),
    CONSTRAINT valid_servings CHECK (servings > 0)
);

CREATE INDEX idx_recipes_author ON recipes(author_id);
CREATE INDEX idx_recipes_published ON recipes(is_published);
CREATE INDEX idx_recipes_created ON recipes(created_at DESC);

-- ==================== TABLE: posts ====================
CREATE TABLE posts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    post_type VARCHAR(20) CHECK (post_type IN ('question', 'tip', 'story', 'general')),
    author_id INTEGER NOT NULL,
    likes_count INTEGER DEFAULT 0,
    comments_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_published BOOLEAN DEFAULT true,
    
    CONSTRAINT fk_post_author 
        FOREIGN KEY (author_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);

CREATE INDEX idx_posts_author ON posts(author_id);
CREATE INDEX idx_posts_type ON posts(post_type);
CREATE INDEX idx_posts_created ON posts(created_at DESC);

-- ==================== TABLE: comments ====================
CREATE TABLE comments (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    author_id INTEGER NOT NULL,
    recipe_id INTEGER,
    post_id INTEGER,
    parent_comment_id INTEGER, -- for nested comments
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_deleted BOOLEAN DEFAULT false,
    
    CONSTRAINT fk_comment_author 
        FOREIGN KEY (author_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_comment_recipe 
        FOREIGN KEY (recipe_id) 
        REFERENCES recipes(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_comment_post 
        FOREIGN KEY (post_id) 
        REFERENCES posts(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_comment_parent 
        FOREIGN KEY (parent_comment_id) 
        REFERENCES comments(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT comment_target CHECK (
        (recipe_id IS NOT NULL AND post_id IS NULL) OR
        (recipe_id IS NULL AND post_id IS NOT NULL)
    )
);

CREATE INDEX idx_comments_recipe ON comments(recipe_id);
CREATE INDEX idx_comments_post ON comments(post_id);
CREATE INDEX idx_comments_author ON comments(author_id);
CREATE INDEX idx_comments_parent ON comments(parent_comment_id);

-- ==================== TABLE: places ====================
CREATE TABLE places (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type place_type NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100),
    country VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    phone VARCHAR(20),
    website VARCHAR(500),
    is_certified BOOLEAN DEFAULT false,
    description TEXT,
    image_url VARCHAR(500),
    average_rating DECIMAL(3, 2) DEFAULT 0,
    ratings_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    added_by INTEGER,
    
    CONSTRAINT fk_place_creator 
        FOREIGN KEY (added_by) 
        REFERENCES users(id) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE,
    
    CONSTRAINT valid_rating CHECK (average_rating >= 0 AND average_rating <= 5),
    CONSTRAINT valid_coordinates CHECK (
        (latitude IS NULL AND longitude IS NULL) OR
        (latitude BETWEEN -90 AND 90 AND longitude BETWEEN -180 AND 180)
    )
);

CREATE INDEX idx_places_type ON places(type);
CREATE INDEX idx_places_city ON places(city);
CREATE INDEX idx_places_certified ON places(is_certified);
CREATE INDEX idx_places_location ON places(latitude, longitude);

-- ==================== TABLE: ratings ====================
CREATE TABLE ratings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    place_id INTEGER NOT NULL,
    rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_rating_user 
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_rating_place 
        FOREIGN KEY (place_id) 
        REFERENCES places(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT unique_user_place_rating UNIQUE (user_id, place_id)
);

CREATE INDEX idx_ratings_user ON ratings(user_id);
CREATE INDEX idx_ratings_place ON ratings(place_id);

-- ==================== TABLE: favorites (many-to-many) ====================
CREATE TABLE favorites (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    favorite_type favorite_type NOT NULL,
    recipe_id INTEGER,
    product_id INTEGER,
    place_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_favorite_user 
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_favorite_recipe 
        FOREIGN KEY (recipe_id) 
        REFERENCES recipes(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_favorite_product 
        FOREIGN KEY (product_id) 
        REFERENCES products(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_favorite_place 
        FOREIGN KEY (place_id) 
        REFERENCES places(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT favorite_item_check CHECK (
        (favorite_type = 'recipe' AND recipe_id IS NOT NULL AND product_id IS NULL AND place_id IS NULL) OR
        (favorite_type = 'product' AND product_id IS NOT NULL AND recipe_id IS NULL AND place_id IS NULL) OR
        (favorite_type = 'place' AND place_id IS NOT NULL AND recipe_id IS NULL AND product_id IS NULL)
    ),
    
    CONSTRAINT unique_favorite UNIQUE (user_id, favorite_type, recipe_id, product_id, place_id)
);

CREATE INDEX idx_favorites_user ON favorites(user_id);
CREATE INDEX idx_favorites_recipe ON favorites(recipe_id);
CREATE INDEX idx_favorites_product ON favorites(product_id);
CREATE INDEX idx_favorites_place ON favorites(place_id);

-- ==================== VIEWS ====================

-- VIEW 1: User Activity Summary
CREATE OR REPLACE VIEW v_user_activity AS
SELECT 
    u.id,
    u.name,
    u.email,
    u.user_type,
    u.created_at AS joined_date,
    COUNT(DISTINCT r.id) AS recipes_count,
    COUNT(DISTINCT p.id) AS posts_count,
    COUNT(DISTINCT c.id) AS comments_count,
    COUNT(DISTINCT f.id) AS favorites_count,
    COALESCE(SUM(r.likes_count), 0) AS total_recipe_likes,
    u.last_login
FROM users u
LEFT JOIN recipes r ON u.id = r.author_id AND r.is_published = true
LEFT JOIN posts p ON u.id = p.author_id AND p.is_published = true
LEFT JOIN comments c ON u.id = c.author_id AND c.is_deleted = false
LEFT JOIN favorites f ON u.id = f.user_id
WHERE u.is_active = true
GROUP BY u.id, u.name, u.email, u.user_type, u.created_at, u.last_login
ORDER BY total_recipe_likes DESC;

-- VIEW 2: Popular Recipes with Author Info
CREATE OR REPLACE VIEW v_popular_recipes AS
SELECT 
    r.id,
    r.title,
    r.description,
    r.difficulty,
    r.prep_time,
    r.cook_time,
    r.servings,
    r.image_url,
    r.likes_count,
    r.comments_count,
    r.created_at,
    u.id AS author_id,
    u.name AS author_name,
    u.user_type AS author_type,
    u.avatar AS author_avatar,
    ROUND((r.prep_time + r.cook_time)::NUMERIC / 60, 1) AS total_hours
FROM recipes r
INNER JOIN users u ON r.author_id = u.id
WHERE r.is_published = true AND u.is_active = true
ORDER BY r.likes_count DESC, r.created_at DESC;

-- ==================== FUNCTIONS ====================

-- FUNCTION: Get User Activity Rank
CREATE OR REPLACE FUNCTION get_user_rank(p_user_id INTEGER)
RETURNS TEXT
LANGUAGE plpgsql
AS $$
DECLARE
    v_recipes_count INTEGER;
    v_total_likes INTEGER;
    v_rank TEXT;
BEGIN
    SELECT 
        COUNT(DISTINCT r.id),
        COALESCE(SUM(r.likes_count), 0)
    INTO v_recipes_count, v_total_likes
    FROM recipes r
    WHERE r.author_id = p_user_id AND r.is_published = true;
    
    IF v_recipes_count = 0 THEN
        v_rank := 'Newcomer';
    ELSIF v_recipes_count < 5 THEN
        v_rank := 'Home Cook';
    ELSIF v_recipes_count < 10 AND v_total_likes < 50 THEN
        v_rank := 'Regular Chef';
    ELSIF v_recipes_count >= 10 AND v_total_likes >= 50 THEN
        v_rank := 'Master Chef';
    ELSE
        v_rank := 'Active Member';
    END IF;
    
    RETURN v_rank;
END;
$$;

-- FUNCTION: Update Place Average Rating
CREATE OR REPLACE FUNCTION update_place_rating(p_place_id INTEGER)
RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
    v_avg_rating DECIMAL(3,2);
    v_count INTEGER;
BEGIN
    SELECT 
        ROUND(AVG(rating)::NUMERIC, 2),
        COUNT(*)
    INTO v_avg_rating, v_count
    FROM ratings
    WHERE place_id = p_place_id;
    
    UPDATE places
    SET 
        average_rating = COALESCE(v_avg_rating, 0),
        ratings_count = v_count
    WHERE id = p_place_id;
END;
$$;

-- ==================== TRIGGERS ====================

-- TRIGGER 1: Update updated_at column (już istnieje, rozszerzamy)
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Apply to all tables with updated_at
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_products_updated_at BEFORE UPDATE ON products
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_recipes_updated_at BEFORE UPDATE ON recipes
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_posts_updated_at BEFORE UPDATE ON posts
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_comments_updated_at BEFORE UPDATE ON comments
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_places_updated_at BEFORE UPDATE ON places
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- TRIGGER 2: Auto-increment recipe likes count
CREATE OR REPLACE FUNCTION increment_recipe_likes()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.favorite_type = 'recipe' AND NEW.recipe_id IS NOT NULL THEN
        UPDATE recipes
        SET likes_count = likes_count + 1
        WHERE id = NEW.recipe_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_increment_recipe_likes
AFTER INSERT ON favorites
FOR EACH ROW
EXECUTE FUNCTION increment_recipe_likes();

-- TRIGGER 3: Auto-decrement recipe likes count
CREATE OR REPLACE FUNCTION decrement_recipe_likes()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.favorite_type = 'recipe' AND OLD.recipe_id IS NOT NULL THEN
        UPDATE recipes
        SET likes_count = GREATEST(likes_count - 1, 0)
        WHERE id = OLD.recipe_id;
    END IF;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_decrement_recipe_likes
AFTER DELETE ON favorites
FOR EACH ROW
EXECUTE FUNCTION decrement_recipe_likes();

-- TRIGGER 4: Auto-increment comments count
CREATE OR REPLACE FUNCTION increment_comments_count()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.recipe_id IS NOT NULL THEN
        UPDATE recipes
        SET comments_count = comments_count + 1
        WHERE id = NEW.recipe_id;
    ELSIF NEW.post_id IS NOT NULL THEN
        UPDATE posts
        SET comments_count = comments_count + 1
        WHERE id = NEW.post_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_increment_comments
AFTER INSERT ON comments
FOR EACH ROW
EXECUTE FUNCTION increment_comments_count();

-- TRIGGER 5: Update place rating after new rating
CREATE OR REPLACE FUNCTION trigger_update_place_rating()
RETURNS TRIGGER AS $$
BEGIN
    PERFORM update_place_rating(NEW.place_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_rating_insert
AFTER INSERT ON ratings
FOR EACH ROW
EXECUTE FUNCTION trigger_update_place_rating();

CREATE TRIGGER trigger_rating_update
AFTER UPDATE ON ratings
FOR EACH ROW
EXECUTE FUNCTION trigger_update_place_rating();

-- ==================== SAMPLE DATA ====================

-- Insert Users (password: admin123)
INSERT INTO users (email, password, name, role, user_type, avatar) VALUES
('admin@glutenfree.com', '$2y$10$aGw74EdQ0wonxHviUJYrrecgfhiDa8wBDjip/GOA3xo5pCydLdqvK', 'Admin User', 'admin', NULL, NULL),
('victoria1@gmail.com', '$2y$10$aGw74EdQ0wonxHviUJYrrecgfhiDa8wBDjip/GOA3xo5pCydLdqvK', 'Victoria Smith', 'user', 'Celiac', '/public/images/avatars/victoria.png'),
('tommy.s@example.com', '$2y$10$aGw74EdQ0wonxHviUJYrrecgfhiDa8wBDjip/GOA3xo5pCydLdqvK', 'Tommy S.', 'user', 'Celiac', '/public/images/avatars/tommy.png'),
('marco.p@example.com', '$2y$10$aGw74EdQ0wonxHviUJYrrecgfhiDa8wBDjip/GOA3xo5pCydLdqvK', 'Marco P.', 'user', 'Nutritionist', '/public/images/avatars/marco.png'),
('tobby.f@example.com', '$2y$10$aGw74EdQ0wonxHviUJYrrecgfhiDa8wBDjip/GOA3xo5pCydLdqvK', 'Tobby F.', 'user', 'Nutritionist', '/public/images/avatars/tobby.png'),
('sarah.m@example.com', '$2y$10$aGw74EdQ0wonxHviUJYrrecgfhiDa8wBDjip/GOA3xo5pCydLdqvK', 'Sarah M.', 'user', 'Chef', '/public/images/avatars/sarah.png'),
('alice.t@example.com', '$2y$10$aGw74EdQ0wonxHviUJYrrecgfhiDa8wBDjip/GOA3xo5pCydLdqvK', 'Alice T.', 'user', 'Food Blogger', '/public/images/avatars/alice.png');

-- Insert User Profiles (1-to-1)
INSERT INTO user_profiles (user_id, bio, location, dietary_restrictions, celiac_diagnosed_date) VALUES
(2, 'Living gluten-free since 2018. Sharing my journey and tips!', 'New York, USA', 'Celiac disease, lactose intolerant', '2018-03-15'),
(3, 'Celiac warrior and food enthusiast', 'London, UK', 'Celiac disease', '2015-06-20'),
(4, 'Nutritionist specializing in gluten-free diets', 'Milan, Italy', NULL, NULL),
(6, 'Professional chef creating amazing GF recipes', 'Paris, France', NULL, NULL);

-- Insert Categories
INSERT INTO categories (name, description, icon) VALUES
('Bakery', 'Bread, pastries, and baked goods', '🥖'),
('Pasta & Grains', 'Pasta, rice, and grain products', '🍝'),
('Snacks', 'Chips, crackers, and snack foods', '🍿'),
('Desserts', 'Cakes, cookies, and sweet treats', '🍰'),
('Beverages', 'Drinks and beverages', '🥤'),
('Dairy', 'Milk, cheese, and dairy products', '🧀');

-- Insert Products
INSERT INTO products (name, brand, barcode, category_id, is_gluten_free, is_certified, ingredients, created_by) VALUES
('Organic Wholewheat Bread', 'Natures Bakery', '5901234123457', 1, true, true, 'Rice flour, water, yeast, salt, olive oil', 2),
('Gluten Free Pasta', 'Barilla', '5901234123458', 2, true, true, 'Corn flour, rice flour', 2),
('GF Chocolate Chip Cookies', 'Simple Mills', '5901234123459', 4, true, true, 'Almond flour, chocolate chips, honey', 3),
('Rice Crackers', 'Crunchmaster', '5901234123460', 3, true, false, 'Brown rice, sesame seeds', 4),
('Almond Milk', 'Almond Breeze', '5901234123461', 5, true, true, 'Almonds, water, calcium', 2);

-- Insert Recipes
INSERT INTO recipes (title, description, ingredients, instructions, prep_time, cook_time, servings, difficulty, author_id, likes_count, comments_count) VALUES
('Gluten Free Fluffy Pancakes', 'Light and fluffy pancakes perfect for breakfast', 'GF flour, eggs, milk, baking powder, vanilla', 'Mix dry ingredients. Add wet ingredients. Cook on griddle.', 10, 15, 4, 'easy', 2, 60, 41),
('Gluten Free Banana Bread', 'Moist and delicious banana bread', 'GF flour, bananas, eggs, butter, sugar', 'Mash bananas. Mix all ingredients. Bake at 350F for 50 minutes.', 15, 50, 8, 'easy', 6, 60, 41),
('Gluten Free Dumplings', 'Traditional dumplings with GF wrapper', 'GF flour, water, pork, cabbage, soy sauce', 'Make dough. Prepare filling. Form dumplings. Steam or pan-fry.', 45, 20, 6, 'hard', 3, 60, 41),
('Gluten Free Lasagna', 'Classic Italian lasagna made gluten-free', 'GF lasagna sheets, beef, tomato sauce, cheese', 'Layer ingredients. Bake at 375F for 45 minutes.', 30, 45, 6, 'medium', 6, 13, 5),
('Gluten Free Pizza Dough', 'Perfect pizza dough recipe', 'GF flour mix, yeast, water, olive oil', 'Mix ingredients. Let rise 1 hour. Roll and bake.', 20, 15, 2, 'medium', 4, 60, 41),
('Gluten Free Strawberry Cake', 'Light and fruity cake', 'GF flour, strawberries, eggs, sugar, cream', 'Make batter. Bake. Add strawberry frosting.', 25, 35, 10, 'medium', 2, 60, 41);

-- Insert Posts
INSERT INTO posts (title, content, post_type, author_id, likes_count, comments_count) VALUES
('Tips for Dining Out Gluten-Free', 'Always call ahead and ask about gluten-free options...', 'tip', 2, 45, 12),
('My Celiac Diagnosis Story', 'I want to share my journey of being diagnosed with celiac disease...', 'story', 3, 78, 23),
('Best GF Bread Brands?', 'What are your favorite gluten-free bread brands?', 'question', 5, 34, 18),
('Cross-Contamination Prevention', 'Here are my top tips for preventing cross-contamination at home...', 'tip', 4, 56, 15);

-- Insert Comments
INSERT INTO comments (content, author_id, recipe_id, created_at) VALUES
('This recipe is amazing! Made it for my family.', 3, 1, NOW() - INTERVAL '2 days'),
('Can I substitute almond milk?', 5, 1, NOW() - INTERVAL '1 day'),
('Best banana bread Ive ever had!', 4, 2, NOW() - INTERVAL '3 days'),
('What temperature should the oven be?', 7, 2, NOW() - INTERVAL '5 hours'),
('This looks delicious!', 2, 3, NOW() - INTERVAL '1 hour');

INSERT INTO comments (content, author_id, post_id, created_at) VALUES
('Thank you for sharing your story!', 4, 2, NOW() - INTERVAL '1 day'),
('I love Canyon Bakehouse bread', 5, 3, NOW() - INTERVAL '2 hours'),
('Great tips! Very helpful.', 2, 4, NOW() - INTERVAL '3 days');

-- Insert Places
INSERT INTO places (name, type, address, city, country, latitude, longitude, is_certified, description, added_by, average_rating, ratings_count) VALUES
('Ristorante My Heart 2', 'restaurant', 'Via Roma 123', 'Milan', 'Italy', 45.4642, 9.1900, true, 'Italian restaurant with extensive GF menu', 2, 4.5, 24),
('GluFree Bakery', 'bakery', 'Via Milano 45', 'Milan', 'Italy', 45.4654, 9.1859, true, '100% gluten-free bakery', 3, 4.8, 156),
('Mama Eat Pizzeria', 'restaurant', 'Corso Buenos Aires 12', 'Milan', 'Italy', 45.4786, 9.2072, false, 'Pizza restaurant with GF options', 4, 4.2, 89),
('Be Bop Ristorante', 'restaurant', 'Via Tortona 28', 'Milan', 'Italy', 45.4519, 9.1628, false, 'Modern Italian cuisine', 2, 4.0, 34);

-- Insert Ratings
INSERT INTO ratings (user_id, place_id, rating, review) VALUES
(2, 1, 5, 'Excellent food and service!'),
(3, 1, 4, 'Great GF options'),
(4, 2, 5, 'Best GF bakery in Milan'),
(2, 2, 5, 'Amazing bread!'),
(5, 3, 4, 'Good pizza, friendly staff');

-- Insert Favorites (many-to-many)
INSERT INTO favorites (user_id, favorite_type, recipe_id) VALUES
(2, 'recipe', 1),
(2, 'recipe', 3),
(3, 'recipe', 1),
(3, 'recipe', 2),
(4, 'recipe', 4),
(5, 'recipe', 1);

INSERT INTO favorites (user_id, favorite_type, product_id) VALUES
(2, 'product', 1),
(3, 'product', 1),
(3, 'product', 3),
(4, 'product', 2);

INSERT INTO favorites (user_id, favorite_type, place_id) VALUES
(2, 'place', 1),
(2, 'place', 2),
(3, 'place', 2),
(4, 'place', 1);

-- ==================== VERIFICATION QUERIES ====================

-- Verify views work
-- SELECT * FROM v_user_activity LIMIT 5;
-- SELECT * FROM v_popular_recipes LIMIT 5;

-- Verify function works
-- SELECT get_user_rank(2);

-- Verify triggers work (counts should auto-update)
-- SELECT id, title, likes_count, comments_count FROM recipes;

-- ==================== END OF SCHEMA ====================