CREATE TABLE user_sessions(
    -- session id to be used in dashboard and references
    session_id INT AUTO_INCREMENT PRIMARY KEY,

    -- user id references
    user_id INT NOT NULL,

    -- session attrbute
    session_start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    session_end DATETIME DEFAULT NULL,
    session_duration INT DEFAULT NULL,

    -- constraint and foreign key implemnetation
    CONSTRAINT fk_user_sesions_user_id
    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)