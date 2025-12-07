-- database/schema.sql
CREATE DATABASE IF NOT EXISTS kowsalya_cake_shop;
USE kowsalya_cake_shop;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    weight VARCHAR(20) DEFAULT '1 Kg',
    image VARCHAR(255),
    stock_quantity INT DEFAULT 100,
    rating DECIMAL(2, 1) DEFAULT 5.0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Newsletter subscribers
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample products
INSERT INTO products (name, description, category, price, image) VALUES
('Chocolate Cake', 'Rich and moist chocolate cake', 'Celebration Cakes', 750.00, 'pexels-anna-wlodarczyk-1284606-2451673.jpg'),
('Vanilla Cake', 'Classic vanilla flavored cake', 'Celebration Cakes', 950.00, 'pexels-avonnephoto-3923555.jpg'),
('Carrot Cake', 'Healthy carrot cake with cream cheese frosting', 'Celebration Cakes', 745.00, 'pexels-caleboquendo-3038302.jpg'),
('Lemon Cake', 'Tangy and refreshing lemon cake', 'Celebration Cakes', 650.00, 'pexels-dariaobymaha-1684039.jpg'),
('Strawberry Cake', 'Fresh strawberry cake', 'Celebration Cakes', 545.00, 'pexels-elli-559179-1854652.jpg'),
('Marble Cake', 'Perfect blend of chocolate and vanilla', 'Celebration Cakes', 855.00, 'pexels-fariphotography-934729.jpg'),
('Almond Cake', 'Nutty almond flavored cake', 'Celebration Cakes', 465.00, 'pexels-jennifer-murray-402778-1200666.jpg'),
('Coconut Cake', 'Tropical coconut delight', 'Celebration Cakes', 675.00, 'pexels-jeremy-wong-382920-1038711.jpg'),
('Pumpkin Cake', 'Seasonal pumpkin spice cake', 'Celebration Cakes', 450.00, 'pexels-jessbaileydesign-913136.jpg'),
('German Chocolate Cake', 'Traditional German chocolate', 'Celebration Cakes', 500.00, 'pexels-kpaukshtite-1998632.jpg'),
('Funfetti Cake', 'Colorful celebration cake', 'Celebration Cakes', 750.00, 'pexels-kpaukshtite-1998633.jpg'),
('Black Pecan Cake', 'Rich pecan cake', 'Celebration Cakes', 380.00, 'pexels-marta-dzedyshko-1042863-2067396.jpg'),
('Hazelnut Cake', 'Nutty hazelnut flavor', 'Celebration Cakes', 550.00, 'pexels-pixabay-264892.jpg'),
('Chai Spice Cake', 'Aromatic chai spiced cake', 'Celebration Cakes', 670.00, 'pexels-pixabay-264939.jpg'),
('Pistachio Cake', 'Premium pistachio cake', 'Celebration Cakes', 950.00, 'pexels-polina-tankilevitch-4110006.jpg'),
('Tiramisu Cake', 'Italian coffee flavored cake', 'Celebration Cakes', 740.00, 'pexels-rodrigo-souza-1275988-2531546.jpg'),
('Mocha Cake', 'Coffee and chocolate combination', 'Celebration Cakes', 955.00, 'pexels-rquiros-1727415.jpg'),
('Pineapple Cake', 'Tropical pineapple flavor', 'Celebration Cakes', 750.00, 'pexels-valeriya-1579926.jpg'),
('Lavender Cake', 'Elegant lavender infused cake', 'Celebration Cakes', 855.00, 'pexels-taryn-elliott-4099127.jpg'),
('Apple Cinnamon Cake', 'Warm apple cinnamon cake', 'Celebration Cakes', 765.00, 'pexels-zvolskiy-1721932.jpg');

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@kowsalyacake.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');