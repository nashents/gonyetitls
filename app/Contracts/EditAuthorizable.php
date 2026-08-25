<?php

namespace App\Contracts;

/**
 * Implemented by any model that supports the one-off "request edit
 * authorization" workflow (see App\Services\EditAuthorizationService) for
 * editing a normally-locked record.
 */
interface EditAuthorizable
{
    /** Registry key in config('edit_authorization'), e.g. 'trips', 'bills'. */
    public function editAuthModule(): string;

    /** User id who "owns" this record; guards against self-authorization. */
    public function editAuthOwnerId(): ?int;

    /** Human-readable identifier shown in authorization request lists. */
    public function editAuthLabel(): string;
}
