<?php

namespace App\Services\Discord;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Servicio de publicación de mensajes y eventos en Discord vía Bot API.
 *
 * Usado por componentes Livewire que necesitan publicar embeds con
 * botones y obtener de vuelta los IDs de Discord (mensaje, canal, evento)
 * para persistirlos en la base de datos.
 */
class DiscordMessageService
{
    /** Token del bot (config: services.discord.bot_token). */
    protected ?string $botToken;

    /** Base de la API de Discord. */
    protected string $apiBase = 'https://discord.com/api/v10';

    /** Timeout HTTP (segundos). */
    protected int $timeout = 15;

    public function __construct()
    {
        $this->botToken = config('services.discord.bot_token');
    }

    /**
     * Publica un mensaje en un canal de Discord.
     *
     * El payload sigue el formato oficial de Discord:
     * https://discord.com/developers/docs/resources/message#create-message
     *
     * @param  string $channelId  ID del canal destino
     * @param  array  $payload    Payload (embeds, components, content...)
     * @return array|null         Respuesta de Discord o null si falla
     */
    public function sendMessage(string $channelId, array $payload): ?array
    {
        if (empty($this->botToken)) {
            Log::warning('DiscordMessageService: bot_token no configurado.');
            return null;
        }

        if (empty($channelId)) {
            Log::warning('DiscordMessageService: channel_id vacío.');
            return null;
        }

        try {
            $response = Http::withToken($this->botToken, 'Bot')
                ->timeout($this->timeout)
                ->post("{$this->apiBase}/channels/{$channelId}/messages", $payload);

            if (!$response->successful()) {
                Log::warning('DiscordMessageService: envío fallido', [
                    'channel_id' => $channelId,
                    'status'     => $response->status(),
                    'body'       => Str::limit($response->body(), 500),
                ]);
                return null;
            }

            return $response->json();

        } catch (\Throwable $e) {
            Log::error('DiscordMessageService: excepción al enviar mensaje', [
                'channel_id' => $channelId,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Crea un evento programado en un servidor de Discord.
     *
     * @param  string $guildId  ID del servidor
     * @param  array  $payload  Payload (name, scheduled_start_time, ...)
     * @return array|null
     */
    public function createScheduledEvent(string $guildId, array $payload): ?array
    {
        if (empty($this->botToken)) {
            return null;
        }

        try {
            $response = Http::withToken($this->botToken, 'Bot')
                ->timeout($this->timeout)
                ->post("{$this->apiBase}/guilds/{$guildId}/scheduled-events", $payload);

            if (!$response->successful()) {
                Log::warning('DiscordMessageService: creación de evento fallida', [
                    'guild_id' => $guildId,
                    'status'   => $response->status(),
                    'body'     => Str::limit($response->body(), 500),
                ]);
                return null;
            }

            return $response->json();

        } catch (\Throwable $e) {
            Log::error('DiscordMessageService: excepción al crear evento', [
                'guild_id' => $guildId,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }
}