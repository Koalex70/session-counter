<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public const PERIODS = [7, 30];

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'days' => ['sometimes', 'integer'],
        ];
    }

    public function days(): int
    {
        $days = (int) $this->query('days', 7);

        return in_array($days, self::PERIODS, true) ? $days : 7;
    }
}
