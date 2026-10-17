<?php

namespace App\Support;

use RuntimeException;

// Holds the active farm id for the current request. Bound as a singleton
// (container resolves one instance per request). ResolveFarm middleware sets it
// from the authenticated user's farm membership.
class CurrentFarm
{
    private ?int $id = null;

    public function set(?int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function isSet(): bool
    {
        return $this->id !== null;
    }

    public function require(): int
    {
        if ($this->id === null) {
            throw new RuntimeException('No active farm set for this request.');
        }

        return $this->id;
    }
}
