# Component specs

Contents: Data table, Filters, Forms, Dialogs and toasts, Charts, Status pills, Navigation

## Data table

- Toolbar above the table: search input (left), filter chips and "Add filter"
  (middle), density toggle and column visibility (right).
- Columns: identifier or name first (left-aligned, medium weight), then
  attributes, then status, then numeric columns (right-aligned, tabular
  numerals), then a trailing actions column.
- Header: sticky, sortable columns show an arrow and expose
  `aria-sort="ascending|descending|none"`.
- Selection: checkbox column; selecting rows replaces the toolbar with a bar
  showing "N selected" plus bulk actions and a "Clear" control.
- Row: hover background, click opens detail, actions in a kebab menu with a
  visible label for assistive tech ("Actions for order #1042").
- Footer: "Showing 1-25 of 312", page-size select, previous/next. Use
  cursor pagination for very large sets.
- Long text: single-line ellipsis with `title` attribute; never wrap rows to
  uneven heights.
- Empty result under active filters: "No orders match these filters" with a
  "Clear filters" button.

## Filters

- Chips show `field: value` and are removable with a close button labelled
  "Remove filter: status is Paid".
- Date range presets (Today, 7 days, 30 days, Custom) beside the table and
  the KPI row, and the selected range applies consistently to both.
- Persist filters and sort in the query string.

## Forms

- Single column for create/edit, max width ~640px. Group related fields under
  a heading, with help text under the label, not in placeholders.
- Required fields are marked; validation runs on blur and again on submit.
  On submit failure, focus moves to the first invalid field and an error
  summary appears at the top.
- Buttons: primary "Save changes" bottom-right, secondary "Cancel" beside it.
  Disable the primary button only while saving, and show progress.
- Long settings pages use a left sub-nav or anchor list, and each section
  saves independently.

## Dialogs and toasts

- Dialog: title states the action; body states the consequence; buttons name
  the action ("Delete user", not "OK"). Focus goes to the least destructive
  button; Esc closes; focus returns to the trigger.
- Toast: bottom-right, auto-dismiss after ~5s (persist errors), includes Undo
  when possible, uses `role="status"` (or `alert` for errors).

## Charts

- Prefer line for trends, bar for comparisons, and avoid pie/donut beyond
  3 segments. Start bar axes at zero.
- Use the categorical palette from tokens; keep to 5 series max. Add direct
  labels or a legend, and vary line style or markers so meaning is not color
  only.
- Tooltip on hover and keyboard focus; include a "View as table" toggle or
  visually hidden data table.
- Show the period and units in the chart title ("Revenue, last 30 days
  (USD)").
- Skeleton while loading; a plain message when there is no data.

## Status pills

Text label plus dot. Map states to semantic tokens: success (Paid, Active),
warning (Pending, Expiring), danger (Failed, Suspended), info (Draft,
Processing), neutral (Archived). Same word means the same color across the
whole product.

## Navigation

- Group labels are sentence case and optional; 5-8 top-level items maximum.
- Show counts or badges only for items requiring action (e.g. "Refunds 4").
- Breadcrumbs on detail pages; the page title repeats the last crumb.
- Collapsed sidebar preserves the active state and shows tooltips on
  hover/focus.