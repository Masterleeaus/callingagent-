<?php

namespace Modules\TitanChatbot\Tenancy\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Modules\TitanChatbot\Models\Chatbot;

class TenantChatbotPolicy
{
    use HandlesAuthorization;

    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, Chatbot $chatbot): bool
    {
        return $this->sameCompany($user, $chatbot);
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, Chatbot $chatbot): bool
    {
        return $this->sameCompany($user, $chatbot);
    }

    public function delete(Authenticatable $user, Chatbot $chatbot): bool
    {
        return $this->sameCompany($user, $chatbot);
    }

    public function forceDelete(Authenticatable $user, Chatbot $chatbot): bool
    {
        return $this->sameCompany($user, $chatbot);
    }

    private function sameCompany(Authenticatable $user, Chatbot $chatbot): bool
    {
        return isset($user->company_id, $chatbot->company_id)
            && (int) $user->company_id === (int) $chatbot->company_id;
    }
}
