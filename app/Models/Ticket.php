<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'subject',
        'message',
        'ai_suggestion',
        'ai_sentiment',
        'status',
        'priority',
        'assigned_to',
        'company_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('company', function ($builder) {
            // Agar user super_admin nahi hai, toh hamesha uski company ke tickets dikhao
            if (Auth::check() && Auth::user()->role !== 'super_admin') {
                $builder->where('tickets.company_id', Auth::user()->company_id);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getPriorityColorAttribute()
    {
        return match ($this->priority) {
            'high' => 'bg-red-100 text-red-700 border-red-200',
            'medium' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            'low' => 'bg-blue-100 text-blue-700 border-blue-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    public function getMoodAttribute()
    {
        return strtolower($this->ai_sentiment ?? 'unknown');
    }
}