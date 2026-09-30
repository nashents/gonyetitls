<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by JournalEntry::assertBalanced() when a posting would put the Trial
 * Balance out. Every posting service calls it inside its DB::transaction, so
 * throwing rolls the whole posting back - nothing half-written is left in
 * the ledger.
 */
class UnbalancedJournalEntryException extends RuntimeException
{
}
