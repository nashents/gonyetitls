<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Employee;
use App\Models\Fitness;
use App\Models\ReminderCopy;
use Illuminate\Support\Facades\Auth;

/**
 * Copy-list picker inside the reminder forms (record Reminders tab, main
 * Reminders screen, reminder detail page): see the logged-in user's copy
 * list, add / activate / remove people on the spot, and choose who gets this
 * reminder - nobody, everyone, or only ticked people.
 *
 * Views: livewire.fitnesses.partials.copy-list (needs $copyList).
 * Save: call applyCopySelection($fitness) after the reminder is saved; it
 * sets cc and the picked people. Uses Fitness::selectedCopies().
 */
trait ManagesReminderCopies
{
    /** none | all | selected */
    public $copy_mode = 'none';
    /** reminder_copy ids ticked in "selected" mode */
    public $selected_copies = [];

    public $show_add_copy = false;
    public $new_copy_employee_id;
    public $new_copy_name;
    public $new_copy_surname;
    public $new_copy_email;

    protected function copyList()
    {
        return ReminderCopy::where('user_id', Auth::id())
            ->orderByDesc('status')->orderBy('name')->orderBy('surname')
            ->get();
    }

    /** Employees with an email, for the quick-add picker (only when open). */
    protected function copyEmployeeOptions()
    {
        return $this->show_add_copy
            ? Employee::whereNotNull('email')->where('email', '!=', '')->where('archive', 0)
                ->orderBy('name')->orderBy('surname')->get(['id', 'name', 'surname', 'email'])
            : collect();
    }

    public function resetCopySelection(): void
    {
        $this->copy_mode = 'none';
        $this->selected_copies = [];
        $this->resetNewCopy();
        $this->show_add_copy = false;
    }

    private function resetNewCopy(): void
    {
        $this->new_copy_employee_id = null;
        $this->new_copy_name = null;
        $this->new_copy_surname = null;
        $this->new_copy_email = null;
    }

    public function loadCopySelection(Fitness $fitness): void
    {
        $ids = $fitness->selectedCopies()->pluck('reminder_copies.id')->map(fn ($id) => (string) $id)->all();
        $this->selected_copies = $ids;
        $this->copy_mode = !$fitness->cc ? 'none' : ($ids ? 'selected' : 'all');
    }

    /** Persist cc + picked people on a saved reminder. */
    public function applyCopySelection(Fitness $fitness): void
    {
        $mode = in_array($this->copy_mode, ['none', 'all', 'selected'], true) ? $this->copy_mode : 'none';

        // Only the acting user's own copies can be picked.
        $ids = $mode === 'selected'
            ? ReminderCopy::where('user_id', Auth::id())->whereIn('id', (array) $this->selected_copies)->pluck('id')->all()
            : [];

        // "Only these people" with nobody ticked = nobody.
        $cc = $mode === 'all' || ($mode === 'selected' && $ids);

        $fitness->forceFill(['cc' => $cc])->save();
        $fitness->selectedCopies()->sync($ids);
    }

    public function updatedNewCopyEmployeeId($id): void
    {
        $employee = $id ? Employee::find($id) : null;
        if ($employee) {
            $this->new_copy_name = $employee->name;
            $this->new_copy_surname = $employee->surname;
            $this->new_copy_email = $employee->email;
        }
    }

    public function toggleAddCopy(): void
    {
        $this->show_add_copy = !$this->show_add_copy;
        $this->resetErrorBag(['new_copy_name', 'new_copy_email']);
    }

    public function addCopy(): void
    {
        $this->validate([
            'new_copy_name' => 'required|string|max:255',
            'new_copy_surname' => 'nullable|string|max:255',
            'new_copy_email' => 'required|email|max:255',
        ], [
            'new_copy_name.required' => 'Name is required.',
            'new_copy_email.required' => 'Email is required.',
            'new_copy_email.email' => 'Enter a valid email.',
        ]);

        $email = strtolower(trim($this->new_copy_email));
        $existing = ReminderCopy::where('user_id', Auth::id())->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($existing) {
            $existing->update(['status' => true]);
            $copy = $existing;
        } else {
            $copy = ReminderCopy::create([
                'user_id' => Auth::id(),
                'employee_id' => $this->new_copy_employee_id ?: null,
                'name' => trim($this->new_copy_name),
                'surname' => trim((string) $this->new_copy_surname),
                'email' => $email,
                'status' => true,
            ]);
        }

        // Someone just added is almost always meant for this reminder.
        if ($this->copy_mode === 'none') {
            $this->copy_mode = 'selected';
        }
        if ($this->copy_mode === 'selected' && !in_array((string) $copy->id, array_map('strval', (array) $this->selected_copies), true)) {
            $this->selected_copies[] = (string) $copy->id;
        }

        $this->resetNewCopy();
        $this->show_add_copy = false;
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $existing ? "{$copy->email} is already on your copy list (now active)." : "{$copy->name} added to your copy list.",
        ]);
    }

    public function toggleCopyActive($id): void
    {
        $copy = ReminderCopy::where('user_id', Auth::id())->find($id);
        if ($copy) {
            $copy->update(['status' => !$copy->status]);
        }
    }

    public function removeCopy($id): void
    {
        $copy = ReminderCopy::where('user_id', Auth::id())->find($id);
        if (!$copy) {
            return;
        }
        $copy->delete();
        $this->selected_copies = array_values(array_filter((array) $this->selected_copies, fn ($v) => (string) $v !== (string) $id));
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => "{$copy->email} removed from your copy list."]);
    }

    public function selectAllCopies(): void
    {
        $this->selected_copies = $this->copyList()->where('status', true)->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function clearSelectedCopies(): void
    {
        $this->selected_copies = [];
    }
}
