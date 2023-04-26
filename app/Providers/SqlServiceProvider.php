<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class SqlServiceProvider extends ServiceProvider
{

    public function boot()
    {
        DB::listen(function ($query) {
            $bindings = $query->connection->prepareBindings($query->bindings);

            $sql = preg_replace_callback('/(?<!\?)\?(?!\?)/', static function () use ($query, &$bindings) {
                $value = array_shift($bindings);

                switch ($value) {
                    case null:
                        $value = 'null';

                        break;
                    case is_bool($value):
                        $value = $value ? 'true' : 'false';

                        break;
                    case is_numeric($value):
                        break;
                    default:
                        $value = $query->connection->getPdo()->quote((string) $value);

                        break;
                }

                return $value;
            }, $query->sql);

            $x = [
                $query->connection->getDatabaseName(),
                self::formatDuration($query->time),
                $sql,
                request()->method(),
                request()->getRequestUri(),
                request()->getClientIp(),
            ];
            Log::info($x);

        });
    }
    protected  static function formatDuration($seconds)
    {
        if ($seconds < 1) {
            return round($seconds * 1000).'μs';
        }

        if ($seconds < 1000) {
            return $seconds.'ms';
        }

        return round($seconds / 1000, 2).'s';
    }
}
