<?php

namespace App\Traits;

trait CheckStatus
{
    public function check($value, $original = null): bool
    {
        return $this->isDirty("status") &&
            $this->status === $value &&
            $this->getOriginal("status") !== $original ?? $value;
    }
}
