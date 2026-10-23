<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    protected $fillable = [
        'slug',
        'version',
        'name_bn',
        'name_en',
        'official_url',
        'sections',
        'is_active',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'is_active' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    /** The newest active version of each template. */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereIn('id', static::query()
            ->selectRaw('max(id)')
            ->where('is_active', true)
            ->groupBy('slug'));
    }

    /** @return list<array<string, mixed>> every field, in form order */
    public function fields(): array
    {
        return collect($this->sections)->flatMap(fn ($section) => $section['fields'])->all();
    }

    public function fieldKeys(): array
    {
        return array_column($this->fields(), 'key');
    }
}
