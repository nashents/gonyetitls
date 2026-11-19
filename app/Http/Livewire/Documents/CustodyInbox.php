<?php

namespace App\Http\Livewire\Documents;

use App\Models\DocumentMovement;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * "My Document Hand-overs": documents waiting for me to sign for them,
 * ones I've sent that haven't been received yet, and my recent history.
 */
class CustodyInbox extends Component
{
    public function render()
    {
        $userId = Auth::id();
        $base = fn () => DocumentMovement::with(['document', 'fromUser', 'toUser'])->whereHas('document');

        return view('livewire.documents.custody-inbox', [
            'incoming' => $base()->where('status', DocumentMovement::PENDING)->where('to_user_id', $userId)->latest('id')->get(),
            'outgoing' => $base()->where('status', DocumentMovement::PENDING)
                ->where(fn ($q) => $q->where('from_user_id', $userId)->orWhere('user_id', $userId))
                ->latest('id')->get(),
            'history' => $base()->where('status', '!=', DocumentMovement::PENDING)
                ->where(fn ($q) => $q->where('from_user_id', $userId)->orWhere('to_user_id', $userId)->orWhere('received_by', $userId))
                ->latest('id')->limit(50)->get(),
        ]);
    }
}
