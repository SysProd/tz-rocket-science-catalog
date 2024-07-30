<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Таблица "products" хранит основную информацию о каждом товаре
 *
 * Список колонок:
 * название
 * цена
 * количество
 *
 * Class Product
 * @package App\Models
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'quantity'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'product_properties')
            ->withPivot('property_value_id');
    }

    /**
     * @param $query
     * @param $properties
     */
    public function scopeFilterByProperties($query, $properties)
    {
        foreach ($properties as $property => $values) {
            $query->whereHas('properties', function ($q) use ($property, $values) {
                $q->join('property_values', 'product_properties.property_value_id', '=', 'property_values.id')
                    ->where('properties.name', $property)
                    ->whereIn('property_values.value', $values);
            });
        }
    }
}
