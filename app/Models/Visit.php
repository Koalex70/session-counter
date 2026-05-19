<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'visitor_id',
        'ip_address',
        'city',
        'country',
        'device_type',
        'browser',
        'os',
        'page_url',
        'referrer',
        'user_agent',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function scopeSince(Builder $query, CarbonInterface $from): Builder
    {
        return $query->where('visited_at', '>=', $from);
    }
}
