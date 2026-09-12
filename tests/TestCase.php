<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        $this->purgeTenantDatabases();

        parent::tearDown();
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }

    /**
     * Drop leftover tenant databases created outside the RefreshDatabase transaction.
     */
    protected function purgeTenantDatabases(): void
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            tenancy()->end();
        }

        $prefix = (string) config('tenancy.database.prefix');
        $central = (string) config('tenancy.database.central_connection');
        $driver = config("database.connections.{$central}.driver");

        if ($driver === 'sqlite') {
            foreach (glob(database_path($prefix.'*')) ?: [] as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }

            return;
        }

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        $databases = DB::connection($central)->select('SHOW DATABASES LIKE ?', [$prefix.'%']);

        foreach ($databases as $database) {
            $name = (string) array_values((array) $database)[0];

            if (preg_match('/^'.preg_quote($prefix, '/').'[A-Za-z0-9_]+$/', $name) !== 1) {
                continue;
            }

            DB::connection($central)->statement("DROP DATABASE IF EXISTS `{$name}`");
        }
    }
}
