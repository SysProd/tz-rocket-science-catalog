<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Таблица "product_properties" является связующей таблицей между таблицами "products" и "property_values".
 * Она используется для хранения связей между товарами и их свойствами (например, цвет, размер и т.д.).
 *
 * Class ProductProperty
 * @package App\Models
 */
class ProductProperty extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'property_id', 'property_value_id'];

}
