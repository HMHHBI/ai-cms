<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'subject',
        'message',
        'status',
        'priority',
        'ai_suggestion',
        'customer_name',
        'customer_email',
        'assigned_to'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class); // 👈 ADD THIS
    }
}
