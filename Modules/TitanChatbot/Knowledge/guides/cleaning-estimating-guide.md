---
title: "Cleaning Service Estimating Guide"
category: guides
version: "1.2"
last_reviewed: "2024-11-01"
author: "Operations Manager"
approved: true
tags: [estimating, pricing, hours, area, rooms, service-types]
---

# Cleaning Service Estimating Guide

This guide defines the formulas and rules used to estimate cleaning time and price. It is the single source of truth for the BookingAgent's `calculate_quote` tool and for any human estimator generating quotes.

---

## Area-Based Estimation (Square Metres → Hours)

When the customer provides a floor area in square metres, use the following base formula:

| Floor Area (sqm) | Base Hours (Standard Clean) |
|------------------|-----------------------------|
| Up to 50 sqm     | 2.0 hours                   |
| 51 – 80 sqm      | 2.5 hours                   |
| 81 – 120 sqm     | 3.0 hours                   |
| 121 – 160 sqm    | 3.5 hours                   |
| 161 – 200 sqm    | 4.0 hours                   |
| 201 – 250 sqm    | 5.0 hours                   |
| 251 – 300 sqm    | 6.0 hours                   |
| 301 – 400 sqm    | 7.5 hours                   |
| Over 400 sqm     | Manual quote required        |

**Formula for mid-range (81–300 sqm):**
`Base Hours = 2.0 + (sqm / 60)`  *(rounded to nearest 0.5)*

---

## Room Count Formulas

When floor area is not provided, estimate from room counts. Use bedroom + bathroom count as the primary driver:

### Residential (House / Apartment)

| Bedrooms | Bathrooms | Standard Clean (hours) |
|----------|-----------|------------------------|
| Studio   | 1         | 1.5                    |
| 1        | 1         | 2.0                    |
| 2        | 1         | 2.5                    |
| 2        | 2         | 3.0                    |
| 3        | 1         | 3.0                    |
| 3        | 2         | 3.5                    |
| 4        | 2         | 4.5                    |
| 4        | 3         | 5.0                    |
| 5        | 2         | 5.5                    |
| 5        | 3         | 6.0                    |

**Additional room adjustments:**
- Each extra living area / lounge: **+0.5 hours**
- Study / home office: **+0.25 hours**
- Garage (interior sweep): **+0.5 hours**
- Outdoor area / balcony: **+0.25 hours**

### Commercial / Office

For commercial properties, use the area-based table above. If only desks/workstations are known:
- Up to 10 workstations: **2.0 hours**
- 11–25 workstations: **3.5 hours**
- 26–50 workstations: **5.0 hours**
- Over 50 workstations: **Manual quote required**

---

## Condition Modifiers

The current cleanliness condition of the property adds a multiplier to the base hours:

| Condition  | Description                                                  | Hours Multiplier |
|------------|--------------------------------------------------------------|------------------|
| `light`    | Regularly maintained, minor touch-up needed                  | × 0.8            |
| `standard` | Average household, moderate dust/grime                       | × 1.0            |
| `heavy`    | Significant neglect, visible grime, grease in kitchen        | × 1.3            |
| `deep`     | Long-term neglect, mould present, requires chemical treatment | × 1.6           |

> **Note:** The condition `deep` triggers a mandatory on-site assessment for properties larger than 150 sqm. The BookingAgent should flag this and offer a callback.

---

## Service Type Multipliers

Different service types require different thoroughness. Apply these multipliers to the condition-adjusted hours:

| Service Type    | Description                                                       | Hours Multiplier | Price Multiplier |
|-----------------|-------------------------------------------------------------------|------------------|------------------|
| `regular`       | Routine maintenance clean (fortnightly / monthly customers)       | × 1.0            | × 1.0            |
| `end_of_lease`  | Full bond / vacate clean to real estate standard                  | × 1.5            | × 1.6            |
| `move_in`       | New tenant pre-occupation clean                                   | × 1.3            | × 1.4            |
| `deep_clean`    | One-off thorough clean (spring clean, post-renovation)            | × 1.4            | × 1.5            |
| `commercial`    | Commercial premises (office, retail, strata)                      | × 1.2            | × 1.3            |

### Combined Formula

```
Estimated Hours = Base Hours × Condition Multiplier × Service Type Multiplier
Final Price     = Estimated Hours × Hourly Rate × Service Price Multiplier
```

Always round Estimated Hours UP to the nearest 0.5 before pricing.

---

## Minimum Booking Thresholds

Regardless of the formula output, the following minimums apply:

| Service Type    | Minimum Hours | Minimum Charge |
|-----------------|---------------|----------------|
| `regular`       | 2.0 hours     | $110           |
| `end_of_lease`  | 3.0 hours     | $220           |
| `move_in`       | 2.5 hours     | $180           |
| `deep_clean`    | 2.5 hours     | $195           |
| `commercial`    | 2.0 hours     | $140           |

If the formula produces a value below the minimum, apply the minimum.

---

## Add-On Services

Add-ons are priced separately and added on top of the base quote:

| Add-On Service    | Fixed Price | Notes                                    |
|-------------------|-------------|------------------------------------------|
| Oven clean        | $65         | Inside oven, racks, and door             |
| Fridge clean      | $45         | Inside fridge/freezer, shelves removed   |
| Window cleaning   | $8 per pane | Interior side; exterior by arrangement   |
| Carpet steam clean| $55/room    | Minimum 2 rooms; excludes stain treatment|
| Wall spot-clean   | $40         | Up to 10 spots; full walls = custom quote|
| Blind cleaning    | $15/blind   | Wipe-down of horizontal blinds           |
| Balcony sweep     | $35         | Sweep, mop, wipe railings                |

---

## Regional Pricing Bands

The hourly rate varies by region:

| Region Band | Example Areas                    | Standard Hourly Rate | After-Hours Rate |
|-------------|----------------------------------|----------------------|------------------|
| Metro A     | CBD, inner suburbs (≤ 10km)      | $55/hr               | $72/hr           |
| Metro B     | Middle suburbs (10–25km)         | $50/hr               | $65/hr           |
| Outer Metro | Outer suburbs (25–45km)          | $47/hr               | $60/hr           |
| Regional    | Towns > 45km from nearest branch | $44/hr + travel      | $58/hr + travel  |

**Travel surcharge** for Regional bookings: $0.80/km beyond the 45km radius, calculated one-way from the nearest branch.

---

## Quick Reference: End-of-Lease Pricing Examples

| Property         | Condition  | Extras          | Estimated Hours | Approx. Price |
|------------------|------------|-----------------|-----------------|---------------|
| 1bed/1bath apt   | Standard   | None            | 3.0 hrs         | $220–$260     |
| 2bed/1bath apt   | Standard   | Oven            | 4.0 hrs         | $300–$350     |
| 3bed/2bath house | Standard   | Oven + Carpet   | 6.0 hrs         | $480–$550     |
| 4bed/2bath house | Heavy      | Oven + Fridge   | 9.0 hrs         | $700–$800     |

*Prices shown are indicative. Actual quote from `calculate_quote` tool uses live rates.*

---

## Estimation Confidence Rules

1. **High confidence** — area sqm provided + service type + condition known → use formula directly.
2. **Medium confidence** — only room count provided → use room count table, present as a range (±10%).
3. **Low confidence** — only address or property type known → present a wide range and recommend an inspection.

When confidence is Medium or Low, the BookingAgent must clearly state that the quote is an estimate and may be adjusted on-site.
