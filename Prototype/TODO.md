# TODO

## Car Calendar: friendlier error messages + toast
- [ ] Update `app/controllers/CarBookingsController.php` to return `error_code` in JSON for booking create/update/get failures.
- [ ] Update `app/views/car_bookings/calendar.php` to replace `alert()` with `showNotification()` for booking save errors.
- [ ] Add client-side mapping from `error_code` (vehicle_conflict / driver_conflict / past_departure / invalid / unknown) into friendly text.
- [ ] Ensure calendar drag/resize conflict messaging uses toast and keeps server message when available.
- [ ] Sanity test create booking with vehicle conflict, driver conflict, and past departure.

