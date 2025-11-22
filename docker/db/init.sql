-- GlutenFree+ Database Initialization
-- Updated structure with user_type

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Create roles enum type
CREATE TYPE user_role AS ENUM ('user', 'moderator', 'admin');

-- Create user types for community
CREATE TYPE community_user_type AS ENUM ('Celiac', 'Nutritionist', 'Food Blogger', 'Chef');

-- Users table
DROP TABLE IF EXISTS users CASCADE;
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    role user_role DEFAULT 'user',
    avatar VARCHAR(500),
    bio TEXT,
    user_type community_user_type,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    is_active BOOLEAN DEFAULT true
);

-- Create indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_user_type ON users(user_type);
CREATE INDEX idx_users_is_active ON users(is_active);

-- Insert sample users (password: admin123)
INSERT INTO users (email, password, name, role, user_type, avatar) VALUES 
('admin@glutenfree.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin', NULL, NULL),
('victoria1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Victoria Smith', 'user', 'Celiac', '/public/images/avatars/victoria.png'),
('tommy.s@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tommy S.', 'user', 'Celiac', '/public/images/avatars/tommy.png'),
('marco.p@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marco P.', 'user', 'Nutritionist', '/public/images/avatars/marco.png'),
('tobby.f@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tobby F.', 'user', 'Nutritionist', '/public/images/avatars/tobby.png'),
('pablo.e@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pablo E.', 'user', 'Celiac', '/public/images/avatars/pablo.png'),
('sarah.m@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah M.', 'user', 'Chef', '/public/images/avatars/sarah.png'),
('alice.t@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice T.', 'user', 'Food Blogger', '/public/images/avatars/alice.png'),
('mary.n@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mary N.', 'user', 'Celiac', '/public/images/avatars/mary.png'),
('sofie.z@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sofie Z.', 'user', 'Nutritionist', '/public/images/avatars/sofie.png');

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Trigger to automatically update updated_at
CREATE TRIGGER update_users_updated_at 
    BEFORE UPDATE ON users 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();