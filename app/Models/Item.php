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
        if ($this->localized_names) {
            $names = is_array($this->localized_names) ? $this->localized_names : json_decode($this->localized_names, true);
            
            // Buscar el nombre en el idioma solicitado
            if (isset($names[$locale])) {
                return $names[$locale];
            }
            
            // Fallback a inglés
            if (isset($names['EN-US'])) {
                return $names['EN-US'];
            }
        }
        
        // Último fallback: mostrar el nombre técnico
        return $this->unique_name;
    }

    public function getDisplayNameAttribute()
    {
        // Si tienes localized_names, úsalo
        if ($this->localized_names && isset($this->localized_names['ES-ES'])) {
            return $this->localized_names['ES-ES'];
        }
        if ($this->localized_names && isset($this->localized_names['EN-US'])) {
            return $this->localized_names['EN-US'];
        }
        
        // Si no, intenta formatear el unique_name
        $name = str_replace('_', ' ', $this->unique_name);
        $name = preg_replace('/@\d+$/', '', $name);
        return trim($name);
    }

    // app/Models/Item.php
    public function getImageUrlAttribute()
    {
        return "https://gameinfo.albiononline.com/api/gameinfo/items/{$this->unique_name}/";
    }

    // Método adicional para obtener la imagen con tamaño específico
    public function getImageUrlWithSize($size = 64)
    {
        return "https://gameinfo.albiononline.com/api/gameinfo/items/{$this->unique_name}/?size={$size}";
    }
}
