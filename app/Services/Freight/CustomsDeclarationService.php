<?php

namespace App\Services\Freight;

use App\Models\CustomsDeclaration;
use App\Models\CustomsDeclarationLine;
use App\Services\NumberGeneratorService;
use Illuminate\Support\Facades\DB;

class CustomsDeclarationService
{
    public function __construct(
        private NumberGeneratorService $numbers,
        private ShipmentMilestoneService $milestones
    ) {
    }

    /**
     * $lines: list of CustomsDeclarationLine attribute arrays.
     */
    public function create(array $data, array $lines = []): CustomsDeclaration
    {
        return DB::transaction(function () use ($data, $lines) {
            $data['status'] = $data['status'] ?? 'instructions_received';
            $data['declaration_number'] = $this->numbers->generate('customs_declaration', 'CD');
            $declaration = CustomsDeclaration::create($data);

            foreach ($lines as $line) {
                $line['customs_declaration_id'] = $declaration->id;
                CustomsDeclarationLine::create($line);
            }

            $this->milestones->recordAndComplete([
                'shipment_id' => $declaration->shipment_id,
                'customs_declaration_id' => $declaration->id,
                'milestone_code' => 'instructions_received',
                'milestone_name' => CustomsDeclaration::WORKFLOW_STAGES['instructions_received'],
            ]);

            return $this->recalculateTotals($declaration);
        });
    }

    public function transitionStatus(CustomsDeclaration $declaration, string $stageCode, array $milestoneData = []): CustomsDeclaration
    {
        return DB::transaction(function () use ($declaration, $stageCode, $milestoneData) {
            $declaration->status = $stageCode;

            $dateColumn = CustomsDeclaration::STAGE_DATE_COLUMNS[$stageCode] ?? null;
            if ($dateColumn) {
                $declaration->{$dateColumn} = $declaration->{$dateColumn} ?? now();
            }

            $declaration->save();

            $this->milestones->recordAndComplete(array_merge([
                'shipment_id' => $declaration->shipment_id,
                'customs_declaration_id' => $declaration->id,
                'milestone_code' => $stageCode,
                'milestone_name' => CustomsDeclaration::WORKFLOW_STAGES[$stageCode] ?? ucwords(str_replace('_', ' ', $stageCode)),
            ], $milestoneData));

            return $declaration;
        });
    }

    public function addLine(CustomsDeclaration $declaration, array $lineData): CustomsDeclarationLine
    {
        return DB::transaction(function () use ($declaration, $lineData) {
            $lineData['customs_declaration_id'] = $declaration->id;
            $line = CustomsDeclarationLine::create($lineData);
            $this->recalculateTotals($declaration);

            return $line;
        });
    }

    public function updateLine(CustomsDeclarationLine $line, array $lineData): CustomsDeclarationLine
    {
        return DB::transaction(function () use ($line, $lineData) {
            $line->update($lineData);
            $this->recalculateTotals($line->customs_declaration);

            return $line;
        });
    }

    public function deleteLine(CustomsDeclarationLine $line): void
    {
        DB::transaction(function () use ($line) {
            $declaration = $line->customs_declaration;
            $line->delete();
            $this->recalculateTotals($declaration);
        });
    }

    public function recalculateTotals(CustomsDeclaration $declaration): CustomsDeclaration
    {
        $lines = $declaration->lines();

        $declaration->total_customs_value = $lines->sum('customs_value');
        $declaration->total_duty = $lines->sum('duty_amount');
        $declaration->total_vat = $lines->sum('vat_amount');
        $declaration->total_excise = $lines->sum('excise_amount');
        $declaration->total_levies = $lines->sum('levies_amount');
        $declaration->save();

        return $declaration;
    }
}
