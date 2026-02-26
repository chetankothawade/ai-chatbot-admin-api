<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subject = $this->subject;
        $subjectLabel = $this->subject_type ? class_basename($this->subject_type) : null;
        $subjectIdentifier = $subject?->name ?? $subject?->title ?? $subject?->id ?? null;

        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'event' => $this->event,
            'description' => $this->description,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'causer_type' => $this->causer_type,
            'causer_id' => $this->causer_id,
            'causer' => $this->whenLoaded('causer', function () {
                return [
                    'id' => $this->causer?->id,
                    'name' => $this->causer?->name,
                ];
            }),
            'subject_label' => $subjectLabel,
            'subject_identifier' => $subjectIdentifier,
            'subject_display' => $subjectLabel && $subjectIdentifier
                ? "{$subjectLabel} - {$subjectIdentifier}"
                : ($subjectLabel ?? $subjectIdentifier),
            'properties' => $this->formatProperties($this->properties?->toArray() ?? []),
            'created_at' => optional($this->created_at)->format('d/m/Y h:i A'),
        ];
    }

    private function formatProperties(array $properties): array
    {
        foreach (['old', 'attributes'] as $bucket) {
            if (empty($properties[$bucket]) || ! is_array($properties[$bucket])) {
                continue;
            }

            foreach ($properties[$bucket] as $field => $value) {
                if (! is_string($field) || ! is_string($value) || $value === '') {
                    continue;
                }

                try {
                    if (str_ends_with($field, '_at')) {
                        $properties[$bucket][$field] = Carbon::parse($value)
                            ->timezone('Asia/Kolkata')
                            ->format('d/m/Y h:i A');
                    }

                    if (str_ends_with($field, '_date') || str_starts_with($field, 'date')) {
                        $properties[$bucket][$field] = Carbon::parse($value)
                            ->timezone('Asia/Kolkata')
                            ->format('d/m/Y');
                    }
                } catch (Throwable) {
                    // Keep original value if parsing fails.
                }
            }
        }

        return $properties;
    }
}
