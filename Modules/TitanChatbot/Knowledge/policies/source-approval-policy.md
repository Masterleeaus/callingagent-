---
title: "Knowledge Source Approval Policy"
category: policies
version: "1.3"
last_reviewed: "2024-09-01"
author: "Knowledge Manager"
approved: true
tags: [policy, knowledge, approval, sources, quality, review]
---

# Knowledge Source Approval Policy

This policy governs what content may be added to the TitanChatbot Knowledge Pack and how it must be reviewed before the AI agent can use it. This policy exists to protect customers from inaccurate, outdated, or harmful information being surfaced by the AI.

---

## Approved Source Types

Only content from the following source categories may be added to the Knowledge Pack:

### 1. Operator-Authored Content
Content written directly by the business operator or their designated staff.

- **Examples:** Custom rate cards, internal SOPs, business-specific policies.
- **Approval authority:** Operations Manager or above.
- **Review cycle:** 3–12 months depending on category (see Review Schedule below).
- **Required metadata:** `author`, `approved: true`, `version`, `last_reviewed`.

### 2. Verified Third-Party Content
Content sourced from reputable external sources, properly attributed and reviewed for applicability.

- **Examples:** Industry association guidelines, government compliance notices, standards documents.
- **Approval authority:** Legal or Compliance Officer, plus Operations Manager sign-off.
- **Required metadata:** `source_url`, `source_date`, `author` (original publisher), `approved: true`.
- **Citation rule:** The original source must be cited in the document. The AI agent may reference "industry guidelines" but must not imply the third party endorses the business.

### Prohibited Source Types
The following may **not** be added to the Knowledge Pack:

- Content from competitors' websites or marketing materials.
- Unverified user-generated content (reviews, forums, social media).
- Legal interpretations or advice not prepared by a qualified solicitor.
- AI-generated content that has not been reviewed and approved by a human.
- Medical, health, or safety claims not backed by a credentialed source.
- Personally identifiable customer data.

---

## Review and Approval Workflow

### New Content Submission

1. **Author** creates the Markdown file with a complete front-matter block (`approved: false` initially).
2. **Author** opens a pull request (PR) in the repository targeting the `main` branch, adding the file under the appropriate `Knowledge/` subdirectory.
3. **Author** completes the Content Quality Checklist (see below) and includes results in the PR description.
4. **Reviewer** (Knowledge Manager or Operations Manager) reviews the PR within 3 business days.
5. If approved, the reviewer:
   - Sets `approved: true` in the file's front matter.
   - Sets `last_reviewed` to today's date.
   - Merges the PR.
6. The nightly indexing job (or a manual trigger) ingests the new content.

### Rejection and Revision
If the reviewer rejects the submission, they must provide written feedback in the PR. The author revises and re-submits. A maximum of **2 revision rounds** is allowed before the submission is escalated to the General Manager for a final decision.

---

## Content Quality Criteria

All submitted content must meet the following criteria before approval:

| Criterion                  | Requirement                                                              |
|----------------------------|--------------------------------------------------------------------------|
| **Accuracy**               | All facts, prices, and rules are verifiable and current                 |
| **Completeness**           | The document covers its topic without requiring the reader to look elsewhere for critical information |
| **Clarity**                | Written in plain English at a Grade 8–10 reading level                  |
| **Scope**                  | Stays within the defined category; no scope creep into other categories |
| **No legal advice**        | Does not contain legal interpretations or advice (compliance notes allowed) |
| **No competitor mentions** | Does not name or compare competitors                                    |
| **Neutral tone**           | Factual, not promotional or persuasive                                  |
| **Formatting**             | Follows the Knowledge Pack formatting conventions (see README)          |
| **YAML front matter**      | Complete and valid; all required fields present                         |

---

## Citation Requirements

### When Citation is Required
Any fact, figure, or rule that originates outside the operator's own policies must be cited. Citing internal documents is encouraged but not required.

### Citation Format (Markdown)
Add a `## Sources` section at the end of the document:

```markdown
## Sources

- Australian Cleaning Industry Award 2020 – https://www.fwc.gov.au (accessed 2024-09-01)
- GBAC STAR Facility Accreditation Standard – https://gbac.issa.com (accessed 2024-08-15)
```

### What the AI Agent May Say
- **May say:** "According to our service guidelines…" / "Our policy states…" / "Based on industry practice…"
- **Must not say:** "According to [external URL]…" or quote URLs to customers.
- **Must not say:** "As required by law…" unless the compliance notes explicitly authorise this phrasing for a specific jurisdiction.

---

## Expiry and Review Schedule

All approved content has an expiry trigger. The indexer logs a warning for overdue reviews, and the BookingAgent is configured to **not surface content** that is more than 12 months past its `last_reviewed` date without a human review.

| Category      | Review Frequency | Expiry Action                                |
|---------------|------------------|----------------------------------------------|
| `pricing`     | Every 3 months   | Set `approved: false`; agent falls back to "contact us for pricing" |
| `policies`    | Every 6 months   | Set `approved: false`; escalate queries to human |
| `guides`      | Every 6 months   | Set `approved: false`; agent uses reduced confidence |
| `compliance`  | Every 12 months  | Set `approved: false`; escalate all compliance queries |
| `faqs`        | Every 3 months   | Set `approved: false`; agent falls back to "let me check for you" |
| `sops`        | Every 6 months   | Internal use only; no agent impact           |
| `contracts`   | Every 12 months  | Internal use only; agent does not quote contracts |

### Automated Expiry Reminders
The system sends an automated email to the `author` listed in the front matter 30 days before the review due date. If not reviewed within 60 days past `last_reviewed + review_frequency`, the file is automatically set to `approved: false` until reviewed.

---

## Responsibility Matrix

| Role                 | Create | Review | Approve | Archive |
|----------------------|--------|--------|---------|---------|
| Operator Staff       | ✅     | ❌     | ❌      | ❌      |
| Knowledge Manager    | ✅     | ✅     | ✅      | ✅      |
| Operations Manager   | ✅     | ✅     | ✅      | ✅      |
| Compliance Officer   | ❌     | ✅     | ✅ (compliance only) | ❌ |
| General Manager      | ✅     | ✅     | ✅      | ✅      |

---

## Policy Violations

Bypassing this approval process (e.g., directly setting `approved: true` without a review, or pushing directly to main) is a policy violation. Violations are logged in the repository audit trail and must be reported to the General Manager. Repeated violations may result in removal of repository access.
