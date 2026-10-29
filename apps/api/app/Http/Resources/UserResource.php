<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\CurrentFarm;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $firstName = $parts[0] ?? '';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'name' => $this->name,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'initials' => Str::upper(Str::substr($firstName, 0, 1).Str::substr($lastName, 0, 1)),
            'email' => $this->email,
            'role' => $this->activeRole(),
        ];
    }

    // Role on the active farm, normalised to owner | worker for the frontend.
    private function activeRole(): ?string
    {
        $farm = app(CurrentFarm::class);
        if (! $farm->isSet()) {
            return null;
        }
        $role = $this->roleOn($farm->id());

        return $role === 'owner' ? 'owner' : ($role ? 'worker' : null);
    }
}
