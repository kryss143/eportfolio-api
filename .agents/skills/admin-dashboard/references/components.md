# Component specs — Tailwind CSS

Canonical idioms for each part of the dashboard. Snippets are plain HTML;
they map 1:1 to JSX/Vue templates (swap `class` → `className` where needed).
`dark:` variants assume `darkMode: 'class'` per the theming setup in SKILL.md.

## Data table

```html
<div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700/60 bg-surface">
  <!-- toolbar -->
  <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 dark:border-slate-700/60 p-3">
    <input type="search" placeholder="Search orders…"
           class="h-9 w-64 rounded-lg border border-slate-300 bg-transparent px-3 text-sm
                  placeholder:text-slate-400 focus-visible:ring-2 focus-visible:ring-accent">
    <!-- filter chips / selects go here -->
  </div>

  <!-- bulk bar (visible when selection > 0) -->
  <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-700/60 bg-raised px-4 py-2 text-sm" hidden>
    <strong class="tabular-nums">3 selected</strong>
    <button class="font-medium text-red-600 hover:underline">Cancel orders</button>
    <button class="ml-auto hover:underline">Clear</button>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full min-w-[720px] text-sm">
      <thead>
        <tr class="border-b border-slate-200 dark:border-slate-700/60 text-left text-xs uppercase tracking-wide text-slate-500">
          <th class="w-12 px-4 py-2"><input type="checkbox" aria-label="Select all"></th>
          <th aria-sort="none" class="px-4 py-2 font-medium">
            <button class="inline-flex items-center gap-1 hover:text-slate-900 dark:hover:text-slate-100">Order ↕</button>
          </th>
          <th class="px-4 py-2 font-medium">Customer</th>
          <th class="px-4 py-2 font-medium">Status</th>
          <th class="px-4 py-2 text-right font-medium">Total</th>
          <th class="w-12 px-4 py-2"><span class="sr-only">Actions</span></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
        <tr class="hover:bg-raised/60 cursor-pointer">
          <td class="px-4 py-2"><input type="checkbox" aria-label="Select order #1042"></td>
          <td class="px-4 py-2 font-medium">#1042</td>
          <td class="px-4 py-2">Amara Okafor</td>
          <td class="px-4 py-2">[[status pill]]</td>
          <td class="px-4 py-2 text-right tabular-nums">$86.40</td>
          <td class="px-4 py-2 text-right">
            <button class="rounded-lg px-2 py-1 hover:bg-raised" aria-label="Actions for order #1042">⋯</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- footer -->
  <div class="flex items-center justify-between gap-2 p-3 text-sm text-slate-500">
    <span class="tabular-nums">Showing 1–25 of 312</span>
    <div class="flex gap-2">
      <button class="rounded-lg border border-slate-300 px-3 py-1.5 hover:bg-raised disabled:opacity-40">Previous</button>
      <button class="rounded-lg border border-slate-300 px-3 py-1.5 hover:bg-raised">Next</button>
    </div>
  </div>
</div>
```

Rules: name/identifier column first (medium weight), numeric columns
right-aligned with `tabular-nums`, status near the end, actions last (kebab).
Sortable headers are `<button>`s with `aria-sort` and an arrow glyph.
Long text: `max-w-[280px] truncate` + `title="…"`. Hover row = open detail.
Empty-from-filters message lives where rows would render:

```html
<tr><td colspan="6" class="p-10 text-center text-slate-500">
  No orders match these filters.
  <button class="ml-1 font-medium text-accent hover:underline">Clear filters</button>
</td></tr>
```

## Status pills

```html
<span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-600/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">
  <span class="size-1.5 rounded-full bg-current"></span>Paid
</span>
```

Mapping: success `emerald` (Paid, Active) · warning `amber` (Pending,
Expiring) · danger `red` (Failed, Suspended) · info `sky` (Draft,
Processing) · neutral `slate` (Archived). Dot uses `bg-current` so the pill
never relies on color alone. Same status = same pill everywhere.

## Filters

- Chip: `inline-flex items-center gap-1 rounded-full border border-slate-300 bg-raised px-2.5 py-0.5 text-xs` with a `×` button carrying
  `aria-label="Remove filter: status is Paid"`.
- Date range: a `<select>` beside the table **and** the KPI row, one control
  driving both (`h-9 rounded-lg border border-slate-300 px-2 text-sm`).
- Keep filters in the query string (`?status=paid&sort=total:desc`) so views
  are shareable and survive refresh. On page load, restore chips from it.

## Forms

```html
<form class="mx-auto max-w-[640px] space-y-6">
  <fieldset class="space-y-4">
    <legend class="text-sm font-semibold">Order details</legend>
    <div>
      <label for="po" class="block text-sm font-medium">Purchase order <span class="text-red-600">*</span></label>
      <input id="po" name="po" required aria-describedby="po-help"
             class="mt-1.5 h-9 w-full rounded-lg border border-slate-300 bg-transparent px-3 text-sm
                    focus-visible:ring-2 focus-visible:ring-accent
                    aria-[invalid]:border-red-600">
      <p id="po-help" class="mt-1 text-xs text-slate-500">Shown on the customer's invoice.</p>
      <p class="mt-1 hidden text-xs text-red-600" data-error-for="po">Enter a purchase order number.</p>
    </div>
  </fieldset>
  <div class="flex justify-end gap-2">
    <button type="button" class="h-9 rounded-lg border border-slate-300 px-4 text-sm hover:bg-raised">Cancel</button>
    <button type="submit" class="h-9 rounded-lg bg-accent px-4 text-sm font-medium text-accent-fg hover:opacity-90 disabled:opacity-50">Save changes</button>
  </div>
</form>
```

Group related fields under a heading; help text under the label (not in
placeholders); validate on blur and on submit; on failure focus the first
invalid field and show an error summary at top. Disable the submit button
only while saving.

## Dialogs and toasts

Native `<dialog>` — free focus trap, Esc, backdrop:

```html
<button class="h-9 rounded-lg bg-accent px-4 text-sm font-medium text-accent-fg" onclick="dlg.showModal()">Cancel orders</button>

<dialog id="dlg" class="rounded-xl border border-slate-200 bg-surface p-5 text-slate-900 backdrop:bg-black/50 dark:border-slate-700 dark:text-slate-100">
  <h2 class="text-base font-semibold">Cancel orders</h2>
  <p class="mt-2 text-sm text-slate-500">This cancels 3 orders and notifies the customers.</p>
  <div class="mt-5 flex justify-end gap-2">
    <button class="h-9 rounded-lg border border-slate-300 px-4 text-sm hover:bg-raised" autofocus>Keep orders</button>
    <button class="h-9 rounded-lg border border-red-600 px-4 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-950" onclick="dlg.close()">Cancel orders</button>
  </div>
</dialog>
```

Toast (fixed bottom-right, `role="status"`, auto-dismiss ~5s, errors persist
and use `role="alert"`, include Undo when reversible):

```html
<div role="status" class="fixed bottom-4 right-4 z-50 flex items-center gap-3 rounded-lg bg-slate-900 px-4 py-2.5 text-sm text-white shadow-lg dark:bg-white dark:text-slate-900">
  3 orders cancelled <button class="underline">Undo</button>
</div>
```

## Charts

Default to one inline SVG line or bar chart — no chart library unless the
project already has one. Pattern (see the template's `chart()` for a working
implementation): fixed viewBox, axis grid lines + labels in `fill-slate-400`,
one polyline in `stroke-accent` (`stroke-width="2.5"`), period + units in the
panel title ("Revenue, last 30 days (USD)"), `role="img"` +
`aria-label` describing the trend, and a `sr-only` data table as the
accessible alternative (or a "View as table" toggle). Bars start at zero;
≤5 series; label lines directly or provide a legend. `animate-pulse`
skeleton while loading; plain message when there's no data.

## Navigation

Sidebar item:

```html
<a href="/orders" aria-current="page"
   class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm
          bg-slate-100 font-medium text-slate-900 dark:bg-white/10 dark:text-white">
  [[icon]] Orders <span class="ml-auto rounded-full bg-red-600 px-2 py-0.5 text-xs text-white">4</span>
</a>
```

Inactive: same classes minus the active state, `text-slate-600 hover:bg-raised dark:text-slate-300`.
5–8 top-level items, grouped by task with optional `text-xs uppercase text-slate-400` group labels (skip labels if only one group). Badge counts
only where action is needed. Detail pages get breadcrumbs; the page title
repeats the last crumb.
