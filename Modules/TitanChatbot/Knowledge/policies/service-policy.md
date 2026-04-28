---
title: "Service Delivery Policy"
category: policies
version: "2.1"
last_reviewed: "2024-10-15"
author: "Operations Manager"
approved: true
tags: [policy, booking, cancellation, rescheduling, access, quality, dispute]
---

# Service Delivery Policy

This policy governs how cleaning services are booked, delivered, and supported. It applies to all customers, operators, and the TitanChatbot BookingAgent. The agent must never override or contradict this policy.

---

## Booking Confirmation Requirements

A booking is only considered **confirmed** when ALL of the following conditions are met:

1. The customer has provided: full name, contact phone number, service address, and preferred date/time.
2. A quote has been presented and verbally or digitally accepted by the customer.
3. A `booking_id` and `confirmation_number` have been issued by the `create_booking_request` tool.
4. A confirmation SMS or email has been dispatched to the customer.

**Provisional bookings** (awaiting one or more of the above) are held for a maximum of **2 hours** before being automatically released. The BookingAgent must inform the customer of this window.

### Confirmation Message
The agent must read back or send the following summary after confirmation:

> "Your [service type] clean is booked for [date] at [time] at [address]. Your reference number is [confirmation_number]. You'll receive a confirmation by [SMS/email]."

---

## Cancellation and Rescheduling Windows

### Customer-Initiated Cancellation

| Notice Given            | Outcome                                                      |
|-------------------------|--------------------------------------------------------------|
| More than 48 hours      | Full refund or credit note; no cancellation fee              |
| 24 – 48 hours           | 50% cancellation fee applies                                 |
| Less than 24 hours      | Full booking fee charged (no refund)                         |
| No-show / locked out    | Full booking fee charged + $35 lock-out fee                  |

**First-time cancellation exception:** For a customer's very first booking, the 24–48 hour fee is waived once as a goodwill gesture. The BookingAgent may apply this exception automatically and should log it in the booking notes.

### Customer-Initiated Rescheduling

| Notice Given            | Outcome                                                      |
|-------------------------|--------------------------------------------------------------|
| More than 48 hours      | Free reschedule; new slot must be within 30 days             |
| 24 – 48 hours           | Free reschedule allowed once; second reschedule incurs $25 fee |
| Less than 24 hours      | Treated as a cancellation; cancellation fee applies          |

### Operator-Initiated Cancellation
If the company cancels or reschedules a booking:
- Customer receives **full refund or complimentary re-book** at no charge.
- If notice is given less than 24 hours before the service, the customer receives a **$50 service credit** in addition to the refund or re-book.

---

## Key Handover Protocols

Many bookings require key access when the customer is not present. The following protocols apply:

### Permitted Key Handover Methods

1. **In-person handover** — Customer or authorised person hands key to the cleaner at the property.
2. **Key lock box** — Customer provides the lock-box code in advance (stored encrypted; not visible to the BookingAgent in plaintext after confirmation).
3. **Building/strata manager** — Customer arranges access via the building manager.
4. **Real estate agent** — For end-of-lease cleans; agent provides key directly to the cleaner.

### Prohibited Key Handling
- Cleaners must never take keys off-site after a service.
- Keys must never be copied or duplicated.
- Lost key incidents must be reported immediately to the office (see Operator SOP).

### Digital Access Systems
For smart locks and app-based entry, the customer must provide a **one-time access code** that expires after the scheduled service window.

---

## Property Access Requirements

The property must be accessible for the full estimated duration of the clean. Specific requirements:

- All areas to be cleaned must be **unlocked and unobstructed** at arrival.
- Utilities must be active: **running water** and **electricity** are required.
- Pets must be **secured or removed** from the premises during the service (see FAQ for details).
- If a property is inaccessible at the scheduled time, the cleaner will wait up to **15 minutes** before the booking is treated as a no-show.

---

## Quality Guarantee Policy

All cleaning services carry a **48-hour re-clean guarantee**:

- If the customer is not satisfied with any aspect of the clean, they must notify the company within **48 hours** of service completion.
- The company will dispatch a cleaner to re-clean the disputed area(s) at **no charge**.
- The re-clean guarantee applies to the specific areas reported, not the entire property.
- The guarantee does **not** apply if:
  - The property was re-entered and used after the service (e.g., moved furniture, cooked in kitchen).
  - The issue relates to pre-existing damage or staining that cannot be cleaned.
  - The claim is made more than 48 hours after the service.

**The BookingAgent must escalate all quality complaints** via the `create_escalation` tool with `reason=complaint`. The agent must not attempt to negotiate compensation or re-clean terms directly.

---

## Dispute Resolution Process

### Step 1 — Initial Report
The customer reports the issue to the BookingAgent or via phone/email within 48 hours.

### Step 2 — Escalation
The BookingAgent creates an escalation ticket (`create_escalation`, `reason=complaint`, `urgency=high`). The customer is given a ticket reference number and an estimated response time.

### Step 3 — Review
The Operations Manager reviews the claim within **4 business hours**. If photos are available, the customer is asked to submit them via the link in the escalation confirmation email.

### Step 4 — Resolution
- **Valid claim:** Re-clean scheduled within 48 hours at no charge.
- **Partial claim:** Re-clean of specific areas or partial credit applied.
- **Rejected claim:** Written explanation provided with photographic evidence where available.

### Step 5 — Escalation to Management
If the customer remains unsatisfied after Step 4, the dispute is escalated to the General Manager. Response within 2 business days.

### External Dispute Resolution
For unresolved disputes, customers may contact Fair Trading / Consumer Affairs in their jurisdiction. The company will cooperate fully with any investigation.

---

## Policy Amendments

This policy is reviewed every 6 months. All amendments are versioned (see `version` in front matter). The BookingAgent always uses the current approved version. Historic versions are archived in the `policies/archive/` directory.
