---
title: "Sample Booking Conversation"
category: examples
version: "1.1"
last_reviewed: "2024-10-01"
author: "Customer Experience Team"
approved: true
tags: [example, conversation, booking, quote, escalation, voice, webchat]
---

# Sample Booking Conversation

This document illustrates a complete end-to-end booking conversation between a customer and the TitanChatbot BookingAgent. It demonstrates intent capture, quote flow, availability check, booking confirmation, and an escalation scenario.

Channels shown: **Webchat** (primary) and a **Voice** variant (abbreviated).

---

## Example 1 — Full Webchat Booking (End-of-Lease)

---

**[TURN 1 — USER]**
> Hi, I need to book an end of lease clean for my apartment.

---

**[TURN 1 — ASSISTANT]**
> Hi! I'd be happy to help you book an end-of-lease clean. 😊
>
> To get you an accurate quote, I just need a few details:
> 1. How many bedrooms and bathrooms does the apartment have?
> 2. What suburb or postcode is the property in?

---

**[TURN 2 — USER]**
> It's a 2 bedroom, 1 bathroom apartment in Newtown, 2042.

---

**[TURN 2 — ASSISTANT]**
> *(Agent calls `check_service_area` with suburb="Newtown", postcode="2042")*
>
> Great news — we service Newtown! 🎉
>
> *(Agent calls `calculate_quote` with property_type="apartment", bedrooms=2, bathrooms=1, service_type="end_of_lease")*
>
> Here's an estimated quote for your 2-bedroom, 1-bathroom end-of-lease clean in Newtown:
>
> | | |
> |---|---|
> | **Estimated price** | **$300 – $370** |
> | **Estimated duration** | ~4 hours |
> | **Includes** | All rooms, interior windows, oven, kitchen cupboards, all bathrooms |
>
> This is a fixed-price service — no surprise charges on the day.
>
> Do you have any add-ons in mind, like carpet steam cleaning or fridge cleaning?

---

**[TURN 3 — USER]**
> Yes, I'd like to add carpet steam cleaning in the 2 bedrooms and the living room.

---

**[TURN 3 — ASSISTANT]**
> *(Agent calls `calculate_quote` adding extras=["carpet"])*
>
> Perfect. Adding carpet steam cleaning for 3 rooms ($55 per room):
>
> | | |
> |---|---|
> | **Base clean** | $300 – $370 |
> | **Carpet steam (3 rooms)** | $165 |
> | **Estimated total** | **$465 – $535** |
>
> Do you have a preferred date in mind for the clean?

---

**[TURN 4 — USER]**
> I was hoping for Saturday the 16th of November or Sunday the 17th.

---

**[TURN 4 — ASSISTANT]**
> *(Agent calls `lookup_available_slots` with preferred_date="2024-11-16", date_to="2024-11-17", service_type="end_of_lease", postcode="2042")*
>
> I've checked our schedule. Here's what's available:
>
> - ✅ **Saturday 16 Nov — 8:00 AM** *(note: weekend rate applies, +20%)*
> - ✅ **Saturday 16 Nov — 12:00 PM** *(weekend rate applies)*
> - ✅ **Sunday 17 Nov — 9:00 AM** *(weekend rate applies)*
>
> **Revised total with weekend surcharge (20%):**
> | | |
> |---|---|
> | **Estimated total** | **$558 – $642** |
>
> Which slot works best for you?

---

**[TURN 5 — USER]**
> Let's go with Saturday 16th at 8am.

---

**[TURN 5 — ASSISTANT]**
> Great choice! To confirm your booking, I'll need a few more details:
>
> 1. Your full name
> 2. Your contact phone number
> 3. Your email address
> 4. The full service address (including unit number)

---

**[TURN 6 — USER]**
> Sarah Mitchell, 0412 555 123, sarah.mitchell@email.com. The address is Unit 4, 12 King Street, Newtown NSW 2042.

---

**[TURN 6 — ASSISTANT]**
> *(Agent calls `lookup_customer` with phone="0412 555 123")*
>
> Thanks, Sarah! Let me confirm your booking details:
>
> | | |
> |---|---|
> | **Name** | Sarah Mitchell |
> | **Service** | End-of-Lease Clean + Carpet Steam (3 rooms) |
> | **Address** | Unit 4, 12 King Street, Newtown NSW 2042 |
> | **Date / Time** | Saturday, 16 November 2024 — 8:00 AM |
> | **Estimated Total** | $558 – $600 (final price confirmed on day) |
>
> Is everything correct?

---

**[TURN 7 — USER]**
> Yes that's all correct.

---

**[TURN 7 — ASSISTANT]**
> *(Agent calls `create_booking_request` with full details)*
>
> ✅ **Booking Confirmed!**
>
> Your reference number is **TC-2024-11-8847**.
>
> You'll receive a confirmation SMS and email shortly. Our cleaner will arrive at 8:00 AM on the 16th. Make sure the property is accessible — if you'll be using a lock box or leaving a key, please let us know when you receive the confirmation email.
>
> Is there anything else I can help you with?

---

**[TURN 8 — USER]**
> Actually, I want to make a complaint about a clean I had last week. The bathroom wasn't done properly.

---

**[TURN 8 — ASSISTANT]**
> I'm really sorry to hear that, Sarah. That's not the standard we aim for.
>
> *(Agent calls `create_escalation` with reason="complaint", urgency="high", customer_name="Sarah Mitchell", summary="Customer reporting unsatisfactory bathroom clean from previous booking.")*
>
> I've created an escalation ticket for our team: **Ticket #ESC-4421**.
>
> Our team will review your previous booking and contact you within **4 business hours** to arrange a resolution. You can also submit photos of the affected area by replying to your confirmation email with "Complaint #ESC-4421" in the subject line.
>
> Again, I apologise for the inconvenience. Is there anything else I can help with today?

---

**[TURN 9 — USER]**
> No that's everything, thanks.

---

**[TURN 9 — ASSISTANT]**
> You're welcome, Sarah! Have a great day, and we'll see you on the 16th. 🙂

---

---

## Example 2 — Voice Channel (Abbreviated)

*Voice responses are shorter and avoid formatting. No markdown, no tables.*

---

**[TURN 1 — USER — Voice]**
> "Hi, I want a quote for a house clean, three bedrooms, two bathrooms."

---

**[TURN 1 — ASSISTANT — Voice]**
> "Hi there! For a 3-bedroom, 2-bathroom house, a standard clean would typically cost between $200 and $250. Would you like to go ahead and book a date, or do you have any questions first?"

---

**[TURN 2 — USER — Voice]**
> "That sounds good. Can I do next Friday?"

---

**[TURN 2 — ASSISTANT — Voice]**
> "Let me check availability for next Friday. *(brief pause)* Yes, I have a 9 AM and a 1 PM slot available. Which would you prefer?"

---

**[TURN 3 — USER — Voice]**
> "9 AM please."

---

**[TURN 3 — ASSISTANT — Voice]**
> "Perfect. Could I get your name, phone number, and the address for the clean?"

---

*...booking proceeds as per webchat example...*

---

**[Final Turn — ASSISTANT — Voice]**
> "Great, you're all booked in. Your reference number is TC-2024-11-9103. You'll get a confirmation SMS shortly. Is there anything else I can help with?"

---

---

## Example 3 — Out-of-Scope Query (Escalation)

---

**[TURN 1 — USER]**
> Can you give me legal advice about whether my landlord is allowed to keep my bond?

---

**[TURN 1 — ASSISTANT]**
> That's a question I can't answer — it involves legal and tenancy matters that are outside what I'm able to advise on.
>
> For guidance on bond disputes, I'd suggest contacting your state's Fair Trading or Residential Tenancies Authority. They offer free advice.
>
> If you'd like, I can also connect you with one of our team members who may be able to point you in the right direction. Would that help?

---

**[TURN 2 — USER]**
> Yes please, connect me to someone.

---

**[TURN 2 — ASSISTANT]**
> *(Agent calls `create_escalation` with reason="complex_request", summary="Customer seeking tenancy/legal advice re: bond dispute. Referred to team for guidance.")*
>
> Done! I've raised a callback request with our team — **Ticket #ESC-4422**. Someone will be in touch within 2 business hours.
>
> Is there anything else I can help you with in the meantime?
