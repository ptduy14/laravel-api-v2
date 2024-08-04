<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    use HasFactory;

    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_details'; // Tên bảng

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
        'product_detail_intro',
        'product_detail_desc',
        'product_detail_weight',
        'product_detail_mfg',
        'product_detail_exp',
        'product_detail_origin',
        'product_detail_manual',
        'product_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'product_detail_weight' => 'decimal:2',
        'product_detail_mfg' => 'date',
        'product_detail_exp' => 'date',
    ];

    /**
     * Get the product that owns the product detail.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
