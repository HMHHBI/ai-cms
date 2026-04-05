<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted()
    {
        static::creating(function ($company) {
            $slug = Str::slug($company->name);
            $count = Company::where('slug', 'LIKE', "{$slug}%")->count();
            $company->slug = $count ? "{$slug}-{$count}" : $slug;
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
