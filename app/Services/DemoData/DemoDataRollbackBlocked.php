<?php

namespace App\Services\DemoData;

use RuntimeException;

/** Rollback refused: real data depends on demo rows (see DemoDataRecorder::plan). */
class DemoDataRollbackBlocked extends RuntimeException
{
    public function __construct(public array $reasons)
    {
        parent::__construct('Demo data cannot be rolled back yet: ' . implode(' ', $reasons));
    }
}
