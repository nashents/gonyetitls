<?php

namespace App\Services\GoodsReturneds;

use App\Models\GoodsReturned;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReturnedAuthorizationService
{
    public function __construct(private GoodsReturnedReversalService $reversal)
    {
    }

    /**
     * Authorize (approve/reject) a submitted (authorization='pending') goods
     * return. Approving decrements the returned qty from the underlying
     * Inventory/Asset/Tyre rows and posts a debit note reversing the
     * relevant portion of the original GRV's Bill/JournalEntry - see
     * GoodsReturnedReversalService. Rejecting only touches the header;
     * nothing is decremented or posted.
     */
    public function authorize(GoodsReturned $goodsReturned, string $decision, ?string $comments, int $userId): GoodsReturned
    {
        return DB::transaction(function () use ($goodsReturned, $decision, $comments, $userId) {

            $goodsReturned = GoodsReturned::lockForUpdate()->find($goodsReturned->id);

            if (! $goodsReturned) {
                throw ValidationException::withMessages([
                    'goods_returned' => 'This goods return could not be found.',
                ]);
            }

            if ($goodsReturned->authorization !== 'pending') {
                throw ValidationException::withMessages([
                    'goods_returned' => 'Only submitted (pending) returns can be authorized.',
                ]);
            }

            $goodsReturned->authorized_by_id = $userId;
            $goodsReturned->authorization = $decision;
            $goodsReturned->authorization_date = now();
            $goodsReturned->authorization_comments = $comments;
            $goodsReturned->save();

            if ($decision === 'approved') {
                $this->reversal->reverse($goodsReturned->fresh());
                // reverse() persisted debit_note_id on its own copy of this
                // row - refresh so the instance returned to the caller
                // reflects it too, instead of clobbering nothing but still
                // reporting a stale (null) debit_note relation.
                $goodsReturned->refresh();
                $goodsReturned->status = 'approved';
                $goodsReturned->save();
            }

            return $goodsReturned;
        });
    }
}
