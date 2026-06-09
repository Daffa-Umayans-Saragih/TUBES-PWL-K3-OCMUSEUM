<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Portfolio extends Model
{
    protected $primaryKey = 'portfolio_id';
    public $timestamps = false;
    protected $fillable = ['portfolio_name'];

    public function artWorks(): BelongsToMany
    {
        return $this->belongsToMany(ArtWork::class, 'art_work_portfolios', 'portfolio_id', 'art_work_id');
    }
}
