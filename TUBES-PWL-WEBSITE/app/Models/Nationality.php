<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    use HasFactory;

    protected $table      = 'nationalities';
    protected $primaryKey = 'nationality_id';

    protected $fillable = [
        'nationality_name',
    ];

    public function getNameAttribute(): string
    {
        return (string) ($this->nationality_name ?? '');
    }
}
