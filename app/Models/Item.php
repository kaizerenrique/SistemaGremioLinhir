<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $primaryKey = 'unique_name';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'unique_name',
        'item_type',
        'tier',
        'enchantment_level',
        'sprite_name',
        'localized_names',
        'localized_descriptions',
        'image_url',
        'last_updated',
    ];

    protected $casts = [
        'localized_names' => 'array',
        'localized_descriptions' => 'array',
        'last_updated' => 'datetime',
    ];

    public function equipment()
    {
        return $this->hasMany(KillEventEquipment::class, 'item_type', 'unique_name');
    }

    // Método para obtener nombre localizado (por ejemplo, en español)
    public function getLocalizedName($locale = 'ES-ES')
    {
        return $this->localized_names[$locale] ?? $this->localized_names['EN-US'] ?? $this->unique_name;
    }
}
