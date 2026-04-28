<?php

namespace Modules\TitanChatbot\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chatbot extends Model
{
    protected $table = 'ext_chatbots';

    protected $fillable = [
        'title',
        'user_id',
        'bubble_message',
        'welcome_message',
        'connect_message',
        'instructions',
        'do_not_go_beyond_instructions',
        'language',
        'ai_model',
        'ai_embedding_model',
        'limit_per_minute',
        'show_pre_defined_questions',
        'pre_defined_questions',
        'logo',
        'avatar',
        'trigger_avatar_size',
        'trigger_background',
        'trigger_foreground',
        'color_mode',
        'color',
        'show_logo',
        'show_date_and_time',
        'show_average_response_time',
        'position',
        'active',
        'footer_link',
        'is_demo',
        'is_favorite',
        'whatsapp_link',
        'telegram_link',
        'watch_product_tour_link',
        'is_email_collect',
        'is_contact',
        'is_attachment',
        'is_emoji',
        'is_articles',
        'is_links',
        'header_bg_type',
        'header_bg_color',
        'header_bg_gradient',
        'header_bg_image',
        'human_agent_conditions',
        'interaction_type',
        'company_id',
        'chatbot_channel_id',
        'provider_type',
        'external_endpoint_url',
        'external_auth_type',
        'external_auth_token',
        'external_signing_secret',
        'external_timeout_ms',
    ];

    protected $casts = [
        'pre_defined_questions'   => 'array',
        'human_agent_conditions'  => 'array',
        'do_not_go_beyond_instructions' => 'boolean',
        'show_pre_defined_questions'    => 'boolean',
        'show_logo'                     => 'boolean',
        'show_date_and_time'            => 'boolean',
        'show_average_response_time'    => 'boolean',
        'active'                        => 'boolean',
        'is_demo'                       => 'boolean',
        'is_favorite'                   => 'boolean',
        'is_email_collect'              => 'boolean',
        'is_contact'                    => 'boolean',
        'is_attachment'                 => 'boolean',
        'is_emoji'                      => 'boolean',
        'is_articles'                   => 'boolean',
        'is_links'                      => 'boolean',
        'limit_per_minute'              => 'integer',
        'external_timeout_ms'           => 'integer',
        'created_at'                    => 'datetime',
        'updated_at'                    => 'datetime',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'chatbot_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ChatbotHistory::class, 'chatbot_id');
    }

    public function embeddings(): HasMany
    {
        return $this->hasMany(ChatbotEmbedding::class, 'chatbot_id');
    }

    public function channels(): HasMany
    {
        return $this->hasMany(ChatbotChannel::class, 'chatbot_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
