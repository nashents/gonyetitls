<?php

namespace App\Console\Commands;

use App\Services\Fiscal\FiscalProviderResolver;
use Illuminate\Console\Command;

/**
 * Status / fiscal-day control for a company's active fiscal provider
 * (whichever is configured under Integrations). Open/close only applies to
 * providers whose day we run (Revmax); Fiscal Harmony runs its own.
 */
class FiscalDevice extends Command
{
    protected $signature = 'fiscal:device
                            {action=status : status | open | close}
                            {--company= : Company id (default: the only company with an active fiscal integration, else the .env Fiscal Harmony fallback)}';

    protected $description = "Show the active fiscal provider's device status, or open/close its fiscal day";

    public function handle(FiscalProviderResolver $providers): int
    {
        $action = strtolower((string) $this->argument('action'));
        if (! in_array($action, ['status', 'open', 'close'], true)) {
            $this->error('Action must be status, open or close.');
            return self::INVALID;
        }

        try {
            $integration = $providers->integrationForCompany($this->option('company') ? (int) $this->option('company') : null);
            $driver      = $providers->driver($integration);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info($driver->providerName() . ($integration->exists ? " (integration #{$integration->id})" : ' (.env fallback)'));

        if ($action !== 'status' && ! $driver->managesFiscalDay()) {
            $this->warn($driver->providerName() . ' opens and closes the fiscal day itself.');
            return self::FAILURE;
        }

        $result = match ($action) {
            'open'  => $driver->openFiscalDay(),
            'close' => $driver->closeFiscalDay(),
            default => $driver->deviceStatus(),
        };

        if (! $result['success']) {
            $this->error('  ' . $result['error']);
            return self::FAILURE;
        }

        $this->line('  ' . ($result['data']['summary'] ?? 'Done.'));

        return self::SUCCESS;
    }
}
