<?php

namespace App\Console\Commands;

use App\Models\Personaje;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordBirthdayNotification extends Command
{
    protected $signature = 'discord:birthday-notification
                            {--test : Ejecutar en modo de prueba sin enviar realmente}';

    protected $description = 'Envía notificaciones de cumpleaños al canal de Discord, diferenciando entre miembros de Linhir y aliados de la HRL';

    public function handle(): int
    {
        $this->info('🔍 Buscando cumpleaños de hoy...');

        $today      = Carbon::today();
        $isTestMode = (bool) $this->option('test');

        $birthdayPeople = Personaje::with('alliedGuild')
            ->whereNotNull('birthdate')
            ->whereMonth('birthdate', $today->month)
            ->whereDay('birthdate', $today->day)
            ->orderBy('Name')
            ->get();

        if ($birthdayPeople->isEmpty()) {
            $this->info('✅ No hay cumpleaños hoy.');
            Log::info('No hay cumpleaños hoy.');
            return self::SUCCESS;
        }

        // Separar: miembros de Linhir vs aliados de la HRL
        $members = $birthdayPeople->filter(fn ($p) => $p->miembro === true);
        $allies  = $birthdayPeople->filter(
            fn ($p) => $p->miembro !== true && $p->allied_guild_id !== null
        );

        $this->info(sprintf(
            '🎂 Total: %d | Miembros Linhir: %d | Aliados HRL: %d',
            $birthdayPeople->count(),
            $members->count(),
            $allies->count()
        ));

        $webhookUrl = config('app.canal_cumpleanos');

        if (!$webhookUrl) {
            $this->error('❌ Webhook de cumpleaños no configurado. Define DISCORD_BIRTHDAY_WEBHOOK_URL en .env');
            Log::error('Webhook de Discord para cumpleaños no configurado');
            return self::FAILURE;
        }

        if ($members->isNotEmpty()) {
            $this->dispatchGroup($members, $today, 'linhir', $webhookUrl, $isTestMode);
        }

        if ($allies->isNotEmpty()) {
            $this->dispatchGroup($allies, $today, 'aliado', $webhookUrl, $isTestMode);
        }

        return self::SUCCESS;
    }

    /**
     * Envía un mensaje webhook para un grupo (miembros o aliados).
     */
    private function dispatchGroup(
        $people,
        Carbon $today,
        string $type,
        string $webhookUrl,
        bool $isTestMode
    ): void {
        $message = $this->buildMessage($people, $today, $type);
        $label   = $type === 'linhir' ? 'Linhir' : 'Aliados HRL';

        if ($isTestMode) {
            $this->info("🧪 MODO PRUEBA - Mensaje para {$label}:");
            $this->line(json_encode(
                $message,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ));
            return;
        }

        try {
            $response = Http::timeout(15)->post($webhookUrl, $message);

            if ($response->successful()) {
                $this->info("✅ Notificación de cumpleaños ({$label}) enviada a Discord.");
                Log::info("Notificación de cumpleaños ({$label}) enviada", [
                    'cumpleañeros' => $people->pluck('Name')->toArray(),
                    'fecha'        => $today->format('Y-m-d'),
                ]);
            } else {
                $this->error("❌ Error al enviar ({$label}): HTTP {$response->status()}");
                Log::error("Fallo webhook cumpleaños ({$label})", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            $this->error("❌ Excepción al enviar ({$label}): " . $e->getMessage());
            Log::error("Excepción webhook cumpleaños ({$label})", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Construye el payload del webhook según el grupo.
     *
     * La "imagen" se coloca en el campo `image` del embed (imagen grande
     * inferior). Si prefieres que sea pequeña arriba a la derecha,
     * cambia 'image' por 'thumbnail'.
     */
    private function buildMessage($people, Carbon $today, string $type): array
    {
        $count   = $people->count();
        $escudo  = asset('imagenes/linhir_escudo_180.png');
        $mention = config('app.birthday_mention_role', '@everyone');

        // ---- Configuración según el grupo --------------------------------
        if ($type === 'linhir') {
            $image      = asset('imagenes/cumpleaños/Gremio_Linhir.png');
            $color      = 0xC54B47;   // primary de Linhir
            $username   = 'Linhir - Cumpleaños';
            $titleEmoji = '⚔️';
            $groupLabel = 'Miembro de Linhir';
            $groupBadge = '🎖️ Miembro del Gremio';
        } else {
            $image      = asset('imagenes/cumpleaños/Alianza.png');
            $color      = 0xD4B44A;   // accent (dorado aliados)
            $username   = 'Linhir - Aliados HRL';
            $titleEmoji = '🤝';
            $groupLabel = 'Aliado de la HRL';
            $groupBadge = '🌟 Aliado del Gremio';
        }

        $title = $count === 1
            ? "{$titleEmoji} ¡FELIZ CUMPLEAÑOS, {$groupLabel}!"
            : "{$titleEmoji} ¡{$count} CUMPLEAÑOS DE {$groupLabel}S HOY!";

        // ---- Descripción con nombres grandes -----------------------------
        // Discord renderiza "# Nombre" como H1 (tamaño máximo).
        $description = "## 🎈 ¡HOY CELEBRAMOS!\n\n";

        foreach ($people as $personaje) {
            $birthdate = Carbon::parse($personaje->birthdate);

            // Nombre en H1 = el tamaño más grande posible en Discord
            $description .= "# 🎂 {$personaje->Name}\n";

            // Línea secundaria (subtexto, tamaño reducido)
            $extras = [
                '📅 ' . $birthdate->format('d/m'),
                $groupBadge,
            ];

            if ($type === 'aliado' && $personaje->alliedGuild) {
                $extras[] = '🏛️ ' . $personaje->alliedGuild->guild_name;
            }

            $description .= '-# ' . implode(' · ', $extras) . "\n\n";
            $description .= "━━━━━━━━━━━━━━━━━━\n\n";
        }

        // Discord: descripción máximo 4096 caracteres
        if (mb_strlen($description) > 4000) {
            $description = mb_substr($description, 0, 3900)
                         . "\n\n-# …y más cumpleañeros hoy.";
        }

        return [
            'username'   => $username,
            'avatar_url' => $escudo,
            'content'    => $mention,
            'embeds'     => [[
                'title'       => $title,
                'color'       => $color,
                'image'       => ['url' => $image],   // imagen grande inferior
                'description' => $description,
                'fields'      => [
                    [
                        'name'   => '🎁 MENSAJE DEL GREMIO',
                        'value'  => '¡Que tengas un día maravilloso lleno de alegría, éxitos y muchas bendiciones! 🥳✨',
                        'inline' => false,
                    ],
                ],
                'footer' => [
                    'text'     => 'Linhir System • Cumpleaños • ' . $today->format('d/m/Y'),
                    'icon_url' => $escudo,
                ],
                'timestamp' => $today->toIso8601String(),
            ]],
        ];
    }
}