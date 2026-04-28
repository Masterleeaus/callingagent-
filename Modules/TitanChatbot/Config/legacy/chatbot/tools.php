<?php

/**
 * Tool registry for the Chatbot workflow engine.
 *
 * Webhook-first (Strategy B): tools are primarily dispatched to the chatbot's
 * configured external tool gateway (ext_chatbots.external_endpoint_url).
 *
 * Tools can be enabled/disabled per chatbot from the dashboard.
 */

return [
    'tools' => [
        'record.create' => [
            'name' => 'Create record',
            'category' => 'records',
            'requires_confirmation' => true,
            'description' => 'Create a business object (lead, ticket, task, booking, etc.) via your external tool gateway.',
            'required_fields' => ['object', 'data'],
        ],
        'record.update' => [
            'name' => 'Update record',
            'category' => 'records',
            'requires_confirmation' => true,
            'description' => 'Update a business object by id/key via your external tool gateway.',
            'required_fields' => ['object', 'id', 'data'],
        ],
        'record.find' => [
            'name' => 'Find record',
            'category' => 'records',
            'requires_confirmation' => false,
            'description' => 'Find/search business objects (customers, invoices, bookings, etc.) via your external tool gateway.',
            'required_fields' => ['object', 'query'],
        ],

        'notify.email' => [
            'name' => 'Send email',
            'category' => 'notifications',
            'requires_confirmation' => true,
            'description' => 'Send an email via your external tool gateway (recommended) or app mailer if implemented downstream.',
            'required_fields' => ['to', 'subject', 'body'],
        ],
        'notify.sms' => [
            'name' => 'Send SMS',
            'category' => 'notifications',
            'requires_confirmation' => true,
            'description' => 'Send an SMS via your external tool gateway (Twilio/MessageBird/etc.).',
            'required_fields' => ['to', 'body'],
        ],
        'notify.channel' => [
            'name' => 'Send channel message',
            'category' => 'notifications',
            'requires_confirmation' => true,
            'description' => 'Send a message back to the current channel (WhatsApp/Telegram/Messenger) via your gateway or native channel senders.',
            'required_fields' => ['body'],
        ],

        'calendar.create' => [
            'name' => 'Create calendar event',
            'category' => 'scheduling',
            'requires_confirmation' => true,
            'description' => 'Create a calendar event via your external tool gateway.',
            'required_fields' => ['title', 'start', 'end', 'timezone'],
        ],

        'file.request' => [
            'name' => 'Request file upload',
            'category' => 'intake',
            'requires_confirmation' => false,
            'description' => 'Ask the user to upload a file (photos, docs). Implemented as a tool-event + UI hint.',
            'required_fields' => ['prompt'],
        ],
    ],
];
