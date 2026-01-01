# Overall Water Quality Score — Documentation

This document explains how the dashboard computes the "Overall Water Quality Score" shown on the dashboard. The text and numbers below reflect the exact logic implemented in the codebase (see `app/Livewire/WaterQualityCard.php` and `config/water_quality.php`). Use this section in your thesis Methods or Appendix to make the computation auditable.

## 1) Parameter classification ranges (source of truth)
- Ranges and labels are defined in `config/water_quality.php` per-parameter. Example canonical parameters and their keys:
  - `temperature_c` (°C)
  - `salinity_ppt` (ppt)
  - `dissolved_oxygen_mg_l` (mg/L)
  - `ph` (pH)

The classification arrays in `config/water_quality.php` map numeric ranges to textual labels such as `BEST`, `GOOD`, `BAD`, `HARM`. These ranges are used for textual badges and annotations; the score computation uses a simplified status mapping described next.

## 2) Status mapping used for scoring
For scoring, each parameter value is converted to one of the simplified status keys by `WaterQualityCard::statusFor()`:
- `good` — parameter within the good range
- `warning` — parameter marginal (borderline) but not critical
- `danger` — parameter in an unacceptable range
- `unknown` — null or missing value

The implemented per-parameter thresholds in `statusFor()` (exact code) are:

- Temperature (°C):
  - good: 24 ≤ T ≤ 30
  - warning: 20 ≤ T < 24 or 30 < T ≤ 34
  - danger: otherwise

- Salinity (ppt):
  - good: S ≤ 35
  - warning: 35 < S ≤ 50
  - danger: S > 50

- Dissolved oxygen (mg/L):
  - good: DO ≥ 5
  - warning: 3 ≤ DO < 5
  - danger: DO < 3

- pH:
  - good: 6.5 ≤ pH ≤ 8.5
  - warning: 6.0 ≤ pH < 6.5 or 8.5 < pH ≤ 9.0
  - danger: otherwise

These thresholds are the authoritative, code-level values — see `app/Livewire/WaterQualityCard.php::statusFor()`.

## 3) Per-parameter numeric score mapping
Each parameter status is converted to a numeric score in `parameterScore()`:
- `good` → 100
- `warning` → 80 (mid-range warning value)
- `danger` → 40 (mid-range danger value)
- `unknown` → 0

This mapping is used as input to the weighted-average described below.

## 4) Weights and weighted-average formula
Weights applied (implemented in `calculateOverallScore()`):
- Dissolved Oxygen: 30% (0.30)
- Temperature: 25% (0.25)
- pH: 25% (0.25)
- Salinity: 20% (0.20)

Weighted-average formula (implemented exactly):

TotalScore = ( Σ_i (score_i * weight_i) ) / ( Σ_i weight_i_for_valid_parameters )

- Only parameters with non-null values contribute to both the numerator and the denominator (weights of missing parameters are excluded). The final `Overall Water Quality Score` is rounded to the nearest integer and constrained to the 0–100 range by construction.

Example (all parameters present):

OverallScore = round((Score_DO*0.30 + Score_Temp*0.25 + Score_pH*0.25 + Score_Salinity*0.20) / 1.0)

If a parameter is missing, its weight is removed from the denominator and the numerator (effectively re-normalizing the weighted average among remaining parameters).

## 5) Overall label / "worst parameter wins" rule
The textual overall status (e.g., `HARM`, `BAD`, `GOOD`, `BEST`, `Disconnected`) is computed in `computeOverallStatus()` and follows this priority:

- If any parameter normalized key equals `harm` → overall label = `HARM`
- Else if any parameter normalized key equals `bad` → overall label = `BAD`
- Else if any parameter normalized key equals `good` → overall label = `GOOD`
- Else if all available (non-unknown) metrics are `best` → overall label = `BEST`
- Else if all metrics are unknown/null → overall label = `Disconnected`

This implements a conservative "worst-parameter wins" approach: a single `HARM` or `BAD` parameter elevates the overall label accordingly even if other parameters are good.

Note: the scoring number (0–100) and the textual label are computed independently; the label uses the conservative rule above, while the numeric score reflects the weighted average.

## 6) Scoring buckets for dashboard messaging
The numeric score is mapped to textual buckets in the UI:
- ≥ 90 → `EXCELLENT` (green)
- 70–89 → `MODERATE` (yellow)
- < 70 → `CRITICAL` (red)

These buckets are used only for the human-facing summary text and color styling.

## 7) Configurability
- All classification ranges and chart annotation thresholds are configurable via `config/water_quality.php` (and related `water_colors` configs). Changing values there will change badges and annotations; the `statusFor()` thresholds and the weight values are implemented in `app/Livewire/WaterQualityCard.php` and must be updated there if you want to change scoring behavior.

## 8) Citations / references
To ground the thresholds and weight emphasis in established aquaculture practice, cite authoritative guidance. Suggested references (add specific editions in the thesis bibliography):

- FAO — water quality guidelines for aquaculture (FAO technical resources on pond and cage culture; e.g., FAO aquaculture manuals and technical papers provide ranges for DO, pH and temperature). 
- Boyd, C.E. & Tucker, C.S. — foundational texts on pond water quality and pond management (widely cited for practical water-quality limits and oxygen thresholds).

(These citations indicate the conventional sources used to derive safe operating ranges and justify the DO-heavy weighting in the score. Replace the above reference placeholders with exact bibliographic entries in the thesis references section.)

## 9) Reproducibility checklist
- Code locations: `app/Livewire/WaterQualityCard.php` (status rules, weights, parameter scoring), `config/water_quality.php` (classification ranges, thresholds), `app/Support/helpers.php` (classification helpers and aggregated tokens)
- To change behavior for the dashboard score, update the values in `WaterQualityCard::parameterScore()`, `WaterQualityCard::calculateOverallScore()` and/or `WaterQualityCard::statusFor()`, and include unit tests asserting expected numeric outputs for canonical input vectors.

---
Document created from code inspection on January 1, 2026. Verify and add formal bibliographic citations in the thesis bibliography before submission.
