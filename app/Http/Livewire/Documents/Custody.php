<?php

namespace App\Http\Livewire\Documents;

use App\Models\Document;
use App\Models\DocumentMovement;
use App\Models\User;
use App\Services\Documents\DocumentCustodyNotifier;
use App\Services\Documents\DocumentCustodyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Chain of custody page for one document: hand it over, confirm receipt,
 * see who has it and every scanned version. See DocumentCustodyService.
 */
class Custody extends Component
{
    use WithFileUploads;

    public $document_id;

    // Hand-over form
    public $recipient_type = 'user';
    public $to_user_id;
    public $to_name;
    public $from_name;
    public $location;
    public $purpose;
    public $handover_condition = 'Good';
    public $handover_notes;
    public $handover_scan;
    public $handed_over_at;

    // Receipt form
    public $receipt_condition = 'Good';
    public $receipt_notes;
    public $receipt_scan;
    public $received_at;
    public $reject_reason;

    public $rand;

    /** Admin chose to confirm / reject on the recipient's behalf. */
    public $show_on_behalf = false;

    /** Section to jump to when opened from an email / QR link (?action=receive|handover). */
    public $focus;

    public function mount(Document $document): void
    {
        $this->authorizeView($document);
        $this->document_id = $document->id;
        $action = request()->query('action');
        $this->focus = in_array($action, ['receive', 'handover', 'scan'], true) ? $action : null;
        $this->resetForms();
    }

    private function service(): DocumentCustodyService
    {
        return app(DocumentCustodyService::class);
    }

    private function document(): Document
    {
        return Document::with('user')->findOrFail($this->document_id);
    }

    /** Same rule as the documents list: employee files are HR / admin / own only. */
    private function authorizeView(Document $document): void
    {
        abort_unless($this->service()->canView(Auth::user(), $document), 403);
    }

    private function resetForms(): void
    {
        $this->recipient_type = 'user';
        $this->to_user_id = null;
        $this->to_name = null;
        $this->from_name = null;
        $this->location = null;
        $this->purpose = null;
        $this->handover_condition = 'Good';
        $this->handover_notes = null;
        $this->handover_scan = null;
        $this->handed_over_at = now()->format('Y-m-d\TH:i');
        $this->receipt_condition = 'Good';
        $this->receipt_notes = null;
        $this->receipt_scan = null;
        $this->received_at = now()->format('Y-m-d\TH:i');
        $this->reject_reason = null;
        $this->show_on_behalf = false;
        $this->rand = rand();
        $this->resetErrorBag();
    }

    public function handOver($signature = null): void
    {
        $this->validate([
            'recipient_type' => 'required|in:user,external',
            'to_user_id' => 'required_if:recipient_type,user|nullable|integer|exists:users,id',
            'to_name' => 'required_if:recipient_type,external|nullable|string|max:255',
            'from_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:255',
            'handover_condition' => 'required|string|max:50',
            'handover_notes' => 'nullable|string|max:2000',
            'handover_scan' => 'nullable|file|max:10240',
            'handed_over_at' => 'required|date',
        ], [
            'to_user_id.required_if' => 'Select the recipient.',
            'to_name.required_if' => 'Type the recipient\'s name.',
        ]);

        try {
            $movement = $this->service()->handOver($this->document(), Auth::user(), [
                'to_user_id' => $this->recipient_type === 'user' ? $this->to_user_id : null,
                'to_name' => $this->recipient_type === 'external' ? $this->to_name : null,
                'from_name' => $this->from_name,
                'location' => $this->location,
                'purpose' => $this->purpose,
                'condition' => $this->handover_condition,
                'notes' => $this->handover_notes,
                'handed_over_at' => $this->handed_over_at,
                'signature' => $signature,
            ], $this->handover_scan);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            return;
        }

        app(DocumentCustodyNotifier::class)->notify($movement, 'handed_over');

        $this->resetForms();
        $this->dispatchBrowserEvent('custody-saved');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $movement->to_user_id
            ? 'Hand-over recorded. ' . $movement->toLabel() . ' has been emailed to confirm receipt.'
            : 'Hand-over recorded. Awaiting receipt.']);
    }

    public function receive($movementId, $signature = null): void
    {
        $this->validate([
            'receipt_condition' => 'required|string|max:50',
            'receipt_notes' => 'nullable|string|max:2000',
            'receipt_scan' => 'nullable|file|max:10240',
            'received_at' => 'required|date',
        ]);

        $movement = DocumentMovement::where('document_id', $this->document_id)->findOrFail($movementId);

        try {
            $movement = $this->service()->receive($movement, Auth::user(), [
                'condition' => $this->receipt_condition,
                'notes' => $this->receipt_notes,
                'received_at' => $this->received_at,
                'signature' => $signature,
            ], $this->receipt_scan);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            return;
        }

        app(DocumentCustodyNotifier::class)->notify($movement, 'received');
        $this->focus = null;

        $this->resetForms();
        $this->dispatchBrowserEvent('custody-saved');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Receipt confirmed.']);
    }

    public function reject($movementId): void
    {
        $this->validate(['reject_reason' => 'required|string|max:2000'], ['reject_reason.required' => 'Give a reason for rejecting.']);

        $movement = DocumentMovement::where('document_id', $this->document_id)->findOrFail($movementId);

        try {
            $movement = $this->service()->reject($movement, Auth::user(), $this->reject_reason);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            return;
        }

        app(DocumentCustodyNotifier::class)->notify($movement, 'rejected');
        $this->focus = null;

        $this->resetForms();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Hand-over rejected. The document stays with the sender.']);
    }

    public function cancel($movementId): void
    {
        $movement = DocumentMovement::with(['document', 'fromUser', 'toUser', 'user'])
            ->where('document_id', $this->document_id)->findOrFail($movementId);

        try {
            $this->service()->cancel($movement, Auth::user());
        } catch (ValidationException $e) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => collect($e->errors())->flatten()->first()]);
            return;
        }

        // Row is gone; relations were loaded above so the email can still render.
        app(DocumentCustodyNotifier::class)->notify($movement, 'cancelled');

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Hand-over cancelled.']);
    }

    public function render()
    {
        $document = $this->document();
        $service = $this->service();
        $user = Auth::user();
        $pending = $service->pending($document);
        $holder = $service->currentHolder($document);

        return view('livewire.documents.custody', [
            'document' => $document,
            'movements' => $document->movements()->with(['fromUser', 'toUser', 'receiver', 'user'])->orderBy('id')->get(),
            'holder' => $holder,
            'pending' => $pending,
            'versions' => $service->versions($document),
            'canHandOver' => $service->canHandOver($user, $document),
            'canReceive' => $pending && $service->canReceive($user, $pending),
            // Admin who isn't the recipient: allowed, but behind a deliberate toggle
            'receiveOnBehalf' => $pending && $pending->to_user_id && $service->canReceive($user, $pending) && !$service->isRecipient($user, $pending),
            'isSender' => $pending && $service->isSender($user, $pending),
            'canCancel' => $pending && $service->canCancel($user, $pending),
            'conditions' => DocumentMovement::CONDITIONS,
            'users' => $user && !$pending
                ? User::where('id', '!=', (int) $holder['user_id'])
                    ->where(fn ($q) => $q->whereNull('active')->orWhere('active', '!=', 0))
                    ->where(fn ($q) => $q->whereNull('category')->orWhereIn('category', ['employee', 'admin', 'company', 'driver']))
                    ->orderBy('name')->orderBy('surname')
                    ->get(['id', 'name', 'surname'])
                : collect(),
        ]);
    }
}
