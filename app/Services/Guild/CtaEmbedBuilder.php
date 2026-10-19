<?php

namespace App\Services\Guild;

use App\Models\Cta;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Builder compartido para los embeds y botones de CTA.
 * Usado por CreateCta (crear) y CtaShow (editar/sincronizar).
 *
 * IMPORTANTE — límites de Discord:
 *   - title            : máx 256
 *   - description      : máx 4096
 *   - field.name       : máx 256
 *   - field.value      : máx 1024  ← el que nos fallaba
 *   - footer.text      : máx 2048
 *   - máx 25 fields por embed
 *   - máx 6000 chars sumando todos los embeds
 */
class CtaEmbedBuilder
{
    /** Límite de Discord para el value de un field. */
    private const FIELD_VALUE_MAX = 1024;

    /** Margen que reservamos para el sufijo "… y N más (ver en la web)". */
    private const SAFE_SUFFIX_MARGIN = 60;

    /** Límite de Discord para el title. */
    private const TITLE_MAX = 256;

    // ================================================================== //
    //                          EMBED PRINCIPAL                            //
    // ================================================================== //

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

        // ---------------------------------------------------------------
        // Construcción del listado de armas con truncado seguro
        // ---------------------------------------------------------------
        [$armasText, $asignadas, $totalArmas] = self::buildWeaponsField($cta, $asignaciones);

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

        // Truncar título defensivamente (por si acaso)
        $title = Str::limit($title, self::TITLE_MAX - 3, '…');

        // Truncar description defensivamente
        $description = Str::limit($description, 4096 - 3, '…');

        $embed = [
            'title'       => $title,
            'description' => $description,
            'color'       => $color,
            'timestamp'   => Carbon::now()->toIso8601String(),
            'fields'      => [
                [
                    'name'   => '⏰ Inicio',
                    'value'  => "<t:{$startTs}:F> (<t:{$startTs}:R>)",
                    'inline' => true,
                ],
                [
                    'name'   => '🏁 Fin',
                    'value'  => "<t:{$endTs}:t>",
                    'inline' => true,
                ],
                [
                    'name'   => '🔔 Recordatorio',
                    'value'  => Str::limit("{$cta->reminder_minutes} min antes", self::FIELD_VALUE_MAX - 3),
                    'inline' => true,
                ],
                [
                    'name'   => Str::limit(
                        "⚔️ Armas y asignación ({$asignadas}/{$totalArmas})",
                        self::FIELD_VALUE_MAX - 3,
                    ),
                    'value'  => $armasText,
                    'inline' => false,
                ],
            ],
            'footer' => [
                'text'     => Str::limit(
                    'Linhir · CTA gestionada por staff',
                    2048 - 3,
                ),
                'icon_url' => asset('imagenes/escudo_512x512.png'),
            ],
        ];

        if ($imageUrl) {
            $embed['image'] = ['url' => $imageUrl];
        }

        return $embed;
    }

    // ================================================================== //
    //                    LISTADO DE ARMAS (TRUNCADO)                      //
    // ================================================================== //

    /**
     * Construye el texto del field "Armas y asignación" respetando el límite
     * de 1024 caracteres de Discord.
     *
     * Estrategia:
     *   - Se ensambla línea por línea.
     *   - Se detiene cuando añadir otra línea superaría
     *     (1024 - SAFE_SUFFIX_MARGIN).
     *   - Si no caben todas, añade "… y N más (ver en la web)".
     *
     * @return array{0: string, 1: int, 2: int} [texto, asignadas, total]
     */
    private static function buildWeaponsField(Cta $cta, array $asignaciones): array
    {
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

        if (empty($lineas)) {
            return ['*Sin armas definidas*', 0, 0];
        }

        $maxLen = self::FIELD_VALUE_MAX - self::SAFE_SUFFIX_MARGIN;
        $out    = [];
        $len    = 0;
        $shown  = 0;
        $total  = count($lineas);

        foreach ($lineas as $linea) {
            // +1 por el "\n" separador (excepto en la primera línea)
            $extra = ($shown > 0 ? 1 : 0) + mb_strlen($linea);

            if ($len + $extra > $maxLen) {
                break;
            }

            $out[] = $linea;
            $len  += $extra;
            $shown++;
        }

        $texto = implode("\n", $out);

        if ($shown < $total) {
            $texto .= "\n… y **" . ($total - $shown) . "** más (ver en la web)";
        }

        // Red de seguridad final: si por alguna razón aún así se pasa (por
        // ejemplo, por emojis compuestos que Discord cuenta distinto),
        // truncamos bruto.
        if (mb_strlen($texto) > self::FIELD_VALUE_MAX) {
            $texto = mb_substr($texto, 0, self::FIELD_VALUE_MAX - 1) . '…';
        }

        return [$texto, $asignadas, $totalArmas];
    }

    // ================================================================== //
    //                          BOTONES DE ACCIÓN                          //
    // ================================================================== //

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