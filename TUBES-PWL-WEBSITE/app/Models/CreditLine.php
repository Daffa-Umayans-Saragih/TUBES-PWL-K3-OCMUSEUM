<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditLine extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'credit_lines';
    protected $primaryKey = 'credit_line_id';

    protected $fillable = [
        'credit_line_text',
    ];

    public function artWorks()
    {
        return $this->hasMany(ArtWork::class, 'credit_line_id');
    }
}
