<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property float $price
 * @property int $stock
 * @property string|null $image
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [

        'category_id',

        'name',

        'slug',

        'description',

        'price',

        'stock',

        'image',

        'is_active',

    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
