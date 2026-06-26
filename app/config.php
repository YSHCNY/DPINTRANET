<?php
// Application config: force BOOKING_STORE to 'db' so module uses database only.
if (!defined('BOOKING_STORE')) {
    define('BOOKING_STORE', 'db');
}

// Note: environment overrides are intentionally disabled to ensure
// bookings are always persisted to the database for production/deadline runs.
