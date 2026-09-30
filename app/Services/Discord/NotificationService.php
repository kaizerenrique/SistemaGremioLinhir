<?php

namespace App\Services\Discord;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Token del bot de Discord.
     */
    protected ?string $botToken;

    /**
     * Base de la API de Discord.
     */
    protected string $apiBase = 'https://discord.com/api/v10';

    /**
     * Timeout para las peticiones HTTP (segundos).
     */
    protected int $timeout = 10;

    public function __construct()
    {
        $this->botToken = config('services.discord.bot_token');
    }

    /**
     * Envía un mensaje directo a un usuario de Discord.
     *
     * @return bool true si el DM se entregó, false en caso contrario.
     */
    public function sendDirectMessage(string $discordUserId, string $message): bool
    {
        if (empty($this->botToken)) {
            Log::warning('NotificationService: bot_token no configurado, DM no enviado.', [
                'discord_user_id' => $discordUserId,
            ]);
            return false;
        }

        try {
            // ---------------------------------------------------------
            // 1. Crear (u obtener) el canal DM con el usuario
            // ---------------------------------------------------------
            $channelResponse = Http::withToken($this->botToken, 'Bot')
                ->timeout($this->timeout)
                ->post("{$this->apiBase}/users/@me/channels", [
                    'recipient_id' => $discordUserId,
                ]);

            if (!$channelResponse->successful()) {
                Log::warning('NotificationService: no se pudo crear el canal DM.', [
                    'discord_user_id' => $discordUserId,
                    'status'          => $channelResponse->status(),
                    'body'            => $channelResponse->body(),
                ]);
                return false;
            }

            $channelId = $channelResponse->json('id');

            if (empty($channelId)) {
                Log::warning('NotificationService: respuesta sin channel id.', [
                    'discord_user_id' => $discordUserId,
                ]);
                return false;
            }

            // ---------------------------------------------------------
            // 2. Enviar el mensaje al canal DM
            // ---------------------------------------------------------
            $messageResponse = Http::withToken($this->botToken, 'Bot')
                ->timeout($this->timeout)
                ->post("{$this->apiBase}/channels/{$channelId}/messages", [
                    'content' => $message,
                ]);

            if (!$messageResponse->successful()) {
                Log::warning('NotificationService: no se pudo enviar el DM.', [
                    'discord_user_id' => $discordUserId,
                    'channel_id'      => $channelId,
                    'status'          => $messageResponse->status(),
                    'body'            => $messageResponse->body(),
                ]);
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            Log::error('NotificationService: excepción al enviar DM.', [
                'discord_user_id' => $discordUserId,
                'error'           => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Envía un payload a un webhook de Discord.
     *
     * @return bool true si el webhook respondió OK, false en caso contrario.
     */
    public function sendWebhook(string $webhookUrl, array $payload): bool
    {
        try {
            $response = Http::timeout($this->timeout)->post($webhookUrl, $payload);

            if (!$response->successful()) {
                Log::warning('NotificationService: fallo al enviar webhook.', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            Log::error('NotificationService: excepción al enviar webhook.', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}