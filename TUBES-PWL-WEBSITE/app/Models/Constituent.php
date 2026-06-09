<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Constituent extends Model
{
    use HasFactory;

    protected $table      = 'constituents';
    protected $primaryKey = 'constituent_id';
    public $timestamps    = false;

    protected $fillable = [
        'met_constituent_id',
        'display_name',
        'display_bio',
        'alpha_sort',
        'birth_year',
        'death_year',
        'birth_date_display',
        'death_date_display',
        'birth_place',
        'death_place',
        'gender',
        'ulan_url',
        'wikidata_url',
    ];

    public function nationalities(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Nationality::class, 'constituent_nationalities', 'constituent_id', 'nationality_id');
    }

    public function artWorks(): BelongsToMany
    {
        return $this->belongsToMany(ArtWork::class, 'art_work_constituents', 'constituent_id', 'art_work_id');
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->display_name ?? '');
    }

    public function getNationalityAttribute(): string
    {
        if ($this->relationLoaded('nationalities')) {
            return $this->nationalities
                ->pluck('name')
                ->filter()
                ->implode(', ');
        }

        return $this->nationalities()->pluck('nationality_name')->filter()->implode(', ');
    }

    public function getRoleAttribute(): string
    {
        $role = $this->pivot?->role;

        return $role?->name ?? '';
    }
}
