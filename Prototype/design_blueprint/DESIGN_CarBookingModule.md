# Car Booking Module — Design Specification

## Purpose & Workflow

- Purpose: Provide an efficient, low-friction interface for booking vehicles, managing bookings, and coordinating drivers and vehicles for the organisation's internal transport needs.
- Primary users: Office staff, dispatchers, standard users booking vehicles, and administrators managing fleet and approvals.
- Key workflows:
  - Quick booking (one-off, single trip)
  - Scheduled booking (future dates/times, recurring)
  - Approvals and assignments (manager approval, driver assignment)
  - Booking management (view, edit, cancel, reschedule)
  - Calendar & list views for availability and conflicts

## Design Principles

- Follow existing product visual language and components.
- Prioritize clarity, reduce cognitive load, and minimize clicks.
- Provide clear feedback and well-defined states (loading, empty, errors, validation, success).
- Reuse existing spacing, typographic scale, colors, buttons, and form controls.

## Layout & Responsive Behavior

- Desktop (>=1024px): Primary two-column layout with list/calendar on the left and details/side panel on the right. Toolbar with actions at top of list.
- Tablet (768–1023px): Single column with collapsible detail panel; toolbar condensed into icon buttons.
- Mobile (<768px): Single-column stack; list or calendar view toggled via segmented control; details open full-screen modal or bottom sheet.

## Entry Points

- `Car Bookings` in main navigation (existing) opens module landing page.
- Landing page shows segmented control: `List | Calendar | New Booking`.
- Quick-add primary CTA `New Booking` (prominent brand primary color, filled button) in top right of toolbar.

## Visual Patterns & Components

- Cards: Use existing card elevation, rounded corners, and padding tokens.
- Tables: Use existing table styles—striped rows, hover highlight, compact row height. Include column for status chips.
- Buttons: Follow existing hierarchy - Primary (filled), Secondary (outline), Tertiary (text). Use icons only for space-constrained toolbars.
- Forms: Use existing labeled inputs, validation color tokens, helper text under inputs, and grouped fieldsets for date/time and passengers.
- Icons: Use existing icon set (svgs). Keep sizes consistent with sidebar and toolbar icons.

## Key Screens & Elements

1. Landing / Overview
   - Top toolbar: breadcrumb, page title, segmented `List | Calendar`, search input, filter dropdown, `New Booking` CTA.
   - Body: Left column list or calendar. Right column shows selected booking details in a card with actions: `Edit`, `Assign Driver`, `Cancel`, `Print`.
   - Empty state: friendly illustration, short instructions, `New Booking` CTA.

2. New Booking Flow (modal / slide-over)
   - Stepper header: `1. Details -> 2. Passengers -> 3. Confirm` for longer flows (optional). For quick booking, use an inline form.
   - Fields: Purpose (text), Passenger(s) (typeahead select), Pickup location, Drop-off location, Date & Time (start & end), Vehicle preference, Additional notes, Attach files.
   - Accessibility: label associations, keyboard-first navigation, aria-live for validation.
   - Validation: inline validation on blur and summary banner on submit errors.
   - Confirmation: show summary card with estimated runtime and approver if required.

3. Booking Details
   - Header: status chip (Approved, Pending, Cancelled), booking reference, copy-to-clipboard button.
   - Info grid: date/time, passengers, vehicle, driver assignment, created by, notes.
   - Actions: prominent `Edit` and `Cancel`, secondary `Reassign Driver`, `Duplicate`, `Download`.

4. Calendar View
   - Week / Month toggles. Events display with driver initials and vehicle reg.
   - Collision detection: color-coded border for conflicts; hover shows quick preview tooltip.
   - Mobile uses condensed event chips and opens details on tap.

5. Assignment & Approval
   - Assign Driver modal uses prioritized list—available drivers first with ETA and current status.
   - Approvals handled via status change; add comment box for approvers with required fields.

## Component Library & Tokens Reference

- Use existing tokens from app CSS: spacings, colors, fonts, radii, elevations.
- Status colors: use existing semantic tokens for `success`, `warning`, `danger`, `info`.
- Form field states: `default`, `focus`, `invalid`, `disabled` follow system tokens.

## Interaction & Microcopy

- Use concise action labels: `New Booking`, `Assign`, `Cancel`, `Reschedule`.
- Provide tooltips for less common actions (e.g., `Duplicate booking`).
- Use confirmations for destructive actions (Cancel) with clear consequences.
- Show in-context help for fields like `Vehicle preference` with optional tooltip explaining availability.

## States

- Loading: skeletons for lists and cards, spinner inside primary CTA for saves.
- Empty: explanatory message + CTA.
- Success: transient toast at top-right with short message and undo where applicable.
- Error: inline message and top banner for blocking errors.

## Accessibility

- High contrast between text and background following AA contrast ratios.
- Keyboard navigable: all interactive elements reachable and operable via keyboard.
- Semantic HTML (tables, lists, headings) and ARIA for dynamic components.

## Performance & Implementation Notes

- Lazy-load calendar events; paginate list view.
- Use client-side validation before server round-trips.
- Prefer progressive enhancement: full functionality without JS where possible.

## Reusable Components

- BookingCard: compact summary used in list and calendar tooltip.
- BookingDetailsPanel: used in right column and full-screen detail views.
- BookingForm: multi-purpose for create/edit with prop to control quick vs advanced mode.
- DriverListItem: shows availability, distance, ETA.

## File & API Considerations

- Component data shapes should map directly to existing backend model `CarBookings`.
- Use optimistic UI updates for assignments with clear rollback on error.

## Migration & Rollout

- Start with list + details view behind feature flag.
- Collect metrics: booking creation time, cancellations, assignment time.

## Implementation Checklist

- [ ] Implement `BookingCard` and `BookingForm` components.
- [ ] Build landing page with segmented control and toolbar.
- [ ] Implement calendar with lazy loading and conflict detection.
- [ ] Create assignment modal and approval flow UI.
- [ ] Accessibility review and keyboard testing.


---

Design notes: This spec intentionally reuses the product's established UI tokens and patterns; it's a blueprint for front-end engineers to implement consistent components that integrate with the existing app.
