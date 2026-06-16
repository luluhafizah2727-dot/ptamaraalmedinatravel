<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'visited_on',
        'visited_at',
        'path',
        'route_name',
        'ip_hash',
        'user_agent_hash',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visited_on' => 'date',
            'visited_at' => 'datetime',
        ];
    }
}
