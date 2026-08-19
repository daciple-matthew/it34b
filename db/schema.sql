CREATE TABLE IF NOT EXISTS activity_logs(
    activity_log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50),
    user_email VARCHAR(100),
    activity_log_action VARCHAR(100) NOT NULL,
    activity_log_status ENUM('SUCCESS', 'FAILED') DEFAULT 'SUCCESS',

    -- client parameters
    activity_log_ip_address VARCHAR(50),
    actibity_log_user_agent VARCHAR(255),

    -- timestamps
    activity_log_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);
