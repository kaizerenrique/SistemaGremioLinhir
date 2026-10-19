<?php

namespace App\Services\Guild;

use App\Models\Cta;
use Carbon\Carbon;

/**
 * Builder compartido para los embeds y botones de CTA.
 * Usado por CreateCta (crear) y CtaShow (editar/sincronizar).
 */

class CtaEmbedBuilder
{
    /**
     * Construye el embed principal de una CTA.
     * Si la CTA está cancelada/completada, se muestra con estilo apagado.
     */
    public static function buildEmbed(Cta $cta, ?string $imageUrl = null): array
    {
        // Asegurar relaciones
        if (!$cta->relationLoaded('composition')) {
            $cta->load('composition.weapons');
        }
        if (!$cta->relationLoaded('participants')) {
            $cta->load('participants');
        }

        $startTs = Carbon::parse($cta->start_time)->utc()->timestamp;
        $endTs   = Carbon::parse($cta->end_time)->utc()->timestamp;

        $asignaciones = $cta->participants
            ->whereNotNull('composition_weapon_id')
            ->mapWithKeys(fn($p) => [$p->composition_weapon_id => $p->user_name])
            ->toArray();

        $lineas     = [];
        $totalArmas = $cta->composition->weapons->count();
        $asignadas  = 0;

        foreach ($cta->composition->weapons as $i => $w) {
            $icono    = $w->weapon_icon ?? '•';
            $ocupante = $asignaciones[$w->id] ?? null;
            if ($ocupante) $asignadas++;

            $estado = $ocupante
                ? "🔒 **{$ocupante}**"
                : "⚪ *disponible*";

            $lineas[] = "`" . str_pad((string)($i + 1), 2, ' ', STR_PAD_LEFT)
                . ".` {$icono} {$w->weapon_name} — {$estado}";
        }

        $armasText = implode("\n", array_slice($lineas, 0, 20));
        if (count($lineas) > 20) {
            $armasText .= "\n… y " . (count($lineas) - 20) . " más (ver en la web)";
        }

        $isCancelled = $cta->status === 'cancelled';
        $isCompleted = $cta->status === 'completed';

        if ($isCancelled) {
            $description = "❌ **Esta CTA ha sido CANCELADA.**\n\n"
                . "**📍 Ubicación:** {$cta->location}\n"
                . "**🎭 Composición:** {$cta->composition->name}";
            $color = 0x6D6D6D;
            $title = '❌ [CANCELADA] ' . $cta->title;
        } elseif ($isCompleted) {
            $description = "✅ **Esta CTA ha finalizado.**\n\n"
                . "**📍 Ubicación:** {$cta->location}\n"
                . "**🎭 Composición:** {$cta->composition->name}\n\n"
                . "**Ocupación final:** `{$asignadas}/{$totalArmas}`";
            $color = 0x5CCB45;
            $title = '✅ [COMPLETADA] ' . $cta->title;
        } else {
            $description = "**📍 Ubicación:** {$cta->location}\n"
                . "**🎭 Composición:** {$cta->composition->name}\n\n"
                . "**Ocupación:** `{$asignadas}/{$totalArmas}` armas reservadas por staff\n\n"
                . "🔒 Reservado · ⚪ Disponible para unirse con el botón\n\n"
                . "> Pulsa **Unirse** para escoger un arma libre. "
                . "Las armas reservadas (🔒) no se pueden tomar.";
            $color = 0xC54B47;
            $title = '⚔️ ' . $cta->title;
        }

        $embed = [
            'title'       => $title,
            'description' => $description,
            'color'       => $color,
            'timestamp'   => Carbon::now()->toIso8601String(),
            'fields'      => [
                ['name' => '⏰ Inicio', 'value' => "<t:{$startTs}:F> (<t:{$startTs}:R>)", 'inline' => true],
                ['name' => '🏁 Fin',    'value' => "<t:{$endTs}:t>", 'inline' => true],
                ['name' => '🔔 Recordatorio', 'value' => "{$cta->reminder_minutes} min antes", 'inline' => true],
                [
                    'name'   => "⚔️ Armas y asignación ({$asignadas}/{$totalArmas})",
                    'value'  => $armasText ?: '*Sin armas definidas*',
                    'inline' => false,
                ],
            ],
            'footer' => [
                'text'     => 'Linhir · CTA gestionada por staff',
                'icon_url' => asset('imagenes/escudo_512x512.png'),
            ],
        ];

        if ($imageUrl) {
            $embed['image'] = ['url' => $imageUrl];
        }

        return $embed;
    }

    /**
     * Construye los botones de acción.
     * Devuelve array vacío si la CTA no está scheduled (cancelada/completada).
     */
    public static function buildButtons(Cta $cta): array
    {
        if ($cta->status !== 'scheduled') {
            return [];
        }

        return [[
            'type' => 1,
            'components' => [
                [
                    'type'      => 2,
                    'style'     => 3,
                    'label'     => 'Unirse',
                    'emoji'     => ['name' => '⚔️'],
                    'custom_id' => "linhir:cta:join:{$cta->id}",
                ],
                [
                    'type'      => 2,
                    'style'     => 2,
                    'label'     => 'Ausente',
                    'emoji'     => ['name' => '⏰'],
                    'custom_id' => "linhir:cta:absent:{$cta->id}",
                ],
            ],
        ]];
    }
}
