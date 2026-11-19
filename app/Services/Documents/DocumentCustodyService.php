<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentMovement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Chain of custody for a Document - replaces the paper receipt book that
 * permits etc. are signed in/out of as they pass from desk to desk.
 *
 *  handOver()  giver signs the document out to the next person (pending)
 *  receive()   recipient confirms receipt + condition (+ new scan / stamps)
 *  reject()    recipient refuses it - custody stays with the giver
 *  cancel()    giver withdraws a hand-over that has not been received yet
 *
 * Only one hand-over can be open per document. Recipients can be system
 * users (they confirm receipt themselves) or external parties typed by
 * name (receipt is captured on their behalf, e.g. from the signed slip).
 */
class DocumentCustodyService
{
    public const DIR = 'documents';

    public function pending(Document $document): ?DocumentMovement
    {
        return $document->movements()->where('status', DocumentMovement::PENDING)->latest('id')->first();
    }

    /**
     * Who holds the document now: ['user_id' => ?int, 'name' => string, 'location' => ?string, 'in_transit' => bool].
     */
    public function currentHolder(Document $document): array
    {
        $last = $document->movements()
            ->whereIn('status', [DocumentMovement::RECEIVED, DocumentMovement::PENDING])
            ->with(['fromUser', 'toUser'])
            ->latest('id')
            ->first();

        if (!$last) {
            $uploader = $document->user;
            return [
                'user_id' => $document->user_id,
                'name' => $uploader ? trim($uploader->name . ' ' . $uploader->surname) : 'Uploader',
                'location' => null,
                'in_transit' => false,
            ];
        }

        if ($last->status === DocumentMovement::PENDING) {
            // Until the recipient signs for it, the giver is still accountable.
            // $last->location is where it is GOING; the document is still
            // where it was last received.
            $previous = $document->movements()
                ->where('status', DocumentMovement::RECEIVED)
                ->where('id', '<', $last->id)
                ->latest('id')
                ->first();

            return [
                'user_id' => $last->from_user_id,
                'name' => $last->fromLabel(),
                'location' => optional($previous)->location,
                'in_transit' => true,
                'to' => $last->toLabel(),
                'to_location' => $last->location,
            ];
        }

        return [
            'user_id' => $last->to_user_id,
            'name' => $last->toLabel(),
            'location' => $last->location,
            'in_transit' => false,
        ];
    }

    /** Same rule as the documents list: employee files are HR / admin / own / involved only. */
    public function canView(User $user, Document $document): bool
    {
        if ($document->category !== 'employee' || $this->isAdmin($user)) {
            return true;
        }

        $roles = $user->roles->pluck('name')->toArray();
        $departments = optional($user->employee)->departments ? $user->employee->departments->pluck('name')->toArray() : [];
        $involved = $document->movements()->where(fn ($q) => $q->where('to_user_id', $user->id)->orWhere('from_user_id', $user->id))->exists();

        return in_array('Admin', $roles) || in_array('Human Resource', $departments)
            || (int) $document->employee_id === (int) optional($user->employee)->id || $involved;
    }

    public function isAdmin(User $user): bool
    {
        return $user->is_admin() || $user->isSuperAdmin();
    }

    public function canHandOver(User $user, Document $document): bool
    {
        if ($this->pending($document)) {
            return false;
        }
        if ($this->isAdmin($user)) {
            return true;
        }

        $holder = $this->currentHolder($document);

        // Held by a system user: only that user signs it out. Held by an
        // external party (no user): whoever receives it back records it.
        return $holder['user_id'] ? (int) $holder['user_id'] === (int) $user->id : true;
    }

    public function canReceive(User $user, DocumentMovement $movement): bool
    {
        if (!$movement->isPending()) {
            return false;
        }

        // External recipient (no login): someone records the receipt for them,
        // e.g. from the signed slip - the sender included.
        if (!$movement->to_user_id) {
            return true;
        }

        if ($this->isRecipient($user, $movement)) {
            return true;
        }

        // Admin acting on the recipient's behalf - but never the sender:
        // signing for your own hand-over defeats the receipt.
        return $this->isAdmin($user) && !$this->isSender($user, $movement);
    }

    public function isRecipient(User $user, DocumentMovement $movement): bool
    {
        return $movement->to_user_id && (int) $movement->to_user_id === (int) $user->id;
    }

    /** Whoever the document left from, or who recorded the hand-over. */
    public function isSender(User $user, DocumentMovement $movement): bool
    {
        return ($movement->from_user_id && (int) $movement->from_user_id === (int) $user->id)
            || ($movement->user_id && (int) $movement->user_id === (int) $user->id);
    }

    public function canCancel(User $user, DocumentMovement $movement): bool
    {
        return $movement->isPending() && (
            (int) $movement->user_id === (int) $user->id
            || ($movement->from_user_id && (int) $movement->from_user_id === (int) $user->id)
            || $this->isAdmin($user)
        );
    }

    public function handOver(Document $document, User $actor, array $data, ?UploadedFile $scan = null): DocumentMovement
    {
        return DB::transaction(function () use ($document, $actor, $data, $scan) {
            Document::whereKey($document->id)->lockForUpdate()->first();

            if (!$this->canHandOver($actor, $document)) {
                throw ValidationException::withMessages([
                    'to_user_id' => $this->pending($document)
                        ? 'This document already has a hand-over awaiting receipt.'
                        : 'Only the current holder of this document can hand it over.',
                ]);
            }

            if (empty($data['to_user_id']) && empty($data['to_name'])) {
                throw ValidationException::withMessages(['to_user_id' => 'Select the recipient or type their name.']);
            }
            $holder = $this->currentHolder($document);

            if (!empty($data['to_user_id']) && $holder['user_id'] && (int) $data['to_user_id'] === (int) $holder['user_id']) {
                throw ValidationException::withMessages(['to_user_id' => 'The recipient already holds this document.']);
            }
            // Admin acting for someone else, or an external holder: record the
            // real giver, not the person typing.
            $fromUserId = $holder['user_id'] ?: null;
            $fromName = $fromUserId ? null : (($data['from_name'] ?? null) ?: $holder['name']);

            return DocumentMovement::create([
                'document_id' => $document->id,
                'status' => DocumentMovement::PENDING,
                'from_user_id' => $fromUserId,
                'from_name' => $fromName,
                'handed_over_at' => $data['handed_over_at'] ?? now(),
                'handover_condition' => $data['condition'] ?? null,
                'handover_notes' => $data['notes'] ?? null,
                'handover_file' => $scan ? $this->storeScan($scan, $document) : null,
                'handover_signature' => $this->cleanSignature($data['signature'] ?? null),
                'to_user_id' => ($data['to_user_id'] ?? null) ?: null,
                'to_name' => !empty($data['to_user_id']) ? null : ($data['to_name'] ?? null),
                'location' => $data['location'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'user_id' => $actor->id,
            ]);
        });
    }

    public function receive(DocumentMovement $movement, User $actor, array $data, ?UploadedFile $scan = null): DocumentMovement
    {
        return DB::transaction(function () use ($movement, $actor, $data, $scan) {
            $movement = DocumentMovement::whereKey($movement->id)->lockForUpdate()->firstOrFail();

            if (!$this->canReceive($actor, $movement)) {
                throw ValidationException::withMessages(['receipt_condition' => $this->isSender($actor, $movement)
                    ? 'You handed this document over, so ' . $movement->toLabel() . ' must confirm receipt.'
                    : 'You cannot confirm receipt of this hand-over.']);
            }

            $document = $movement->document;
            $receiptFile = $scan ? $this->storeScan($scan, $document) : null;
            // A hand-over scan nobody re-scanned on receipt is still the latest version.
            $newFile = $receiptFile ?: $movement->handover_file;

            $movement->update([
                'status' => DocumentMovement::RECEIVED,
                'received_at' => $data['received_at'] ?? now(),
                'received_by' => $actor->id,
                'receipt_condition' => $data['condition'] ?? null,
                'receipt_notes' => $data['notes'] ?? null,
                'receipt_file' => $receiptFile,
                'receipt_signature' => $this->cleanSignature($data['signature'] ?? null),
                'replaced_filename' => $newFile ? $document->filename : null,
            ]);

            // The newest scan becomes the document's current file; earlier
            // files stay on the movement rows as the version history.
            if ($newFile) {
                $document->forceFill(['filename' => $newFile])->save();
            }

            return $movement;
        });
    }

    public function reject(DocumentMovement $movement, User $actor, ?string $reason): DocumentMovement
    {
        return DB::transaction(function () use ($movement, $actor, $reason) {
            $movement = DocumentMovement::whereKey($movement->id)->lockForUpdate()->firstOrFail();

            if (!$this->canReceive($actor, $movement)) {
                throw ValidationException::withMessages(['receipt_notes' => 'You cannot reject this hand-over.']);
            }

            $movement->update([
                'status' => DocumentMovement::REJECTED,
                'received_at' => now(),
                'received_by' => $actor->id,
                'receipt_notes' => $reason,
            ]);

            return $movement;
        });
    }

    public function cancel(DocumentMovement $movement, User $actor): void
    {
        if (!$this->canCancel($actor, $movement)) {
            throw ValidationException::withMessages(['cancel' => 'You cannot cancel this hand-over.']);
        }

        $movement->delete();
    }

    /**
     * Every file this document has had, oldest first: the original upload
     * then each scan captured on hand-over / receipt.
     */
    public function versions(Document $document): array
    {
        $versions = [];
        $movements = $document->movements()->with(['fromUser', 'toUser'])->orderBy('id')->get();
        $original = optional($movements->firstWhere('replaced_filename', '!=', null))->replaced_filename
            ?? $document->filename;

        if ($original) {
            $versions[] = ['file' => $original, 'label' => 'Original upload', 'at' => $document->created_at, 'by' => optional($document->user)->name];
        }

        foreach ($movements as $m) {
            if ($m->handover_file) {
                $versions[] = ['file' => $m->handover_file, 'label' => 'Handed over by ' . $m->fromLabel(), 'at' => $m->handed_over_at, 'by' => $m->fromLabel()];
            }
            if ($m->receipt_file) {
                $versions[] = ['file' => $m->receipt_file, 'label' => 'Received by ' . $m->toLabel(), 'at' => $m->received_at, 'by' => $m->toLabel()];
            }
        }

        return $versions;
    }

    /** Pending hand-overs addressed to this user. */
    public function awaitingCount(User $user): int
    {
        return DocumentMovement::where('status', DocumentMovement::PENDING)
            ->where('to_user_id', $user->id)
            ->whereHas('document')
            ->count();
    }

    private function storeScan(UploadedFile $file, Document $document): string
    {
        $base = preg_replace('/[^A-Za-z0-9_\-]+/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $name = $base . '_v' . ($document->movements()->count() + 1) . '_' . time() . '.' . strtolower($file->getClientOriginalExtension());
        $file->storeAs('/' . self::DIR, $name, 'my_files');

        return $name;
    }

    private function cleanSignature(?string $dataUrl): ?string
    {
        if (!$dataUrl || !str_starts_with($dataUrl, 'data:image/png;base64,') || strlen($dataUrl) > 2_000_000) {
            return null;
        }

        return $dataUrl;
    }
}
