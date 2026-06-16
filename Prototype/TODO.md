# TODO - Car Booking enhancements

## Step 1: Driver CRUD (done/unchanged)
- [x] Driver model CRUD + listAjax exist.
- [x] DriversController AJAX endpoints exist (create/update/delete as soft-disable).
- [x] Calendar UI has Manage drivers modal.
- [x] Booking driver dropdown refresh after driver changes.

## Step 2: Vehicle CRUD + UI (implemented)
- [x] `app/models/CarVehicles.php`: add `listVehicles`, `updateVehicle`, `disableVehicle`.
- [x] `app/controllers/VehiclesController.php`: add `update`, `delete`, `listAllAjax` endpoints.
- [x] `app/views/car_bookings/calendar.php`: modern Manage vehicles modal with edit/disable table.
- [x] Refresh top vehicle filter + booking vehicle select + calendar after vehicle changes.
- [x] Booking vehicle form supports both create and update (based on hidden id).

## Step 3: Calendar all-day date correctness (implemented)
- [x] `app/models/CarBookings.php`: adjust FullCalendar all-day `end` to be exclusive (+1 day) to match DB span.

## Step 4: Validate
- [ ] Open calendar: `index.php?controller=CarBookings&action=calendar` and verify:
  - Create booking works.
  - Manage vehicles: create/edit/disable works.
  - Events show correct day range.
- [ ] If anything fails, check browser console + network responses for failing AJAX routes.

