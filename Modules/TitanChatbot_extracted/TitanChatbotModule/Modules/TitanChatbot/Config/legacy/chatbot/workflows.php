<?php

/**
 * Chatbot Workflows Registry (Horizontal / Multi-tenant)
 *
 * Strategy B (locked): workflows execute via a webhook "tool gateway".
 * The chatbot NEVER directly mutates business objects.
 */

return [
    'tools' => [
        // Webhook-first record operations
        'record.create',
        'record.update',
        'record.find',

        // Notifications (typically implemented via gateway webhook as well)
        'notify.email',
        'notify.sms',
        'notify.channel',

        // Generic integrations
        'calendar.create',
        'webhook.call',
        'file.request',
        'audit.log',
    ],

    /**
     * Workflows are enabled per chatbot (DB). If no DB row exists, default_enabled applies.
     */
    'workflows' => [
        // Core conversation & control
        'human_handoff' => [
            'category' => 'conversation',
            'title' => 'Human handoff',
            'description' => 'Escalate the conversation to a human agent and create/flag a ticket.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['reason'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'human_handoff'],
                ['tool' => 'audit.log'],
            ],
        ],
        'conversation_tagging' => [
            'category' => 'conversation',
            'title' => 'Conversation tagging',
            'description' => 'Apply tags/labels to a conversation for reporting and routing.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['tags'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'conversation_tagging'],
                ['tool' => 'audit.log'],
            ],
        ],
        'priority_escalation' => [
            'category' => 'conversation',
            'title' => 'Priority escalation',
            'description' => 'Mark the conversation high priority and alert staff.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['priority'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'priority_escalation'],
                ['tool' => 'audit.log'],
            ],
        ],
        'conversation_summary' => [
            'category' => 'conversation',
            'title' => 'Conversation summary',
            'description' => 'Generate and store a summary of the conversation.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => [],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'conversation_summary'],
                ['tool' => 'audit.log'],
            ],
        ],
        'transcript_export' => [
            'category' => 'conversation',
            'title' => 'Transcript export',
            'description' => 'Export and deliver a conversation transcript (optionally redacted).',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['delivery'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'transcript_export'],
                ['tool' => 'audit.log'],
            ],
        ],

        // Identity, leads, onboarding
        'lead_capture' => [
            'category' => 'identity',
            'title' => 'Lead capture',
            'description' => 'Capture contact details and create a lead in the host SaaS.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['name', 'email_or_phone'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'lead'],
                ['tool' => 'audit.log'],
            ],
        ],
        'lead_qualification' => [
            'category' => 'identity',
            'title' => 'Lead qualification',
            'description' => 'Ask qualification questions and score/rout the lead.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['qualification'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'lead_qualification'],
                ['tool' => 'audit.log'],
            ],
        ],
        'account_signup_request' => [
            'category' => 'identity',
            'title' => 'Account signup request',
            'description' => 'Create a pending account request (or start signup flow).',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['email'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'account_request'],
                ['tool' => 'audit.log'],
            ],
        ],
        'onboarding_sequence_start' => [
            'category' => 'identity',
            'title' => 'Onboarding sequence start',
            'description' => 'Trigger onboarding sequence / checklist for a user or org.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['user_identifier'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'onboarding_sequence_start'],
                ['tool' => 'audit.log'],
            ],
        ],
        'profile_update_request' => [
            'category' => 'identity',
            'title' => 'Profile update request',
            'description' => 'Update user profile fields (webhook-first).',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['changes'],
            'steps' => [
                ['tool' => 'record.update', 'action' => 'profile'],
                ['tool' => 'audit.log'],
            ],
        ],

        // Scheduling
        'appointment_book' => [
            'category' => 'scheduling',
            'title' => 'Appointment booking',
            'description' => 'Book an appointment (demo, consult, visit, etc.).',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['datetime', 'timezone'],
            'steps' => [
                ['tool' => 'calendar.create', 'action' => 'appointment'],
                ['tool' => 'audit.log'],
            ],
        ],
        'appointment_reschedule' => [
            'category' => 'scheduling',
            'title' => 'Appointment reschedule',
            'description' => 'Reschedule an existing appointment.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['appointment_id', 'new_datetime', 'timezone'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'appointment_reschedule'],
                ['tool' => 'audit.log'],
            ],
        ],
        'appointment_cancel' => [
            'category' => 'scheduling',
            'title' => 'Appointment cancellation',
            'description' => 'Cancel an appointment and notify parties.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['appointment_id'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'appointment_cancel'],
                ['tool' => 'audit.log'],
            ],
        ],
        'callback_request' => [
            'category' => 'scheduling',
            'title' => 'Callback request',
            'description' => 'Create a callback request with preferred times.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['preferred_times'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'callback_request'],
                ['tool' => 'audit.log'],
            ],
        ],
        'reminder_schedule' => [
            'category' => 'scheduling',
            'title' => 'Reminder scheduling',
            'description' => 'Schedule reminders (email/SMS/channel) for a user/event.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['reminder'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'reminder_schedule'],
                ['tool' => 'audit.log'],
            ],
        ],

        // Tasks & operations
        'task_create' => [
            'category' => 'operations',
            'title' => 'Task creation',
            'description' => 'Create a task/work item.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['task'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'task'],
                ['tool' => 'audit.log'],
            ],
        ],
        'task_assign' => [
            'category' => 'operations',
            'title' => 'Task assignment',
            'description' => 'Assign a task to a user/team.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['task_id', 'assignee'],
            'steps' => [
                ['tool' => 'record.update', 'action' => 'task_assign'],
                ['tool' => 'audit.log'],
            ],
        ],
        'task_status_update' => [
            'category' => 'operations',
            'title' => 'Task status update',
            'description' => 'Update task status/progress.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['task_id', 'status'],
            'steps' => [
                ['tool' => 'record.update', 'action' => 'task_status'],
                ['tool' => 'audit.log'],
            ],
        ],
        'file_request_intake' => [
            'category' => 'operations',
            'title' => 'File request intake',
            'description' => 'Request files from the user and attach them to a record.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['file_request'],
            'steps' => [
                ['tool' => 'file.request', 'action' => 'file_request'],
                ['tool' => 'audit.log'],
            ],
        ],
        'internal_note_add' => [
            'category' => 'operations',
            'title' => 'Internal note add',
            'description' => 'Create an internal note linked to a conversation/record.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['note'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'internal_note'],
                ['tool' => 'audit.log'],
            ],
        ],

        // Support & tickets
        'ticket_create' => [
            'category' => 'support',
            'title' => 'Ticket creation',
            'description' => 'Create a support ticket.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['summary'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'ticket'],
                ['tool' => 'audit.log'],
            ],
        ],
        'ticket_assign' => [
            'category' => 'support',
            'title' => 'Ticket assignment',
            'description' => 'Assign an existing ticket.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['ticket_id', 'assignee'],
            'steps' => [
                ['tool' => 'record.update', 'action' => 'ticket_assign'],
                ['tool' => 'audit.log'],
            ],
        ],
        'ticket_status_update' => [
            'category' => 'support',
            'title' => 'Ticket status update',
            'description' => 'Update ticket status.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['ticket_id', 'status'],
            'steps' => [
                ['tool' => 'record.update', 'action' => 'ticket_status'],
                ['tool' => 'audit.log'],
            ],
        ],
        'faq_answer_or_escalate' => [
            'category' => 'support',
            'title' => 'FAQ answer or escalate',
            'description' => 'Answer using knowledge base; if insufficient, create ticket.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => [],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'faq_answer_or_escalate'],
                ['tool' => 'audit.log'],
            ],
        ],
        'sla_breach_alert' => [
            'category' => 'support',
            'title' => 'SLA breach alert',
            'description' => 'Alert internal team when SLA is breached or at risk.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['sla'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'sla_breach_alert'],
                ['tool' => 'audit.log'],
            ],
        ],

        // Billing & subscription
        'invoice_request' => [
            'category' => 'billing',
            'title' => 'Invoice request',
            'description' => 'Request or generate an invoice.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['invoice_context'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'invoice_request'],
                ['tool' => 'audit.log'],
            ],
        ],
        'payment_link_send' => [
            'category' => 'billing',
            'title' => 'Payment link send',
            'description' => 'Send a payment link to a customer.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['invoice_id'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'payment_link_send'],
                ['tool' => 'audit.log'],
            ],
        ],
        'payment_reminder' => [
            'category' => 'billing',
            'title' => 'Payment reminder',
            'description' => 'Send a payment reminder.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['invoice_id'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'payment_reminder'],
                ['tool' => 'audit.log'],
            ],
        ],
        'refund_request' => [
            'category' => 'billing',
            'title' => 'Refund request',
            'description' => 'Create a refund request for review.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['reason'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'refund_request'],
                ['tool' => 'audit.log'],
            ],
        ],
        'subscription_change_request' => [
            'category' => 'billing',
            'title' => 'Subscription change request',
            'description' => 'Request a subscription change (upgrade/downgrade/pause/cancel).',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['change'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'subscription_change_request'],
                ['tool' => 'audit.log'],
            ],
        ],

        // Compliance & privacy
        'identity_verification_request' => [
            'category' => 'compliance',
            'title' => 'Identity verification request',
            'description' => 'Start identity verification process.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['verification'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'identity_verification_request'],
                ['tool' => 'audit.log'],
            ],
        ],
        'consent_capture' => [
            'category' => 'compliance',
            'title' => 'Consent capture',
            'description' => 'Capture consent acknowledgement with timestamp.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['consent'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'consent_capture'],
                ['tool' => 'audit.log'],
            ],
        ],
        'data_access_request' => [
            'category' => 'compliance',
            'title' => 'Data access request',
            'description' => 'Create a privacy request for data access/export.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['identity'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'data_access_request'],
                ['tool' => 'audit.log'],
            ],
        ],
        'data_deletion_request' => [
            'category' => 'compliance',
            'title' => 'Data deletion request',
            'description' => 'Create a privacy request for data deletion.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['identity'],
            'steps' => [
                ['tool' => 'record.create', 'action' => 'data_deletion_request'],
                ['tool' => 'audit.log'],
            ],
        ],
        'policy_acknowledgement' => [
            'category' => 'compliance',
            'title' => 'Policy acknowledgement',
            'description' => 'Record acknowledgement of terms/privacy/policy.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['policy'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'policy_acknowledgement'],
                ['tool' => 'audit.log'],
            ],
        ],

        // Integrations & automation
        'webhook_dispatch' => [
            'category' => 'integration',
            'title' => 'Webhook dispatch',
            'description' => 'Send a structured payload to an external system.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['payload'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'webhook_dispatch'],
                ['tool' => 'audit.log'],
            ],
        ],
        'crm_sync' => [
            'category' => 'integration',
            'title' => 'CRM sync',
            'description' => 'Sync customer/lead/ticket info to CRM.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['entity'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'crm_sync'],
                ['tool' => 'audit.log'],
            ],
        ],
        'spreadsheet_update' => [
            'category' => 'integration',
            'title' => 'Spreadsheet update',
            'description' => 'Append/update a spreadsheet row.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['sheet'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'spreadsheet_update'],
                ['tool' => 'audit.log'],
            ],
        ],
        'internal_alert' => [
            'category' => 'integration',
            'title' => 'Internal alert',
            'description' => 'Notify internal team via preferred channel.',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['message'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'internal_alert'],
                ['tool' => 'audit.log'],
            ],
        ],
        'workflow_chain_trigger' => [
            'category' => 'integration',
            'title' => 'Workflow chain trigger',
            'description' => 'Trigger a follow-on workflow (chaining).',
            'requires_confirmation' => true,
            'default_enabled' => true,
            'required_fields' => ['next_workflow_key'],
            'steps' => [
                ['tool' => 'webhook.call', 'action' => 'workflow_chain_trigger'],
                ['tool' => 'audit.log'],
            ],
        ],
    ],
];
