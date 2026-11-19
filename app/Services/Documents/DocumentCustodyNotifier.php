<?php

namespace App\Services\Documents;

use App\Mail\DocumentCustodyMail;
use App\Models\Company;
use App\Models\DocumentMovement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails for document hand-overs. Each email carries a button that opens
 * the document's custody page (after login) on the right action.
 *
 *  handed_over → recipient          "Confirm receipt"
 *  received    → sender (+ capturer) "View custody log"
 *  rejected    → sender (+ capturer) "View custody log"
 *  cancelled   → recipient          "View document"
 *
 * Sent after the response is returned (no queue worker in this app), and a
 * mail failure never undoes the hand-over itself - it is only logged.
 */
class DocumentCustodyNotifier
{
    public function notify(DocumentMovement $movement, string $event): void
    {
        $movement->loadMissing(['document', 'fromUser', 'toUser', 'user']);
        $actor = Auth::user();
        $company = optional(optional($actor)->employee)->company ?? Company::first();

        // Build URLs now, while the request (and its host) is still available.
        $custodyUrl = route('documents.custody', $movement->document_id);
        $messages = [];

        foreach ($this->recipients($movement, $event, $actor) as $user) {
            [$headline, $url, $label] = match ($event) {
                'handed_over' => ['Document handed over to you', $custodyUrl . '?action=receive', 'Confirm receipt'],
                'received' => ['Document received', $custodyUrl, 'View custody log'],
                'rejected' => ['Document hand-over rejected', $custodyUrl, 'View custody log'],
                'cancelled' => ['Document hand-over cancelled', $custodyUrl, 'View document'],
            };

            $messages[] = [$user->email, new DocumentCustodyMail(
                $company, $movement, $event, $headline, $url, $label, trim($user->name . ' ' . $user->surname)
            )];
        }

        if (!$messages) {
            return;
        }

        dispatch(function () use ($messages, $movement, $event) {
            foreach ($messages as [$email, $mail]) {
                try {
                    Mail::to($email)->send($mail);
                } catch (\Throwable $e) {
                    Log::warning('Document custody email failed', [
                        'movement_id' => $movement->id, 'event' => $event, 'email' => $email, 'error' => $e->getMessage(),
                    ]);
                }
            }
        })->afterResponse();
    }

    /** @return User[] */
    private function recipients(DocumentMovement $movement, string $event, ?User $actor): array
    {
        $users = in_array($event, ['handed_over', 'cancelled'], true)
            ? [$movement->toUser]
            : [$movement->fromUser, $movement->user];

        return collect($users)
            ->filter(fn ($u) => $u && $u->email && filter_var($u->email, FILTER_VALIDATE_EMAIL))
            ->reject(fn ($u) => $actor && (int) $u->id === (int) $actor->id)   // don't email yourself
            ->unique('id')
            ->values()
            ->all();
    }
}
