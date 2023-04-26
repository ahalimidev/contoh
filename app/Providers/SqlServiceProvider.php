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

            $log['name'] = "";
            $log['sql'] = $sql;
            $log['time'] = self::formatDuration($query->time);
            $log['method'] = request()->method();
            $log['url'] = request()->getRequestUri();
            $log['ip'] = request()->getClientIp();
            $log['tanggal'] = date('Y-m-d H:i:s');
            Log::info($log);

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
