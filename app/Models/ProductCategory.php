<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'uid';
    public $timestamps = false;

    protected $fillable = [
        'category_name',
        'description',
        'image_path',
        'image_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_uid', 'uid');
    }

    public function imageUrl(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return asset($this->image_path);
    }
}
