Act as a senior UI/UX engineer specializing in Tailwind CSS.

Refactor ONLY the sizing, spacing, typography, and responsive density of this module. Do NOT redesign the UI, modify colors, alter layouts, rename components, remove elements, or change the existing user experience. Preserve all existing HTML structure, routes, controllers, PHP logic, JavaScript behavior, permissions, and business logic.

Objective

Optimize this module for an enterprise desktop application. The primary target is office users on 13-inch laptops (1366px+) and larger monitors. Reduce excessive whitespace, improve information density, and make better use of available screen space while preserving the existing visual design.

Tailwind CSS Priority

- Prefer Tailwind CSS utility classes.
- Only use custom CSS when Tailwind cannot accomplish the requirement.
- Avoid fixed pixel dimensions whenever responsive Tailwind utilities are available.
- Keep the implementation clean, reusable, and maintainable.

Desktop-First Responsive Strategy

Treat desktop as the primary experience.

Primary target:

- lg (1024px+) = Compact enterprise desktop
- xl (1280px+) = Comfortable desktop
- 2xl (1536px+) = Spacious desktop

Do not enlarge components simply because more screen space is available.

Instead:

- Maintain comfortable reading widths.
- Improve information density.
- Prevent excessive stretching.
- Prefer max-width containers where appropriate.

Typography

Use only these sizes when appropriate:

- text-xs
- text-sm
- text-base
- text-lg

Use only these font weights:

- font-normal
- font-medium
- font-semibold

Avoid oversized headings unless they already exist.

Spacing

Reduce unnecessary whitespace throughout the module.

Preferred spacing patterns:

- p-3 lg:p-4 xl:p-5
- px-3 lg:px-4 xl:px-5
- py-2 lg:py-3
- gap-3 lg:gap-4
- space-y-3 lg:space-y-4
- mb-3 lg:mb-4

Reduce:

- large paddings
- large margins
- oversized gaps
- unnecessary empty space

Cards

Reduce card density without changing their appearance.

Keep:

- background colors
- borders
- rounded corners
- shadows

Use compact responsive padding.

Buttons

Prefer:

- h-9
- h-10

Reduce unnecessary horizontal padding while preserving accessibility.

Inputs

Prefer:

- h-9
- h-10

Keep labels readable and aligned.

Icons

Do not resize icons unnecessarily.

Keep icon alignment and spacing consistent.

Grids

Improve responsiveness without changing the visual hierarchy.

Prefer responsive grids such as:

- grid-cols-1
- md:grid-cols-2
- lg:grid-cols-3
- xl:grid-cols-4

Avoid unnecessary fixed widths.

Containers

Avoid components stretching across the full monitor width.

Use responsive max-width containers where appropriate.

Keep content readable on:

- 1366px
- 1440px
- 1600px
- 1920px

Reduce unnecessary scrolling caused by oversized spacing.

Maintain the existing layout hierarchy.

Do NOT modify

- Colors
- Theme
- Borders
- Shadows
- Icons
- Animations
- Hover behavior
- Active states
- Component hierarchy
- UI flow
- UX flow
- Functionality
- JavaScript
- PHP
- Controllers
- Models
- Routes
- Business logic

Goal

Only improve responsive density and sizing. The finished module should display more information within the viewport, reduce unnecessary scrolling, and feel like a modern enterprise application optimized for office desktop environments while preserving the existing UI and functionality.















<!-- ================================================================================================ -->


Prompt

Act as a senior UI/UX engineer specializing in Tailwind CSS and enterprise desktop applications.

Refactor ONLY the sizing, spacing, typography, component dimensions, and responsive density of this module.

Do NOT redesign the interface.

Preserve the existing:

HTML structure
PHP
Controllers
Models
Routes
JavaScript
AJAX
Permissions
Business logic
Component hierarchy
User flow
Visual design
Colors
Borders
Shadows
Icons
Animations
Hover states
Active states

The objective is not to change the UI, but to make it significantly more compact, readable, and efficient for office users.

Primary Design Target

Optimize primarily for:

Windows laptops using 150% Display Scaling
13-inch enterprise laptops (1366×768 physical resolution)
14-inch laptops
15.6-inch laptops
Desktop monitors

The UI should comfortably fit on screens where the effective workspace is much smaller because Windows scaling is set to 150%.

The interface should remain usable on larger monitors without becoming oversized or overly spacious.

Responsive Philosophy

Desktop-first.

Prioritize these breakpoints:

lg
xl
2xl

Do not increase spacing simply because the monitor is larger.

Instead:

maximize visible information
reduce scrolling
improve scanability
maintain comfortable reading widths
prevent components from stretching excessively

Large monitors should show more content, not larger content.

Tailwind CSS Priority

Use Tailwind utilities whenever possible.

Avoid custom CSS unless absolutely necessary.

Avoid fixed pixel values.

Prefer responsive utility classes.

Global Density

Reduce the overall scale of the interface by approximately 10–20% while maintaining readability.

Everything should feel slightly denser:

cards
forms
tables
filters
headers
toolbars
panels
badges
navigation
buttons
spacing
typography

The goal is to display more useful information within the viewport.

Typography

Use only:

text-xs
text-sm
text-base
text-lg

Use only:

font-normal
font-medium
font-semibold

Prefer:

text-xs for helper text, metadata, timestamps, badges, table metadata
text-sm for labels, buttons, inputs, navigation, filters, table cells
text-base for card titles and section headings
text-lg only for existing major page titles

Do not introduce oversized headings.

Reduce excessive line heights where appropriate.

Spacing

Reduce all unnecessary whitespace.

Prefer compact spacing:

p-2 lg:p-3 xl:p-4
px-3 lg:px-4
py-2
gap-2 lg:gap-3
space-y-2 lg:space-y-3
mb-2 lg:mb-3
mt-2 lg:mt-3

Reduce:

excessive margins
oversized gaps
large paddings
unnecessary empty space
oversized section spacing

Content should appear more compact while remaining readable.

Cards

Keep:

background
borders
shadows
rounded corners

Reduce only:

padding
internal spacing
header spacing
footer spacing

Preferred padding:

p-3
lg:p-4
Buttons

Prefer compact enterprise buttons.

Recommended:

h-9
lg:h-10

px-3
lg:px-4

text-sm

Avoid oversized buttons.

Keep touch targets accessible.

Inputs

Use consistent compact sizing.

Preferred:

h-9
lg:h-10

px-3

text-sm

Labels should align neatly with inputs.

Reduce excessive spacing between labels and fields.

Tables

Increase visible rows within the viewport.

Reduce:

row height
cell padding
header padding

Keep readability high.

Do not reduce below comfortable reading size.

Grids

Use responsive Tailwind grids.

Prefer:

grid-cols-1
md:grid-cols-2
lg:grid-cols-3
xl:grid-cols-4

Avoid fixed widths whenever possible.

Containers

Avoid layouts stretching across ultra-wide monitors.

Prefer:

max-w-screen-2xl
max-w-7xl
centered containers where appropriate

Maintain comfortable reading widths.

Icons

Do not enlarge icons.

Maintain consistent spacing.

Align icons cleanly with adjacent text.

Information Density

The interface should display more useful information above the fold.

Reduce scrolling by:

tightening spacing
reducing component heights
minimizing oversized whitespace
improving vertical density

Do not remove any information.

Responsiveness

The module must remain responsive across:

1366×768 @ 150% Windows scaling
1440×900
1600×900
1920×1080
2560×1440

Larger monitors should show more content, not larger UI elements.

Strict Constraints

Do NOT:

redesign layouts
reorder components
rename components
change interactions
change JavaScript
modify PHP
alter routes
modify controllers
change models
alter permissions
remove elements
change animations
modify colors
change shadows
alter borders
modify hover behavior
change business logic
Expected Result

Produce a refined version of the module that feels like a dense, modern enterprise application optimized for office environments. The interface should comfortably accommodate users running Windows at 150% display scaling, fit more content within the viewport, reduce unnecessary scrolling, and maintain full responsiveness across laptops and desktop monitors using Tailwind CSS while preserving the existing functionality and visual design.