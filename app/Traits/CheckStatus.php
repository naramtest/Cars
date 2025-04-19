<?php

namespace App\Traits;

trait CheckStatus
{
    public function check($value, $original = null): bool
    {
        $result = $this->isDirty("status") && $this->status === $value;
        if ($original) {
            return $result && $this->getOriginal("status") === $original;
        }
        return $result;
    }
}
