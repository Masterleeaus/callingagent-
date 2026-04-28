# TitanChatbot Knowledge Pack

## Overview

The Knowledge Pack is the authoritative content library for the TitanChatbot BookingAgent. It provides structured, operator-curated knowledge that the RAG (Retrieval-Augmented Generation) pipeline indexes and retrieves at runtime to ground AI responses in accurate, business-specific information.

All files in this directory are plain Markdown (`.md`) and are treated as **source-of-truth** for the BookingAgent's answers. The agent never fabricates policy, pricing, or procedure — it retrieves from here.

---

## Directory Layout

```
Knowledge/
├── README.md                          ← This file
├── guides/
│   └── cleaning-estimating-guide.md  ← Estimating formulas & area/room rules
├── policies/
│   ├── service-policy.md              ← Service delivery & cancellation policy
│   └── source-approval-policy.md     ← Rules for approving knowledge sources
├── pricing/
│   └── rate-card.md                   ← Rate card with all pricing tiers
├── faqs/
│   └── cleaning-faqs.md               ← 30+ customer-facing FAQs
├── contracts/
│   └── sample-service-agreement.md   ← Sample service agreement outline
├── compliance/
│   └── compliance-notes.md            ← GDPR/Privacy, AI guardrails, disclaimers
├── sops/
│   └── operator-sop.md                ← Standard operating procedure for cleaners
├── checklists/
│   └── quality-checklist.md           ← Room-by-room quality inspection checklist
└── examples/
    └── sample-booking-conversation.md ← Sample full booking conversation
```

---

## File Format Conventions

### Metadata Header (YAML Front Matter)
Every knowledge file **should** begin with a YAML front-matter block:

```yaml
---
title: "Human-Readable Title"
category: guides | policies | pricing | faqs | contracts | compliance | sops | checklists | examples
version: "1.0"
last_reviewed: "YYYY-MM-DD"
author: "Operator Name or Role"
approved: true
tags: [cleaning, pricing, booking, ...]
---
```

The RAG indexer reads `category`, `tags`, and `title` for metadata filtering. The `approved: true` flag is **required** for a file to be included in live indexing (see `policies/source-approval-policy.md`).

### Section Headings
Use `##` for top-level sections and `###` for sub-sections. The indexer splits documents into chunks at `##` boundaries, so keep sections focused and self-contained.

### Lists and Tables
Prefer Markdown tables for structured data (pricing, formulas) and numbered/bulleted lists for procedures. Avoid deeply nested lists — flatten when possible for cleaner chunk extraction.

### Internal References
Do **not** link to other internal files using absolute paths. Use descriptive cross-references like:
> *See also: Service Policy for cancellation windows.*

The RAG system handles cross-document retrieval; hard links are not needed.

---

## How Knowledge is Indexed

### Indexing Pipeline

1. **Crawl**: The indexer scans all `.md` files under `Knowledge/` recursively.
2. **Filter**: Files with `approved: false` or missing front matter are skipped (logged as warnings).
3. **Chunk**: Each file is split into semantic chunks at `##` heading boundaries (max 512 tokens per chunk). Code blocks and tables are kept intact.
4. **Embed**: Each chunk is vectorised using the configured embedding model (see `Config/ai.php` → `knowledge.embedding_model`).
5. **Store**: Embeddings are stored in the vector store (default: Qdrant collection `titan_knowledge`). Metadata (`category`, `tags`, `source_file`, `version`) is stored alongside each vector for filtered retrieval.
6. **Invalidate**: When a file changes, stale embeddings for that file are deleted and re-indexed automatically on the next indexing run.

### Triggering a Re-Index

```bash
# Re-index all knowledge (run from the Laravel project root)
php artisan titan:knowledge:index --force

# Index a single category
php artisan titan:knowledge:index --category=pricing

# Dry run (shows what would be indexed without writing)
php artisan titan:knowledge:index --dry-run
```

### Retrieval at Runtime

When a customer sends a message, the BookingAgent:

1. Calls the `search_knowledge` tool with the customer's query and an optional `category` filter.
2. The tool performs a cosine-similarity search across the vector store, returning the top-N chunks (default: 3).
3. The agent synthesises a response using the retrieved chunks, citing the **source type** (e.g. "our pricing guide") but never exposing internal file paths.

---

## How to Add New Knowledge

### Step 1 — Create the File
Add a new `.md` file to the appropriate subdirectory. Use the metadata header template above.

### Step 2 — Write Substantive Content
- Be specific. Vague content produces vague agent answers.
- Use concrete numbers, rules, and examples.
- Keep each `##` section focused on a single topic (≤ 400 words per section is ideal).

### Step 3 — Get Approval
Submit the file for review per the `policies/source-approval-policy.md` workflow. Set `approved: true` only after sign-off.

### Step 4 — Re-Index
Run `php artisan titan:knowledge:index` (or wait for the scheduled nightly index run).

### Step 5 — Validate
Run the eval suite to confirm the agent can correctly answer questions based on the new content:

```bash
php artisan titan:eval --suite=Agents/BookingAgent/evaluations/eval-suite.json
```

---

## Versioning and Review Schedule

- All files carry a `version` and `last_reviewed` date in front matter.
- Pricing files (`pricing/`) **must** be reviewed every 3 months.
- Policy files (`policies/`) **must** be reviewed every 6 months or when business rules change.
- Compliance files (`compliance/`) **must** be reviewed annually or when regulations change.
- The `source-approval-policy.md` defines the full review workflow.

---

## Contact

Knowledge pack maintained by the TitanChatbot module team. For questions, open an issue in the repository or contact the operator responsible for each file (see `author` in front matter).
