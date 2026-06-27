-- Add file categories for the Official File Repository
-- Run this migration to add Reports, Meeting Minutes, and Others categories

INSERT IGNORE INTO `filescategory` (`id`, `category`, `categoryType`) VALUES
(NULL, 'Reports', 'filetype'),
(NULL, 'Meeting Minutes', 'filetype'),
(NULL, 'Others', 'filetype')
ON DUPLICATE KEY UPDATE `categoryType` = VALUES(`categoryType`);
