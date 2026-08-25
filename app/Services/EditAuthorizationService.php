<?php

namespace App\Services;

use App\Contracts\EditAuthorizable;
use App\Models\EditAuthorizationRequest;
use App\Models\EditAuthorizer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Shared request/approve/one-time-consume workflow for editing a normally
 * locked record. Originally built for Trips only (TripEditAuthorizer /
 * TripEditAuthorizationRequest); generalized so any module implementing
 * App\Contracts\EditAuthorizable can reuse the same tables and logic
 * (see config('edit_authorization') for the module registry).
 */
class EditAuthorizationService
{
    public function isAuthorizer(User $user, ?string $module = null): bool
    {
        $query = EditAuthorizer::where('user_id', $user->id)->where('status', 1);

        if ($module) {
            $query->forModule($module);
        }

        return $query->exists();
    }

    /** Modules the given user is an active authorizer for. */
    public function authorizedModules(User $user): array
    {
        return EditAuthorizer::where('user_id', $user->id)
            ->where('status', 1)
            ->pluck('module')
            ->unique()
            ->values()
            ->all();
    }

    public function hasPendingRequest(EditAuthorizable $model, User $user): bool
    {
        return EditAuthorizationRequest::where('editable_type', get_class($model))
            ->where('editable_id', $model->getKey())
            ->where('user_id', $user->id)
            ->pending()
            ->exists();
    }

    public function activeGrant(EditAuthorizable $model, User $user): ?EditAuthorizationRequest
    {
        return EditAuthorizationRequest::where('editable_type', get_class($model))
            ->where('editable_id', $model->getKey())
            ->where('user_id', $user->id)
            ->approvedUnconsumed()
            ->latest()
            ->first();
    }

    public function requestEdit(EditAuthorizable $model, User $requester, string $reason): EditAuthorizationRequest
    {
        if ($this->hasPendingRequest($model, $requester)) {
            throw new \RuntimeException('You already have a pending authorization request for this record.');
        }

        return EditAuthorizationRequest::create([
            'editable_type' => get_class($model),
            'editable_id' => $model->getKey(),
            'module' => $model->editAuthModule(),
            'owner_id' => $model->editAuthOwnerId(),
            'user_id' => $requester->id,
            'reason' => $reason,
            'status' => 'pending',
        ]);
    }

    public function decide(EditAuthorizationRequest $request, User $decider, string $decision, ?string $comments = null): EditAuthorizationRequest
    {
        return DB::transaction(function () use ($request, $decider, $decision, $comments) {
            $ownerId = $request->owner_id ?? $request->editable?->editAuthOwnerId();

            abort_if($ownerId !== null && $ownerId === $decider->id, 403, 'You cannot authorize edits for your own record.');

            $request->decided_by = $decider->id;
            $request->status = $decision;
            $request->decided_at = now();
            $request->decision_comments = $comments;
            $request->save();

            return $request;
        });
    }

    public function consume(EditAuthorizationRequest $grant): void
    {
        $grant->update(['consumed_at' => now()]);
    }

    /** Pending request count across every module the user is an authorizer for (all modules if super admin). */
    public function pendingCountForUser(User $user): int
    {
        $query = EditAuthorizationRequest::pending()
            ->where(function ($q) use ($user) {
                $q->whereNull('owner_id')->orWhere('owner_id', '!=', $user->id);
            });

        if (! $user->isSuperAdmin()) {
            $modules = $this->authorizedModules($user);

            if (empty($modules)) {
                return 0;
            }

            $query->whereIn('module', $modules);
        }

        return $query->count();
    }
}
