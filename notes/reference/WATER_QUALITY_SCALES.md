# Water Quality Classification Scales

These scales define how AgwaSuri classifies water quality for Philippine fishponds based on pH, temperature, salinity, and dissolved oxygen (DO) ranges reported in local studies and regulatory guidelines [web:26][web:39][web:32].

## pH Scale

### Research-based ranges

-   Regulatory standard: DENR AO Class SC acceptable pH range is 6.50–8.50 for aquaculture [web:26].
-   Observed Philippine fishpond values:
    -   Manila Bay fishponds: roughly 7.9–8.3
    -   Bukidnon (CMU) ponds: roughly 8.4–8.6
    -   Candaba, Pampanga ponds: roughly 7.8–8.7 [web:39].

### pH classification

| pH range | Classification               | Suitability for fish culture   |
| -------- | ---------------------------- | ------------------------------ |
| < 5.0    | HARMFUL highly acidic        | Lethal to fish                 |
| 5.0–6.4  | HARMFUL acidic               | Suboptimal or harmful          |
| 6.5–9.0  | GOOD acceptable              | Suitable for fish production   |
| 6.5–8.5  | GOOD optimal (DENR standard) | Recommended range              |
| 7.9–8.3  | GOOD typical range           | Common in Manila Bay fishponds |
| > 9.0    | HARMFUL highly alkaline      | Harmful to fish                |

## Temperature Scale

### Research-based ranges

-   Regulatory standard: DENR 2016 optimum range 25–31 °C for freshwater fish growth and reproduction [web:26].
-   Sub-lethal and lethal bounds: temperatures below about 20 °C or above about 35 °C are reported to cause serious stress or mortality for many cultured species [web:32].

### Temperature classification

| Temperature (°C) | Classification      | Suitability                           |
| ---------------- | ------------------- | ------------------------------------- |
| < 20             | HARMFUL sub-lethal  | Harmful to growth and survival        |
| 20–25            | BAD suboptimal      | Below desirable range                 |
| 25–31            | GOOD optimal (DENR) | Recommended for fish culture          |
| 26.7–31.0        | GOOD typical range  | Common in Philippine fishpond studies |
| 31–35            | GOOD acceptable     | Upper tolerance limit                 |
| > 35             | HARMFUL lethal      | Fatal to most fish species            |

## Salinity Scale

### Research-based ranges

-   Manila Bay fishponds typically show salinity from roughly 6.3 to 17.3 psu during flooding and draining cycles [web:26].
-   Species have different preferred salinity windows (for example tilapia, shrimp, and crab tolerating low to moderate brackish water) [web:26].

### Salinity level labels

| Salinity (ppt) | Salinity level |
| -------------- | -------------- |
| < 7            | Low            |
| 7–25           | Moderate       |
| > 25           | High           |

### Salinity and cultured species

| Salinity (ppt) | Classification         | Example suitable species               |
| -------------- | ---------------------- | -------------------------------------- |
| < 7.0          | Freshwater–brackish    | Tilapia                                |
| 5–25           | Low–moderate brackish  | Crab                                   |
| 6.3–17.3       | Typical observed range | General fish culture around Manila Bay |
| 10–25          | Moderate brackish      | Shrimp                                 |
| < 25           | Brackish               | Tilapia, shrimp, crab                  |
| > 25           | High salinity          | Limited freshwater species             |

## Dissolved Oxygen (DO) Scale

### Research-based ranges

-   Guideline: DO concentrations at or above about 5.0 mg/L are commonly recommended as suitable for fish production in ponds [web:32].
-   Philippine fishpond data show typical DO ranges from about 3.8 to 7.6 mg/L, with some high-quality systems reaching around 13–14 mg/L [web:26][web:39].

### DO classification

| DO (mg/L) | Classification               | Effect on fish                                 |
| --------- | ---------------------------- | ---------------------------------------------- |
| < 2.0     | HARMFUL critical/lethal      | High mortality, disease risk                   |
| 2.0–3.0   | HARMFUL severe stress        | Extreme stress, sharply reduced feeding        |
| 3.0–5.0   | GOOD suboptimal              | Growth retardation and chronic stress          |
| ≥ 5.0     | GOOD optimal (DENR standard) | Suitable for fish production                   |
| 3.8–7.6   | GOOD typical range           | Common in Manila Bay and Pampanga ponds        |
| 13–14     | GOOD high range              | Very favorable conditions in some case studies |

## Usage in the system

-   These ranges should be implemented as configuration data and used to classify each real-time sensor reading as “HARMFUL,” “BAD,” or “GOOD” for dashboard cards, trend chart annotations, and historical status flags.
-   The same ranges should also back any analysis summaries and fish-species recommendations so that all front-end messaging is consistent with the underlying thresholds [web:26][web:39][web:32].
