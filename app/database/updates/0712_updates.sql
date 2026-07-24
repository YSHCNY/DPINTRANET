-- Production-safe notification architecture migration
-- Adds module-based notification columns for scalability while preserving existing data

ALTER TABLE notifications
  ADD COLUMN IF NOT EXISTS module VARCHAR(50) NOT NULL DEFAULT '' AFTER user_id,
  ADD COLUMN IF NOT EXISTS event_key VARCHAR(100) NOT NULL DEFAULT '' AFTER module,
  ADD COLUMN IF NOT EXISTS entity_id INT NULL AFTER event_key,
  ADD COLUMN IF NOT EXISTS title VARCHAR(255) NOT NULL DEFAULT '' AFTER url,
  ADD COLUMN IF NOT EXISTS priority ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal' AFTER title,
  ADD COLUMN IF NOT EXISTS icon VARCHAR(50) NULL AFTER priority,
  ADD COLUMN IF NOT EXISTS created_by INT NULL AFTER icon;

ALTER TABLE notifications
  ADD INDEX idx_notifications_user_read (user_id, is_read),
  ADD INDEX idx_notifications_module (module),
  ADD INDEX idx_notifications_event_key (event_key),
  ADD INDEX idx_notifications_entity_id (entity_id),
  ADD INDEX idx_notifications_created_at (created_at);
