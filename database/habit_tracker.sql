-- =============================================
-- CORRECT DATABASE SCHEMA FOR habittracker2
-- =============================================

-- Use the database
USE track_habit;

-- =============================================
-- TABLE: users (with user_id as primary key)
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: habits
-- =============================================
CREATE TABLE IF NOT EXISTS habits (
    habit_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    habit_name VARCHAR(100) NOT NULL,
    description TEXT,
    frequency ENUM('Daily','Weekly','Monthly') DEFAULT 'Daily',
    reminder_enabled BOOLEAN DEFAULT TRUE,
    reminder_time TIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_habit_name (habit_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: tracking
-- =============================================
CREATE TABLE IF NOT EXISTS tracking (
    tracking_id INT AUTO_INCREMENT PRIMARY KEY,
    habit_id INT NOT NULL,
    completed_date DATE NOT NULL,
    status ENUM('Completed','Missed') DEFAULT 'Completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (habit_id) REFERENCES habits(habit_id) ON DELETE CASCADE,
    UNIQUE KEY unique_habit_date (habit_id, completed_date),
    INDEX idx_habit_id (habit_id),
    INDEX idx_completed_date (completed_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- -- =============================================
-- -- INSERT SAMPLE DATA
-- -- =============================================

-- -- Create demo users
-- INSERT IGNORE INTO users (username, email, password) VALUES 
-- ('demo', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- ('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- ('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- -- Get the demo user ID
-- SET @user_id = (SELECT user_id FROM users WHERE username = 'demo' LIMIT 1);

-- -- Insert sample habits for demo user
-- INSERT IGNORE INTO habits (user_id, habit_name, description, frequency, reminder_enabled, reminder_time) VALUES
-- (@user_id, 'Read 20 Pages', 'Read at least 20 pages from a book', 'Daily', 1, '20:00:00'),
-- (@user_id, 'Drink Water', 'Drink 8 glasses of water throughout the day', 'Daily', 1, '09:00:00'),
-- (@user_id, 'Exercise', '30 minutes of physical exercise', 'Daily', 0, NULL),
-- (@user_id, 'Meditate', '10 minutes of mindfulness meditation', 'Daily', 1, '07:00:00'),
-- (@user_id, 'Write Journal', 'Write in journal for 15 minutes', 'Daily', 1, '21:30:00'),
-- (@user_id, 'Learn Coding', 'Practice coding for 1 hour', 'Daily', 1, '19:00:00');

-- Insert sample tracking data
-- INSERT IGNORE INTO tracking (habit_id, completed_date, status, notes) 
-- SELECT 
--     h.habit_id,
--     DATE_SUB(CURDATE(), INTERVAL n DAY) as completed_date,
--     CASE 
--         WHEN RAND() > 0.3 THEN 'Completed' 
--         ELSE 'Missed' 
--     END as status,
--     CASE 
--         WHEN RAND() > 0.85 THEN 'Great progress today!' 
--         WHEN RAND() > 0.7 THEN 'Could have done better' 
--         ELSE NULL 
--     END as notes
-- FROM habits h
-- CROSS JOIN (
--     SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
--     UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
--     UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
--     UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
--     UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
--     UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
-- ) dates
-- WHERE h.user_id = @user_id
-- AND DATE_SUB(CURDATE(), INTERVAL n DAY) <= CURDATE()
-- ON DUPLICATE KEY UPDATE 
--     status = VALUES(status),
--     notes = VALUES(notes);

-- Today's tracking
INSERT IGNORE INTO tracking (habit_id, completed_date, status) 
SELECT 
    habit_id,
    CURDATE(),
    CASE 
        WHEN RAND() > 0.4 THEN 'Completed' 
        ELSE 'Missed' 
    END
FROM habits
WHERE user_id = @user_id
ON DUPLICATE KEY UPDATE status = VALUES(status);

SELECT '✅ Database setup completed successfully!' as 'Status';



