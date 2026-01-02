<?php

namespace App\Console\Commands;

use App\Models\ChangeLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncChangeLog extends Command
{
    protected $signature = 'changelog:sync {--file= : Path to changelog json file} {--force : Sync even if file missing (will error)}';
    protected $description = 'Sync release notes from resources/changelog/changelog.json into the database (upsert by key).';

    public function handle(): int
    {
        $path = $this->option('file')
            ? base_path($this->option('file'))
            : resource_path('changelog/changelog.json');

        if (!File::exists($path)) {
            $msg = "Changelog file not found: {$path}";
            $this->error($msg);
            return self::FAILURE;
        }

        $raw = File::get($path);
        $json = json_decode($raw, true);

        if (!is_array($json) || !isset($json['releases']) || !is_array($json['releases'])) {
            $this->error("Invalid changelog format. Expected {\"releases\": [...]}");
            return self::FAILURE;
        }

        $releases = $json['releases'];

        $upserts = [];
        foreach ($releases as $i => $item) {
            if (empty($item['key']) || empty($item['title']) || empty($item['description'])) {
                $this->warn("Skipping item #{$i}: missing key/title/description");
                continue;
            }

            $upserts[] = [
                'key'          => (string) $item['key'],
                'title'        => (string) $item['title'],
                'description'  => (string) $item['description'],
                'module'       => isset($item['module']) ? (string) $item['module'] : null,
                'type'         => isset($item['type']) ? (string) $item['type'] : 'improved',
                'version'      => isset($item['version']) ? (string) $item['version'] : null,
                'released_at'  => !empty($item['released_at']) ? Carbon::parse($item['released_at']) : null,
                'is_published' => array_key_exists('is_published', $item) ? (bool) $item['is_published'] : true,
                'updated_at'   => now(),
                // keep created_at stable on existing rows
                'created_at'   => now(),
            ];
        }

        if (empty($upserts)) {
            $this->info("No valid changelog items found.");
            return self::SUCCESS;
        }

        // Upsert by key: if exists, update these columns
        ChangeLog::upsert(
            $upserts,
            ['key'],
            ['title', 'description', 'module', 'type', 'version', 'released_at', 'is_published', 'updated_at']
        );

        $this->info("Synced " . count($upserts) . " change log items into DB.");
        return self::SUCCESS;
    }
}
