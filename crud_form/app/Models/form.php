<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class form extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'form_title',
        'form_description',
        'status',
        'form_fields',
        'google_sheet_endpoint',
        // 'google_sheet_url',
        // 'created_by',
        // 'updated_by'
    ];

    protected $casts = [
        'status' => 'boolean',
        'form_fields' => 'array',
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime'
    ];

    // Relationships
    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    public function responses()
    {
        return $this->hasMany(FormResponse::class);
    }

    // public function creator()
    // {
    //     return $this->belongsTo(User::class, 'created_by');
    // }

    // public function updater()
    // {
    //     return $this->belongsTo(User::class, 'updated_by');
    // }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    // Accessors
    public function getResponseCountAttribute()
    {
        return $this->responses()->count();
    }

    public function getFieldTypesAttribute()
    {
        return $this->fields->pluck('type')->unique()->toArray();
    }

    public function getIsPublishedAttribute()
    {
        return $this->is_active && !$this->trashed();
    }
}
