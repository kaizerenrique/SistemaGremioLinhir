<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompositionWeapon extends Model
{
    protected $fillable = ['composition_id', 'weapon_name', 'weapon_icon', 'sort_order'];

    public function composition(): BelongsTo
    {
        return $this->belongsTo(Composition::class);
    }

    /**
     * Devuelve el icono listo para renderizar en HTML.
     *
     * Soporta:
     *   - Emoji Unicode (🛡️)          → se devuelve tal cual.
     *   - Custom emoji (<:name:id>)     → <img> apuntando a la CDN de Discord.
     *   - Custom animado (<a:name:id>)  → <img> con extensión .gif.
     *   - Vacío                          → placeholder "•".
     *
     * Uso en Blade: {!! $weapon->icon_html !!}
     */
    public function getIconHtmlAttribute(): string
    {
        $raw = $this->weapon_icon;

        if (empty($raw)) {
            return '<span class="text-content-light/40 select-none">•</span>';
        }

        // Custom emoji: <:name:id> o <a:name:id>
        if (preg_match('/^<(a?):(\w+):(\d+)>$/', $raw, $m)) {
            $animated = $m[1] === 'a';
            $id       = $m[3];
            $name     = $m[2];
            $ext      = $animated ? 'gif' : 'png';
            $url      = "https://cdn.discordapp.com/emojis/{$id}.{$ext}";

            return sprintf(
                '<img src="%s" alt="%s" title="%s" ' .
                'class="inline-block w-5 h-5 align-middle" loading="lazy" ' .
                'onerror="this.style.display=\'none\'">',
                e($url),
                e($name),
                e($name)
            );
        }

        // Emoji Unicode (o texto plano residual)
        return '<span class="text-base leading-none">' . e($raw) . '</span>';
    }
}
