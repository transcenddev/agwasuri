# Web Design — Agwasuri

This document captures design context, UX strategy, UI guidance, and implementation notes for the Agwasuri web application. It is intended for designers, frontend developers, and contributors working on UI, UX, and product flows.

## Chapter One: Process

### Planning and Strategy

Purpose and audience

-   Why: Provide fish farmers with real-time water quality monitoring and AI-driven fish species recommendations to improve decision-making and sustainability.
-   Who: Primary: fish farmers. Secondary: aquaculture experts, municipal agriculture officers, and researchers.
-   What: Deliver timely insights, automate monitoring, and promote sustainability with a simple, mobile-first interface.
-   Success metrics: daily active users (DAU), dashboard session time, time-to-action after alerts, reduction in pond mortality in pilot farms, and recommendation acceptance rate.

### Structure (UX)

Start with low-fidelity sketches and map the critical user journeys:

-   First-time setup: register, add pond, pair sensor, confirm data flow.
-   Daily check: open dashboard, glance KPIs, read recommendation, take action.
-   Incident flow: receive alert, inspect histories, apply recommended mitigation, acknowledge alert.

Wireframe checklist:

-   Header: location selector, timestamp, connection status
-   KPI bar: temperature, pH, dissolved oxygen, salinity with color-coded status
-   Main chart area: selectable time ranges (1h, 24h, 7d, custom)
-   Recommendation card: predicted species, confidence, reason, actions
-   Quick actions: add note, call technician, silence alert

## Chapter Two: Strategy

### Problem

Manual water testing is slow and error-prone. Operators need fast, reliable guidance to avoid stock loss.

### Solution

Automate water-quality ingestion from IoT devices and provide clear, actionable recommendations through a responsive web app.

### Website Type

App-style website optimized for mobile use and on-site decision making.

### Goals

-   Present real-time water data and clear recommendations
-   Build trust via reliable, transparent features
-   Support decisions that improve pond outcomes

## Chapter Three: Structure

Key pages and features

-   Landing / Homepage
-   Real-time Dashboard (graphical indicators)
-   Fish Species Recommendations
-   Historical Data & Insights with filters and exports
-   Devices / Sensor Management
-   Settings / Alerts and account management

Use a sitemap and simple wireframes before detailed design.

## Chapter Four: Design

### Visual system

-   Color palette suggestion:
    -   Primary: #0B6E99 (deep blue)
    -   Accent: #2AAE97 (teal)
    -   Alert: #F59E0B (warning), #EF4444 (critical)
    -   Background: #F3F4F6 (light gray)
-   Typography: Inter or Nunito; base 16px; headings 20–32px
-   Iconography: simple stroke icons (drop, thermometer, fish, wifi)

### Components to standardize

-   KPI card (value, unit, status)
-   Line/area chart with selectable ranges
-   Recommendation card with action buttons
-   Modal for device pairing
-   Toast notifications for transient messages

### Data visualization principles

-   Show value, trend, and delta (value, arrow, change since 1h)
-   Keep units visible and consistent
-   Provide CSV export and shareable data slices

## Chapter Five: Development

Tools and steps

-   Design: Figma for components and tokens
-   Backend: Laravel with REST endpoints for ingestion and ML integration
-   Frontend: responsive UI with Livewire or React components
-   Steps: environment setup, DB schema, implement components, integrate IoT ingestion and ML
-   Testing: functional tests (Pest), E2E (Playwright), usability testing with target users
-   Deployment: secure hosting with SSL, daily backups of sensor data

### Offline and resilience

-   Cache last-known values locally on device
-   Allow acknowledging alerts offline; sync when reconnected

## Website Flow and Sitemap Summary

1. Homepage: overview and call to action
2. How it Works: sensor explanation and AI analytics
3. Dashboard: real-time monitoring and recommendations
4. Case Studies / Testimonials
5. Research Hub: optional data access for researchers
6. About & Contact
7. User Registration / Login

## Accessibility

-   AA contrast compliance
-   Keyboard navigable main actions and forms
-   Screen reader friendly labels for charts and buttons
-   Large touch targets for mobile

## Analytics and Metrics

Track events: `sensor_connected`, `recommendation_viewed`, `recommendation_accepted`, `alert_acknowledged`.

## Handoff checklist for developers

-   Figma components and tokens
-   API spec for sensor POST and ML endpoints
-   Seed/test dataset for charts
-   Acceptance criteria for pages and flows

## Roadmap (high level)

-   Phase 0 (MVP): ingestion, dashboard, recommendations, seed data
-   Phase 1: alerts, offline cache, user settings
-   Phase 2: analytics dashboards, multi-pond support
-   Phase 3: field trials, advanced ML, scale

## Copy examples (microcopy ready-to-use)

Homepage hero:
"Real-time pond monitoring and AI recommendations to help keep fish healthy and reduce losses."

Dashboard empty state:
"No data yet. Check your device connection or add a sensor."

Recommendation card example:
"Recommended species: Nile tilapia. Confidence: 87%. Reason: current temperature and dissolved oxygen are within the optimal range. Action: prepare fingerlings and reduce feeding for 24 hours."

---

If you want, I can also produce a Figma-ready tokens list (colors, type sizes, spacing) or create acceptance tests for the dashboard and recommendation flows.
