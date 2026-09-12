# Implementation Plan - Use Organizer Create Event Modal in New Front Index

The goal is to replace the current simplified `CreateEventModal` in the new frontend's events index page with the full-featured event creation form used in the organizer dashboard (`pages/organizer/event/Create.vue`).

## Proposed Changes

### [Backend] [EventController.php](file:///D:/atif projects/LinkupFinal/app/Http/Controllers/NewFrontend/EventController.php)

- Update the `events` method to provide additional data required by the full event creation form (`caribbeans`, `scanners`, `allEvents`, etc.) when the authenticated user is an organizer.

### [Frontend] [Create.vue](file:///D:/atif projects/LinkupFinal/resources/js/pages/organizer/event/Create.vue)

- Refactor to support being used as a standalone modal component.
- Add an `asModal` prop.
- Wrap the modal UI in a conditional template so it doesn't render `AppLayout` when used as a modal.
- Expose `open` and `close` methods via `defineExpose`.

### [Frontend] [Index.vue](file:///D:/atif projects/LinkupFinal/resources/js/pages/new_front/events/Index.vue)

- Change the import of `CreateEventModal` to point to `@/pages/organizer/event/Create.vue`.
- Pass the new props (`caribbeans`, `scanners`, `allEvents`, `appURL`) to the `CreateEventModal` component.
- Add `as-modal` prop to the component.

## Verification Plan

### Automated Tests
- No automated tests are planned as this is a UI refactoring task.

### Manual Verification
1.  **Login as an Organizer**.
2.  Navigate to the **New Frontend Events Index** (`/new_frontend/events`).
3.  Click the **"Create Event"** button.
4.  Verify that the full event creation modal (from the organizer dashboard) opens.
5.  Verify that all form fields (Media, Location, Social Media, etc.) are correctly populated and functional.
6.  Try to submit a test event and verify it redirects/behaves correctly.
7.  Verify that the **Organizer Dashboard**'s create event page still works as expected.
