CREATE TABLE IF NOT EXISTS rate_limit_states (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    scope VARCHAR(64) NOT NULL,
    identifier VARCHAR(255) NOT NULL,
    failure_count INT NOT NULL DEFAULT 0,
    last_failure_at DATETIME NULL,
    locked_until DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_rate_limit_states_scope_identifier (scope, identifier),
    INDEX idx_rate_limit_states_locked_until (locked_until),
    INDEX idx_rate_limit_states_last_failure_at (last_failure_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
