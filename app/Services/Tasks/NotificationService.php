<?php

namespace App\Services\Discord;

use Illuminate\Support\Facades\Http;

class NotificationService
{
    protected string $botToken;

    public function __construct()
    {
        $this->botToken = config('services.discord.bot_token');
    }

    /**
     * Enviar un mensaje directo a un usuario de Discord.
     */
    public function sendDirectMessage(string $discordUserId, string $message): void
    {
        if (empty($this->botToken)) {
            return; // O lanzar excepción si es crítico
        }

        // Crear canal DM
        $channelResponse = Http::withToken($this->botToken, 'Bot')
            ->post('https://discord.com/api/v10/users/@me/channels', [
                'recipient_id' => $discordUserId,
            ]);

        if (!$channelResponse->successful()) {
            // Log o manejo silencioso (puede ser que el usuario tenga DMs bloqueados)
            return;
        }

        $channelId = $channelResponse->json('id');

        // Enviar mensaje
        Http::withToken($this->botToken, 'Bot')
            ->post("https://discord.com/api/v10/channels/{$channelId}/messages", [
                'content' => $message,
            ]);
    }

    /**
     * Enviar mensaje a un canal mediante webhook.
     */
    public function sendWebhook(string $webhookUrl, array $payload): void
    {
        Http::post($webhookUrl, $payload);
    }
}