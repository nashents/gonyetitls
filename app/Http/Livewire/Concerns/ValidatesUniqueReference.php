<?php

namespace App\Http\Livewire\Concerns;

use App\Rules\UniqueReference;
use Illuminate\Validation\ValidationException;

/**
 * Blocks saving a record whose user-entered reference is already taken.
 * Shows the field error and a toast (the field may sit on another wizard step).
 */
trait ValidatesUniqueReference
{
    protected function assertUniqueReference(string $field, string $model, string $column, $value, $ignoreId = null, string $label = 'reference'): void
    {
        $rule = new UniqueReference($model, $column, $ignoreId, $label);
        if ($rule->passes($field, $value)) {
            return;
        }

        $message = $rule->message();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $message,
        ]);

        throw ValidationException::withMessages([$field => $message]);
    }

    // Live check as the user types, without the toast.
    protected function checkUniqueReference(string $field, string $model, string $column, $ignoreId = null, string $label = 'reference'): void
    {
        $this->resetErrorBag($field);
        $this->validateOnly($field, [$field => ['nullable', new UniqueReference($model, $column, $ignoreId, $label)]]);
    }
}
