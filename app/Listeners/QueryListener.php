<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class QueryListener
{
    /**
     *
     *
     * @param  QueryExecuted  $query
     * @return void
     */
    public function handle(QueryExecuted $query)
    {
        // SQL 日志
        if (config('app.debug', false)) {
            $sql = str_replace("?", "'%s'", $query->sql);
            $log = vsprintf($sql, $query->bindings);
            Log::channel('sql')->debug($query->time . ' : ' . $log);
        }
    }
}

