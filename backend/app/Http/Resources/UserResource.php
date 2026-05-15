<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'role' => $this->getRoleNames()->first(),
            'plan_type' => $this->plan_type,
            'subscription_status' => $this->subscription_status,
            'last_login_at' => $this->last_login_at,
            'country' => $this->country,
            'created_at' => $this->created_at,
        ];
    }
}
