<?php

namespace App\Livewire\Concerns;

/**
 * Crafted Livewire requests can post arrays (even nested ones) into public
 * properties. Components keep those properties untyped (a typed `string`
 * would throw a TypeError / 500 before validation runs) and call toText()
 * from their updated* hooks so validation sees a plain string.
 */
trait CoercesTextInput
{
    /** First scalar of a (possibly nested) array, as a string. */
    protected static function toText(mixed $value, string $default = ''): string
    {
        while (is_array($value)) {
            $value = reset($value);
        }

        return is_scalar($value) ? (string) $value : $default;
    }
}
