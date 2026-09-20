---
name: admin-dashboard
description: Build or restyle an admin UI dashboard using a proven, recommended layout - persistent sidebar, top bar with search and account menu, KPI summary row, charts, and a filterable data table with row actions, plus loading/empty/error states, dark mode, and accessibility built in. Use this skill whenever the user asks for an admin panel, admin dashboard, back-office, internal tool, backend management UI, CMS dashboard, analytics dashboard, or "apply the recommended dashboard layout" - even if they don't say "admin". Also use it when restyling an existing dashboard to a cleaner, standard structure.
---

# Admin Dashboard

Apply a consistent, recommended structure for admin UIs. Admin users are
repeat, task-driven users: they scan, filter, compare, and act. Optimize for
density, clarity, and speed, not for first-impression flair.

## Workflow

1. **Identify the subject.** What is being administered (orders, users,
   content, infrastructure)? Who is the admin, and what are their top 3
   tasks? If the user hasn't said, propose an answer in one line and proceed;
   do not stall on questions.
2. **Detect the stack.** If the user has an existing project, match its
   framework and component library (React + Tailwind, shadcn/ui, MUI, Vue,
   plain HTML). Do not introduce a second UI library. If nothing exists,
   default to a single self-contained HTML file (see `assets/dashboard-template.html`),
   or React + Tailwind if the user asks for React.
3. **Plan the tokens** (see "Design tokens"), then map the subject's real
   entities onto the layout below. Replace all placeholder copy with
   subject-specific content.
4. **Build** using the layout, components, and states below. Read
   `references/components.md` for detailed specs of the table, filters,
   forms, and charts before writing them.
5. **Self-check** against the checklist at the end, and fix failures before
   delivering.

## Recommended layout

```
+---------+--------------------------------------------------+
| Sidebar | Top bar: breadcrumb/title | search | help | user  |
|         +--------------------------------------------------+
| logo    | Page header: title, one-line context, primary CTA|
| nav     +--------------------------------------------------+
| groups  | KPI row (3-4 metrics, each with delta + period)  |
|         +---------------------------+----------------------+
| footer  | Primary chart / trend     | Secondary panel      |
| (user,  +---------------------------+----------------------+
| collapse| Data table: search, filters, bulk actions, rows  |
+---------+--------------------------------------------------+
```

- **Sidebar**: 240px expanded, 64px collapsed (icons + tooltips). Group nav
  items by task, not by database table. Highlight the current page with
  `aria-current="page"`. On screens under 900px it becomes an off-canvas
  drawer opened from a menu button.
- **Top bar**: sticky, 56px. Global search (focus with `/` or Ctrl/Cmd+K),
  environment or workspace switcher if relevant, notifications, account
  menu. Keep it quiet; it is chrome, not content.
- **Page header**: page title, a single sentence of context or last-updated
  time, and at most one primary action ("Add user", "Create order"). Extra
  actions go in a secondary menu.
- **KPI row**: 3-4 metrics maximum. Each shows label, value, change versus
  the previous period, and the period itself. Color the delta by whether the
  change is good or bad for this metric, and never rely on color alone (add
  an arrow or +/- sign). Metrics should be things the admin would act on.
- **Main area**: one primary chart (2/3 width) plus one secondary panel
  (1/3), such as recent activity or items needing attention. Put "needs
  attention" content above passive analytics when the admin's job is triage.
- **Data table**: the workhorse. Below the fold is fine.

## Design tokens

Define tokens once as CSS variables (or the framework's theme) and use only
those. Provide light and dark values.

- **Color**: neutral surface scale (background, surface, raised, border),
  text (primary, secondary), one brand/accent color, and semantic colors
  (success, warning, danger, info). Semantic colors are reserved for status;
  do not reuse the accent as a status color.
- **Type**: one family, or two clearly distinct. Use tabular numerals
  (`font-variant-numeric: tabular-nums`) for every number, table column and
  KPI. Scale: 12 / 13 / 14 (body) / 16 / 20 / 28. Sentence case everywhere.
- **Spacing**: 4px base grid. Compact row height 40px, comfortable 52px;
  offer a density toggle if the table is a primary surface.
- **Radius and elevation**: two radii at most (controls vs. panels). Prefer
  1px borders to shadows; reserve shadow for overlays (menus, dialogs).
- **Avoid**: identical decorative gradient cards, a different accent color
  per KPI, all-caps eyebrow labels above every section, and decorative
  motion. Choose an accent that fits the subject rather than defaulting to
  generic blue or a warm-terracotta look.

## Required states

Every data region needs all four states designed, not just the happy path:

- **Loading**: skeleton placeholders matching final layout (no layout shift).
- **Empty**: say what is missing and offer the action that fixes it
  ("No orders yet. Create an order").
- **Error**: state what failed and how to recover, with a Retry button. No
  apologies, no vague "Something went wrong."
- **Partial/stale**: show "Updated 3 min ago" and a refresh control where
  data can go stale.

## Interaction rules

- Destructive actions require a confirmation dialog naming the item and the
  consequence; offer Undo via toast when the action is reversible.
- Use the same verb in the button, the dialog, and the toast ("Delete user"
  -> "Delete user" -> "User deleted").
- Filters are visible as removable chips; the URL (or state) reflects them so
  views are shareable and survive refresh.
- Tables: sortable headers, sticky header, pagination or virtualization,
  bulk-select with a contextual action bar, row actions in a kebab menu, and
  a row click that opens detail. Never truncate without a title/tooltip.
- Forms: labels above inputs, inline validation on blur, errors adjacent to
  the field, primary action bottom-right, dirty-state warning on navigation.
- Status is a pill with text plus a dot or icon, not color alone.

## Accessibility and quality floor

- Semantic landmarks: `<nav>`, `<header>`, `<main>`, and a skip-to-content
  link. Real `<table>` with `<th scope>`; real `<button>` elements.
- Visible focus ring on every interactive element; full keyboard operation
  including menus, dialogs (focus trap, Esc to close) and the table.
- Contrast 4.5:1 for text, 3:1 for UI boundaries, in both themes.
- Respect `prefers-reduced-motion` and `prefers-color-scheme`.
- Responsive: sidebar collapses to a drawer, KPI row wraps to 2 then 1
  columns, tables scroll horizontally inside their own container.
- Charts have a text summary or data table alternative, and do not encode
  meaning by color alone.

## Content

Use realistic, subject-specific sample data (names, amounts, dates that make
sense together). Never ship "Lorem ipsum", "Item 1", or "User 123". If real
data is available (API, CSV, database schema), wire the UI to it and make the
sample data clearly separate so it can be removed.

## Final checklist

- [ ] Sidebar, top bar, page header, KPI row, chart, table all present and
      mapped to the real subject
- [ ] Loading, empty, error, and stale states exist for each data region
- [ ] Light and dark themes both legible; tokens used throughout
- [ ] Keyboard-only walkthrough works; focus is always visible
- [ ] Narrow-screen layout checked (drawer nav, wrapped KPIs, scrolling table)
- [ ] Numbers are tabular, dates and currencies consistently formatted
- [ ] Copy is sentence case, active voice, consistent verbs
- [ ] No second UI library added; existing project conventions followed

## Bundled files

- `references/components.md`: detailed specs for table, filters, forms,
  dialogs, toasts, and charts. Read before implementing those parts.
- `assets/dashboard-template.html`: working single-file starting point
  (sidebar, top bar, KPIs, chart, table, states, dark mode). Copy and adapt
  it; replace the sample "orders" content with the user's subject.