-- Add portal ownership column for notifications
-- Existing rows default to admin and future inserts can explicitly set portal

ALTER TABLE notifications
  ADD COLUMN IF NOT EXISTS portal ENUM('admin','standard') NOT NULL DEFAULT 'admin' AFTER user_id;

UPDATE notifications
SET portal = 'admin'
WHERE portal IS NULL OR portal = '';
