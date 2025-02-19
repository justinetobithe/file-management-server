<?php

namespace App\Traits;

use App\Services\AuditLogsService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

trait ActivityLoggable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }

    public function getActivityLogAttribute()
    {
        if (Request::isMethod('get') && Request::is('api/*/*/*')) {
            $perPage = request()->input('per_page', 10);

            $activities = Activity::where('subject_id', $this->id)
                ->where('subject_type', get_class($this))
                ->latest()
                ->paginate($perPage);

            return $this->formatActivities($activities);
        }

        return null;
    }

    private function formatActivities($activities)
    {
        return $this->sliceActivities($activities);
    }

    private function sliceActivities($activities)
    {
        $perPage = request()->input('per_page', 10);

        $slicedActivities = $activities->slice(0, $perPage)->map(function ($activity) {
            return app(AuditLogsService::class)->formatActivity($activity);
        });

        $paginator = new LengthAwarePaginator(
            $slicedActivities,
            $activities->total(),
            $perPage,
            $activities->currentPage(),
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return $paginator->setCollection($slicedActivities);
    }
}
