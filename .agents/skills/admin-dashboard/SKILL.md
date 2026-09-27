---
name: admin-dashboard
description: Build or restyle a modern admin UI dashboard using Tailwind CSS - persistent sidebar, top bar with search and account menu, KPI summary row, chart, and a filterable data table, with loading/empty/error states, dark mode, and accessibility built in. Use this skill whenever the user asks for an admin panel, admin dashboard, back-office, internal tool, backend management UI, CMS dashboard, or analytics dashboard - even if they don't say "admin" or "Tailwind". Also use it when restyling an existing dashboard.
---

# Admin Dashboard (Tailwind CSS)

Build admin UIs with Tailwind utilities — no hand-rolled CSS token systems, no
bespoke component stylesheets, no framework beyond what the project already
has. Admin users are repeat, task-driven users: they scan, filter, compare,
and act. Optimize for density, clarity, and speed, not visual flair.

## Anti-over-engineering rules (read first)

1. **Utilities, not custom CSS.** No `:root { --token }` systems, no
   component `<style>` blocks, no BEM. Everything is Tailwind classes plus at
   most a small `tailwind.config` extension for semantic colors.
2. **No new JS libraries.** If the project has none, plain HTML plus **one
   small vanilla-JS block** is the ceiling — no React/Vue/Alpine/Chart.js/lodash
   to render a table and a line chart. An inline SVG polyline is a complete
   chart; native `<dialog>` is a complete modal. The template's script is the
   upper bound of acceptable complexity.
3. **Don't spec what the user didn't ask for.** Density toggles, column
   visibility menus, saved views, i18n, collapsible icon rails — skip them
   unless requested. Build the layout, the states, and the table well.
4. **Copy the template, then delete.** Start from
   `assets/dashboard-template.html`, rename the subject's entities, delete
   any section the user didn't ask for. Rewriting from scratch is a last
   resort.

## Workflow

1. **Identify the subject.** What is being administered (orders, users,
   content)? Who is the admin, and what are their top 3 tasks? If the user
   hasn't said, propose an answer in one line and proceed; do not stall.
2. **Detect the stack.**
   - Project already uses Tailwind → use its existing setup
     (`tailwind.config`, build pipeline, component framework if any). Map the
     utilities in this skill 1:1 — they are identical in React/Vue/Livewire.
   - No project / standalone → deliver one self-contained HTML file using the
     Tailwind Play CDN (`https://cdn.tailwindcss.com`) with an inline
     `tailwind.config`, and one small vanilla-JS script for interactions. Say
     clearly that the CDN is for prototypes and the file should be migrated to
     a build step for production.
   - Project has no Tailwind and isn't web-based → say so and ask before
     introducing it.
3. **Set up theming** (see below), then map the subject's real entities onto
   the layout. Replace all placeholder copy with subject-specific content.
4. **Read `references/components.md`** before writing the table, filters,
   forms, dialogs, or charts — it has the canonical Tailwind snippet for each.
5. **Self-check** against the checklist at the end.

## Theming (the Tailwind way)

Semantic colors live in `tailwind.config`, mapped to CSS variables so dark
mode works with the `class` strategy:

```js
tailwind.config = {
  darkMode: 'class',
  theme: { extend: {
    colors: {
      surface: 'rgb(var(--surface) / <alpha-value>)',
      raised:  'rgb(var(--raised) / <alpha-value>)',
      accent:  { DEFAULT: 'rgb(var(--accent) / <alpha-value>)', fg: 'rgb(var(--accent-fg) / <alpha-value>)' },
    },
    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
  }},
}
```

Define the variables once per theme in a *few* lines of CSS — this is the
only CSS you write:

```css
:root  { --surface: 255 255 255; --raised: 241 245 249; --accent: 79 70 229; --accent-fg: 255 255 255; }
.dark  { --surface: 23 23 28;    --raised: 39 39 46;    --accent: 129 140 248; --accent-fg: 30 27 45; }
```

Conventions:

- **Semantic status colors are stock Tailwind palettes**, not custom hexes:
  success = `emerald`, warning = `amber`, danger = `red`, info = `sky`,
  neutral = `slate`. Same word = same color everywhere. Don't reuse the
  accent as a status color.
- **Borders over shadows.** Panels are `border border-slate-200 dark:border-slate-700/60 rounded-xl`.
  Reserve `shadow-lg` for overlays (dialogs, menus).
- **Numeric alignment:** `tabular-nums` on every KPI value, money column, and
  table footer. Type scale: `text-xs` / `text-sm` (body) / `text-base` /
  `text-xl` / `text-2xl`. Sentence case everywhere.
- **Spacing:** the default 4px scale. Table rows: `h-11` (compact) or `h-13`.

## Required layout

```
+---------+--------------------------------------------------+
| Sidebar | Top bar: menu | search | theme | account          |
| logo    +--------------------------------------------------+
| nav     | Page header: title, one-line context, primary CTA|
| groups  +--------------------------------------------------+
|         | KPI row (3-4 metrics, each with delta + period)  |
|         +---------------------------+----------------------+
|         | Chart (2/3)               | Needs attention (1/3)|
|         +---------------------------+----------------------+
|         | Data table: search, filters, bulk actions        |
+---------+--------------------------------------------------+
```

- **Sidebar** (`w-64`): logo, nav grouped by task (not by database table),
  active item = `bg-slate-100 dark:bg-white/10 font-medium` +
  `aria-current="page"`. Badge counts only for items needing action. Under
  the `lg` breakpoint: off-canvas drawer opened by the top-bar menu button
  (`translate-x` transition + backdrop + scroll lock), not a new layout.
- **Top bar** (`h-14 sticky top-0`): global search (`/` or Ctrl/Cmd+K
  focuses it), theme toggle, account menu. It's chrome — keep it quiet.
- **Page header:** title, one line of context ("Updated 3 min ago"), at most
  one primary action. Extra actions go in a secondary menu.
- **KPI row:** 3-4 cards. Label (`text-sm text-slate-500`), value
  (`text-2xl font-semibold tabular-nums`), delta vs previous period with an
  arrow or sign (never color alone), period in muted small text.
- **Main area:** one chart (2/3, `lg:col-span-2`) + "needs attention" list
  (1/3) when the admin's job is triage. Passive analytics never displace
  triage content.
- **Data table:** the workhorse. Full spec in `references/components.md`.

## Required states (every data region)

- **Loading:** skeleton matching the final layout — `animate-pulse` blocks,
  no layout shift, no spinners-in-place.
- **Empty:** say what's missing and offer the fix ("No orders yet. Create
  your first order"). Empty-from-filters is its own message with a "Clear
  filters" action.
- **Error:** what failed + a Retry button. Never "Something went wrong".
- **Stale:** "Updated 3 min ago" + refresh control where data goes stale.

## Interaction rules

- Destructive actions confirm in a `<dialog>` that names the item and
  consequence; buttons name the action ("Cancel order", not "OK"); focus
  starts on the safe button; Esc closes. Offer Undo via toast when
  reversible.
- Filters render as removable chips and persist in the query string so views
  are shareable and survive refresh.
- Table: sortable headers (`aria-sort`), sticky header, pagination footer
  ("Showing 1–25 of 312"), bulk-select with a contextual action bar, row
  click opens detail, kebab menu for row actions. `truncate` + `title` for
  long text — never wrap rows to uneven heights.
- Forms: labels above inputs, inline validation on blur, errors adjacent to
  the field, primary action bottom-right, dirty-state warning on navigation.
- Toasts: bottom-right, `role="status"` (errors `role="alert"`),
  auto-dismiss ~5s (errors persist).

## Accessibility floor (non-negotiable, and cheap in Tailwind)

- Semantic landmarks: `<nav>`, `<header>`, `<main>`, skip-to-content link.
- Real `<table>` with `<th scope="col">`, real `<button>`s.
- Visible focus: `focus-visible:ring-2 ring-offset-1` on every interactive
  element; full keyboard operation including dialogs (native `<dialog>` gives
  focus trap + Esc for free).
- Contrast 4.5:1 for text in both themes (check `slate-400` on dark
  backgrounds — use `slate-500` there instead).
- `motion-reduce:transition-none motion-reduce:animate-none` on animated
  elements; honor `prefers-color-scheme` as the initial theme, persist the
  user's toggle in `localStorage`, and apply the class before first paint.
- Charts include a visually-hidden data table or "View as table" toggle, and
  never encode meaning by color alone.

## Content

Realistic, subject-specific sample data (names, amounts, dates that make
sense together). Never "Lorem ipsum" or "User 123". If a real API/schema
exists, wire it and keep sample data clearly separated for removal.

## Final checklist

- [ ] Sidebar, top bar, page header, KPI row, chart, table — mapped to the
      real subject, no placeholder copy
- [ ] Loading / empty / empty-from-filters / error / stale states exist
- [ ] Light + dark themes both legible; only the few theming CSS vars exist,
      everything else is utilities
- [ ] No new JS dependencies beyond the project's existing ones
- [ ] Keyboard walkthrough works; focus visible everywhere; `<dialog>` used
      for confirms
- [ ] Narrow-screen: drawer nav, KPIs wrap to 2 then 1 column, table scrolls
      horizontally in its own container
- [ ] Numbers tabular, dates/currency formatted consistently
- [ ] Status pills: text + dot, same status = same color everywhere

## Bundled files

- `references/components.md` — canonical Tailwind snippets for table,
  filters, forms, dialogs/toasts, charts, status pills, and navigation. Read
  before implementing those parts.
- `assets/dashboard-template.html` — working single-file starting point
  (Tailwind CDN + vanilla JS: sidebar drawer, top bar, KPIs, chart, table with
  sort/selection/bulk, all four states, dark mode). Copy, adapt, replace the
  sample "orders" content with the user's subject.
