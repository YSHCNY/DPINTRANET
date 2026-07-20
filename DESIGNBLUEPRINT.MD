Act as a senior UI/UX designer and senior Tailwind CSS frontend engineer.

Refactor the following module/card while preserving all existing functionality, business logic, permissions, IDs, names, routes, PHP/JS behavior, and responsiveness.

Goals:
- Improve UX without changing workflows.
- Follow an 8-point grid system.
- Keep the design clean, modern, premium, and enterprise-ready.
- Reduce cognitive overload through strong visual hierarchy.
- Avoid information duplication.
- Maintain compact layouts using only necessary spacing.
- Improve alignment, consistency, accessibility, and readability.
- Optimize typography, spacing, grouping, and component hierarchy.
- Preserve existing functionality unless explicitly instructed otherwise.

Design Rules:
- bg-white cards only
- subtle borders (border-slate-200)
- minimal shadows
- rounded-lg or rounded-xl only
- text-xs, text-sm, text-base only
- compact spacing (gap-2, gap-3, gap-4, p-3, p-4)
- consistent alignment
- responsive
- no unnecessary colors
- badges use "• Label" where only the dot is colored
- no oversized headings
- no decorative elements unless they improve UX

Output production-ready Tailwind CSS and explain only significant UX improvements.

Project Style
- Inspiration: Linear, GitHub, Notion, Stripe Dashboard, Vercel
- Industry: Enterprise Document Management System
- Users: Internal Government Employees
- Theme: Professional Minimal
- Density: Compact

Typography
- Title: text-base font-semibold
- Subtitle: text-sm
- Labels: text-xs font-medium
- Body: text-sm
- Helper: text-xs text-slate-500

Cards
- bg-white
- rounded-xl
- border-slate-200
- shadow-sm
- p-4
- gap-4

Buttons
- rounded-lg
- h-9
- compact
- subtle primary
- outlined secondary

Badges
- • Label
- only dot colored
- text-xs

Tables
- compact rows
- subtle hover
- actions on right
- avoid clickable rows

Forms
- two-column when possible
- textarea full width
- compact spacing
- minimal helper text

Header
- CTA top-right
- metadata below title
- compact

Modals
- medium width
- compact header/footer
- body scroll only

Spacing
- 8-point grid
- p-4
- gap-4
- space-y-3

Avoid
- giant text
- oversized padding
- loud colors
- duplicate information
- excessive icons
- nested cards
- unnecessary borders