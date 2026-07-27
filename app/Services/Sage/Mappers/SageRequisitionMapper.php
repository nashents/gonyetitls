<?php

namespace App\Services\Sage\Mappers;

use App\Models\TripExpense;
use App\Models\Trip;
use App\Services\Sage\Support\SageFormat;

/**
 * Builds the Sage Purchase Requisition (create_potransaction) header + lines for
 * one (trip × vendor × currency) group of trip expenses.
 */
class SageRequisitionMapper
{
    /** Stable de-dup reference: TRIP-{trip_number}-V{vendorId}. */
    public static function referenceNo(Trip $trip, int $vendorId): string
    {
        $tripRef = $trip->trip_number ?: ('TRIP-' . $trip->id);

        return mb_substr('TRIP-' . $tripRef . '-V' . $vendorId, 0, 100);
    }

    /**
     * @param  string       $vendorSageId   VENDORID
     * @param  string       $vendorContact  vendor's contact name (pay-to/return-to)
     * @param  string|null  $currencyCode
     */
    public static function header(Trip $trip, int $vendorId, string $vendorSageId, string $vendorContact, ?string $currencyCode): array
    {
        return [
            'transactiontype' => (string) config('sageintacct.purchasing.requisition_type', 'Purchase requisition'),
            'datecreated'     => $trip->start_date,
            'datedue'         => $trip->start_date,     // expiration = same as date
            'vendorid'        => $vendorSageId,
            'referenceno'     => self::referenceNo($trip, $vendorId),
            'contactname'     => $vendorContact,        // pay-to + return-to
            'currency'        => $currencyCode ?: null,
        ];
    }

    /**
     * One requisition line from a trip expense.
     *
     * @param  string       $itemId       Sage ITEMID (from the Expense)
     * @param  string|null  $projectId    trip project id
     * @param  string|null  $classId      horse class id
     * @param  string|null  $employeeId   driver employee id
     */
    public static function line(TripExpense $expense, string $itemId, ?string $projectId, ?string $classId, ?string $employeeId): array
    {
        return [
            'itemid'       => $itemId,
            'itemdesc'     => optional($expense->expense)->name ?: 'Trip expense',
            'quantity'     => 1,
            'unit'         => (string) config('sageintacct.purchasing.line_unit', 'Each'),
            'price'        => self::amount($expense),
            'locationid'   => config('sageintacct.project.location_id'),
            'departmentid' => config('sageintacct.project.department_id'),
            'projectid'    => $projectId ?: null,
            'employeeid'   => $employeeId ?: null,
            'classid'      => $classId ?: null,
        ];
    }

    protected static function amount(TripExpense $expense): string
    {
        return number_format((float) $expense->amount, 2, '.', '');
    }
}
