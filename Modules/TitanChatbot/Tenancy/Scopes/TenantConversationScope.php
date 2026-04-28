<?php

namespace Modules\TitanChatbot\Tenancy\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantConversationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = Auth::user()?->company_id ?? 0;

        $builder->where('company_id', $companyId);
    }
}
