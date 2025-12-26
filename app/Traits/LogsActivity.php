<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{

    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->recordActivity('created');
        });

        static::updated(function ($model) {
            $model->recordActivity('updated');
        });

        static::deleted(function ($model) {
            $model->recordActivity('deleted');
        });

          static::retrieved(function ($model) {
        $model->recordActivity('viewed');
    });
    }

    protected function recordActivity($event)
    {
        ActivityLog::create([
            'log_name'    => (new \ReflectionClass($this))->getShortName(), // يعطيك اسم الـ Model فقط (مثلاً User)
            'description' => "$event " . $this->{$this->getKeyName()},
            'user_id'     => Auth::id() ?? null,
            'ip_address'  => Request::ip(),
        ]);
    }
}
