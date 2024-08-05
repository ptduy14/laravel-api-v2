<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories'; // Tên bảng

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id'; // Khóa chính

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_name',
        'category_desc',
        'category_status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'category_status' => 'boolean',
    ];

    /**
     * Get all the products for the category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
