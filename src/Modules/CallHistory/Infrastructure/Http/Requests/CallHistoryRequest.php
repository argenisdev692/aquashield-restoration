<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CallHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $method = $this->method();

        if ($method === 'POST' && $this->routeIs('*sync*')) {
            return $this->getSyncRules();
        }

        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            return $this->getUpdateRules();
        }

        return $this->getListRules();
    }

    /**
     * @return array<string, mixed>
     */
    private function getListRules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:registered,ongoing,ended,error',
            'direction' => 'nullable|string|in:inbound,outbound',
            'call_type' => 'nullable|string|in:lead,appointment,support,other',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'sort_field' => 'nullable|string|in:start_timestamp,end_timestamp,call_status,call_type,created_at',
            'sort_direction' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getUpdateRules(): array
    {
        return [
            'agent_name' => 'nullable|string|max:255',
            'call_status' => 'nullable|string|in:registered,ongoing,ended,error',
            'call_type' => 'nullable|string|in:lead,appointment,support,other',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getSyncRules(): array
    {
        return [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:1000',
        ];
    }
}
