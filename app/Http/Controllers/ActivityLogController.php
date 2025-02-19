<?php

namespace App\Http\Controllers;

use App\Services\AuditLogsService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    protected $searchService;
    protected $auditLogsService;

    public function __construct(AuditLogsService $auditLogsService)
    {
        $this->auditLogsService = $auditLogsService;
    }

    public function index()
    {
        if (request()->has('page')) {
            $activities = Activity::paginate(10);
        } else {
            $activities = Activity::get();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Request was successful.',
            'data' => $activities,
        ]);
    }

    public function getAllRecords(Request $request)
    {
        $query = Activity::query();

        if ($request->filled('event')) {
            $changeTypes = explode(',', $request->input('event'));
            $query->whereIn('event', $changeTypes);
        }

        if ($request->filled('startDate') && $request->filled('endDate')) {
            $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
            $endDate = Carbon::parse($request->input('endDate'))->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if (request()->has('sortField') && request()->has('sortOrder')) {
            $sortableFields = ['date', 'time', 'user', 'change_type', 'record_type', 'record_id', 'old_value', 'new_value', 'message'];

            $sortField = request('sortField');
            $sortOrder = request('sortOrder', 'asc');

            if (in_array($sortField, $sortableFields)) {
                if (!in_array($sortOrder, ['asc', 'desc'])) {
                    $sortOrder = 'asc';
                }

                if ($sortField === 'date') {
                    $query->orderByRaw("DATE(created_at) $sortOrder");
                } elseif ($sortField === 'time') {
                    $query->orderByRaw("TIME(created_at) $sortOrder");
                } elseif ($sortField === 'user') {
                    if (Schema::hasColumn('activities', 'causer_id')) {
                        $query->leftJoin('users', 'activities.causer_id', '=', 'users.id')
                            ->orderByRaw("CONCAT(users.first_name, ' ', users.last_name) $sortOrder");
                    } else {
                        $query->orderBy('causer_id', $sortOrder);
                    }
                } elseif ($sortField === 'change_type') {
                    $query->orderBy('event', $sortOrder);
                } elseif ($sortField === 'record_type') {
                    $query->orderBy('subject_type', $sortOrder);
                } elseif ($sortField === 'record_id') {
                    $query->orderBy('subject_id', $sortOrder);
                } elseif ($sortField === 'message') {
                    $query->orderBy('event', $sortOrder);
                }
            }
        }

        $activities = $query->orderBy('id', 'desc')->paginate(10);

        $formattedActivities = $activities->getCollection()->map(function ($activity) {
            return $this->auditLogsService->formatActivity($activity);
        });

        $formattedActivities = $formattedActivities->map(function ($activity) {
            $oldValue = json_decode($activity['old_value'], true);
            $newValue = json_decode($activity['new_value'], true);

            $changedFields = [];

            if ($oldValue && $newValue) {
                foreach ($newValue['attributes'] as $key => $newFieldValue) {
                    $oldFieldValue = $oldValue['attributes'][$key] ?? null;

                    if ($oldFieldValue !== $newFieldValue) {
                        $changedFields[$key] = [
                            'old' => $oldFieldValue,
                            'new' => $newFieldValue
                        ];
                    }
                }

                ksort($changedFields);
            }

            $activity['changed_fields'] = $changedFields;

            return $activity;
        });

        if (request()->has('sortField') && request()->has('sortOrder')) {
            $sortField = request('sortField');
            $sortOrder = request('sortOrder', 'asc');

            $formattedActivities = $formattedActivities->sortBy(function ($activity) use ($sortField) {
                return $activity[$sortField];
            }, SORT_REGULAR, $sortOrder === 'desc');
        }

        $formattedActivities = $formattedActivities->values();

        $activities->setCollection($formattedActivities);

        return response()->json([
            'status' => 'success',
            'message' => 'Request was successful.',
            'data' => $activities,
        ]);
    }
}
