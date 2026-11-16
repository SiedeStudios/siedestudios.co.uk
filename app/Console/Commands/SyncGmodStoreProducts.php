<?php

namespace App\Console\Commands;

use App\Services\GmodStore\GmodStoreClient;
use App\Services\GmodStore\ProductSync;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('gmodstore:sync')]
#[Description('Refresh GmodStore product prices, ratings and sales')]
class SyncGmodStoreProducts extends Command
{
    public function handle(GmodStoreClient $client, ProductSync $sync): int
    {
        if (! $client->configured()) {
            $this->components->warn('No GmodStore API key set (Admin → Settings → GmodStore).');

            return self::SUCCESS;
        }

        $added = $sync->importTeam();
        [$ok, $failed] = $sync->syncAll();
        $this->components->info("Synced {$ok} product(s)".($added ? ", {$added} new" : '').($failed ? ", {$failed} failed" : '').'.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
