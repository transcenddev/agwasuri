# Admin Dashboard Chart Fix Log

Date: 2026-01-05
Scope: Livewire WaterQualityChart + ApexCharts (admin dashboard) desync between selected user and chart instance.

## Goals

-   Chart ID must track the selected user (no reuse of `water-quality-1`).
-   Date/time filters should apply without page reload.
-   Prevent stale cached chart instances when switching users or ranges.

## Changes Made

### Server (Livewire)

-   WaterQualityChart dispatches chart payloads with meta: `userId`, `chart_id`, `startDate`, `endDate`.
-   On user switch (`updateUser`) and date range change (`updateDateRange`), component clears caches and destroys chart before reloading data.
-   `hydrate` reassigns `chartId` to the current user to align server/client IDs.
-   `loadData` builds chart config, sets `chartId`, and logs meta for debugging.

### Frontend (ApexCharts bootstrap)

-   `resources/js/waterQualityInit.js` now derives `userId` from meta, URL `?user=`, or nearest `data-user-id` root; rejects payloads with no user.
-   Forces `derivedChartId = water-quality-{userId}`; ignores stale `payload.chart.id` and clears caches for mismatched IDs.
-   Always creates a fresh chart instance for the derived ID and destroys any existing chart before creation to avoid reuse.
-   Clears cached series when payload ID differs from the working/derived ID to prevent stale appends.
-   Added guards to prefer derived ID over payload/DOM IDs and to refuse initialization without a resolved user.

### Build/verification steps run

-   `npm run build` (2026-01-05) to rebuild assets after JS changes.

## How to Verify

1. Hard-refresh with cache disabled (Ctrl+Shift+R). Ensure Vite build is current.
2. Switch admin user selector:
    - Console should log chart meta with `chart_id = water-quality-{selectedUser}`.
    - Chart should destroy/recreate and render the selected user’s data.
3. Apply custom date/time range:
    - Confirm chart reloads with new range without page reload.
4. Repeat user switch after date range change to confirm caches are cleared and IDs stay aligned.

## Outstanding Risks / Next Steps

-   If console still shows `water-quality-1`, add temporary debug logs in `waterQualityInit.js` to print `derivedUserId`, `meta.userId`, URL `user`, and `data-user-id` source detection.
-   If Livewire payload lacks `meta.userId`, confirm server dispatch includes it and that selected user propagates through filters.
