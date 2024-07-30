<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Таблица "property_values" хранит значения для различных свойств
 * (например, для свойства "Цвет" значения могут быть "Красный", "Синий" и т.д.).
 *
 * Class PropertyValue
 * @package App\Models
 */
class PropertyValue extends Model
{
    use HasFactory;

    protected $fillable = ['value', 'property_id'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
