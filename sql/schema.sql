-
-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student', 'faculty', 'technician') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- COMPUTERS TABLE
CREATE TABLE IF NOT EXISTS computers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(50) NOT NULL,
    status ENUM('available', 'in_use', 'maintenance') DEFAULT 'available',
    health_score INT DEFAULT 100  -- Range: 0 (bad) to 100 (perfect)
);

-- BOOKINGS TABLE
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    computer_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (computer_id) REFERENCES computers(id) ON DELETE CASCADE
);

-- USAGE LOGS TABLE
CREATE TABLE IF NOT EXISTS usage_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    computer_id INT NOT NULL,
    user_id INT NOT NULL,
    usage_start DATETIME NOT NULL,
    usage_end DATETIME NOT NULL,
    duration_minutes INT,
    FOREIGN KEY (computer_id) REFERENCES computers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
- SQL for creating tables