<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

class AuditLogsService
{
    public function __construct() {}

    public function formatActivity(Activity $activity): array
    {
        $user = User::find($activity->causer_id);

        $message = $this->getMessage($activity);

        $formattedActivity = [
            'id' => $activity->id,
            'date' => Carbon::parse($activity->created_at)->format('d/n/y'),
            'time' => Carbon::parse($activity->created_at)->format('g:i A'),
            'user' => $user ? strtoupper($user->first_name[0]) . ' ' . $user->last_name : 'N/A',
            'change_type' => $activity->event ? ucfirst($this->getActionVerb($activity->event)) : 'N/A',
            'record_type' => $activity->subject_type ? str_replace('_', ' ', class_basename($activity->subject_type)) : 'N/A',
            'record_id' => $activity->subject_id,
            'old_value' => 'N/A',
            'new_value' => 'N/A',
            'message' => $message,
            'created_at' => $activity->created_at,
        ];

        if ($activity->event === 'updated') {
            $formattedActivity = $this->handleUpdatedActivity($formattedActivity, $activity);
        }

        return $formattedActivity;
    }

    private function getMessage(Activity $activity): string
    {
        if ($activity->event === 'created') {
            return 'Created ' . str_replace('_', ' ', strtolower(class_basename($activity->subject_type))) . ' data';
        } elseif ($activity->event === 'deleted') {
            return 'Deleted ' . str_replace('_', ' ', strtolower(class_basename($activity->subject_type))) . ' data';
        } elseif ($activity->event === 'restored') {
            return 'Restored ' . str_replace('_', ' ', strtolower(class_basename($activity->subject_type))) . ' data';
        }

        return 'N/A';
    }

    private function handleUpdatedActivity(array $formattedActivity, Activity $activity): array
    {
        $oldValues = $activity->properties['old'] ?? [];
        $newValues = $activity->properties['attributes'] ?? [];

        $changes = collect();

        foreach ($newValues as $attribute => $newValue) {
            if (in_array($attribute, ['created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            if ($attribute === 'centre_lcr_req') {
                $oldCentreLcrReq = $oldValues['centre_lcr_req'] ?? 'N/A';
                $newCentreLcrReq = $newValue ?? 'N/A';

                $oldCentreLcrReq = explode('","', str_replace(['["', '"]'], '', $oldCentreLcrReq));
                $newCentreLcrReq = explode('","', str_replace(['["', '"]'], '', $newCentreLcrReq));

                if ($oldCentreLcrReq !== $newCentreLcrReq) {
                    $changes->push([
                        'attribute' => $attribute,
                        'old_value' => implode(', ', $oldCentreLcrReq),
                        'new_value' => implode(', ', $newCentreLcrReq),
                    ]);
                }
            }

            if ((!isset($oldValues[$attribute]) || $oldValues[$attribute] === '' || $oldValues[$attribute] === null) && $newValue !== '' && $newValue !== null) {
                $formattedOldValue = 'null';
                $formattedNewValue = $newValue;
            } else if (isset($oldValues[$attribute]) && $oldValues[$attribute] !== $newValue) {
                $formattedOldValue = $oldValues[$attribute] ?? 'null';
                $formattedNewValue = $newValue ?? 'null';
            } else {
                continue;
            }

            $changes->push([
                'attribute' => $attribute,
                'old_value' => $formattedOldValue,
                'new_value' => $formattedNewValue,
            ]);
        }

        $formattedChanges = $changes->map(function ($change) {
            $attributeName = ucwords(str_replace('_', ' ', $change['attribute']));
            $oldValue = $change['old_value'];
            $newValue = $change['new_value'];

            if ($change['attribute'] === 'filename') {
                $oldValue = is_array($oldValue) ? implode(', ', $oldValue) : $oldValue;
                $newValue = is_array($newValue) ? implode(', ', $newValue) : $newValue;
            } elseif ($change['attribute'] === 'joining_cert_files' || $change['attribute'] === 'clearing_cert_files') {
                $oldValue = is_array($oldValue) ? implode(', ', $oldValue) : $oldValue;
                $newValue = is_array($newValue) ? implode(', ', $newValue) : $newValue;
            } else {
                $oldValue = is_array($oldValue) ? json_encode($oldValue) : str_replace(['["', '"]'], '', $oldValue);
                $newValue = is_array($newValue) ? json_encode($newValue) : str_replace(['["', '"]'], '', $newValue);
            }

            return [
                'old_value' => $attributeName . ': ' . $oldValue,
                'new_value' => $attributeName . ': ' . $newValue,
            ];
        })->toArray();


        $formattedOldValue = implode(', ', array_column($formattedChanges, 'old_value'));
        $formattedNewValue = implode(', ', array_column($formattedChanges, 'new_value'));

        $formattedActivity['old_value'] = $formattedOldValue;
        $formattedActivity['new_value'] = $formattedNewValue;

        if ($formattedActivity['record_type'] === 'ConflictManager') {
            $oldResolved = $oldValues['resolved'] ?? 0;
            $newResolved = $newValues['resolved'] ?? 0;
            if ($oldResolved == 0 && $newResolved == 1) {
                $formattedActivity['message'] = 'Accepted';
            }
        }

        return $formattedActivity;
    }

    private function getActionVerb(string $description): string
    {
        $pastTenseVerbs = ['created', 'deleted', 'restored', 'updated'];

        if (in_array($description, $pastTenseVerbs)) {
            return rtrim($description, 'd');
        }

        return $description;
    }
}
