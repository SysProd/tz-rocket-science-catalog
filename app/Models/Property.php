<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Таблица properties хранит информацию о различных свойствах товаров (например, цвет, размер, бренд и т.д.).
 *
 * Вариант данных в таблице:
 * Бренд
 * Страна производитель
 * Гарантия
 * Цвет
 * Размер и т.д.
 *
 * Class Property
 * @package App\Models
 */
class Property extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function values()
    {
        return $this->hasMany(PropertyValue::class);
    }
}
