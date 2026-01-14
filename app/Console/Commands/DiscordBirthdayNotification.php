<?php

namespace App\Console\Commands;

use App\Models\Personaje;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class DiscordBirthdayNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:birthday-notification
                            {--test : Ejecutar en modo de prueba sin enviar realmente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía notificaciones de cumpleaños al canal de Discord del gremio usando webhook';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Buscando cumpleaños de hoy...');
        
        $today = Carbon::today();
        $isTestMode = $this->option('test');
        
        // Buscar personajes cuyo cumpleaños sea hoy (ignorando el año)
        $birthdayPeople = Personaje::whereNotNull('birthdate')
            ->whereMonth('birthdate', $today->month)
            ->whereDay('birthdate', $today->day)
            ->orderBy('Name')
            ->get();
        
        if ($birthdayPeople->isEmpty()) {
            $this->info('✅ No hay cumpleaños hoy.');
            Log::info('No hay cumpleaños hoy.');
            return;
        }
        
        $this->info('🎂 Encontrados ' . $birthdayPeople->count() . ' cumpleaños.');
        
        // Crear el mensaje para Discord (formato webhook)
        $messageData = $this->createDiscordWebhookMessage($birthdayPeople, $today);
        
        // En modo prueba, solo mostraríamos el mensaje
        if ($isTestMode) {
            $this->info('🧪 MODO PRUEBA - No se enviará realmente a Discord');
            $this->info('📝 Mensaje que se enviaría:');
            $this->info(json_encode($messageData, JSON_PRETTY_PRINT));
            return;
        }
        
        // Obtener el webhook desde la configuración
        $webhookUrl = config('app.canal_cumpleanos');
        
        if (!$webhookUrl) {
            $this->error('❌ Webhook de cumpleaños no configurado. Por favor, establece DISCORD_BIRTHDAY_WEBHOOK_URL en .env');
            Log::error('Webhook de Discord para cumpleaños no configurado');
            return;
        }
        
        // Enviar a Discord usando webhook (igual que tu función status_server)
        try {
            $response = $this->sendWebhookMessage($webhookUrl, $messageData);
            
            if ($response) {
                $this->info('✅ Notificación enviada exitosamente a Discord!');
                Log::info('Notificación de cumpleaños enviada a Discord', [
                    'cumpleañeros' => $birthdayPeople->pluck('Name')->toArray(),
                    'fecha' => $today->format('Y-m-d'),
                    'webhook' => $webhookUrl
                ]);
            } else {
                $this->error('❌ Error al enviar notificación a Discord');
                Log::error('Error al enviar notificación de cumpleaños a Discord');
            }
        } catch (\Exception $e) {
            $this->error('❌ Excepción al enviar a Discord: ' . $e->getMessage());
            Log::error('Excepción en notificación de cumpleaños', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Crear el mensaje embellecido para Discord (formato webhook)
     */
    private function createDiscordWebhookMessage($birthdayPeople, $today)
    {
        $count = $birthdayPeople->count();
        $escudo = asset('imagenes/linhir_escudo_180.png');
        $imagendecumpleaños = asset('imagenes/birthday-cake.mp4');
        
        $title = $count === 1 
            ? "🎉 ¡HOY ES EL CUMPLEAÑOS DE UN MIEMBRO DEL GREMIO! 🎂" 
            : "🎉 ¡HOY ES EL CUMPLEAÑOS DE {$count} MIEMBROS DEL GREMIO! 🎂";
        
        // Construir descripción con información de cada cumpleañero
        $description = "## 🎈 CUMPLEAÑEROS DEL DÍA:\n\n";
        
        foreach ($birthdayPeople as $personaje) {
            $birthdate = Carbon::parse($personaje->birthdate);
            
            
            $description .= "**👤 {$personaje->Name}**\n";    
            
            $description .= "🗓️ **Fecha:** " . $birthdate->format('d/m/Y') . "\n";
            
            // Separador entre personas
            $description .= "━━━━━━━━━━━━━━━━━━\n";
        }
        
        // Color según la cantidad de cumpleañeros
        $color = $count === 1 ? 5763719 : 15844367; // Verde o dorado
        
        return [
            "username" => "Linhir - Sistema de Cumpleaños",
            "avatar_url" => $escudo,
            "content" => "<@&1291436337949446247>", // Menciona a todos
            "embeds" => [
                [
                    "title" => $title,
                    "color" => $color,
                    "thumbnail" => [
                        "url" => $imagendecumpleaños // Icono de pastel
                    ],
                    "description" => $description,
                    "fields" => [
                        [
                            "name" => "🎁 MENSAJE DEL GREMIO",
                            "value" => "¡Que tengas un día maravilloso lleno de alegría, éxitos y muchas bendiciones! 🥳✨",
                            "inline" => false
                        ],
                        [
                            "name" => "📣 SALUDOS",
                            "value" => "¡Deja tus buenos deseos en el chat! 🎊",
                            "inline" => false
                        ]
                    ],
                    "footer" => [
                        "text" => "Linhir System • Sistema de Cumpleaños • " . $today->format('d/m/Y'),
                        "icon_url" => $escudo
                    ],
                    "timestamp" => $today->toISOString()
                ]
            ]
        ];
    }
    
    /**
     * Enviar mensaje usando webhook (igual que tu función status_server)
     */
    private function sendWebhookMessage($webhookUrl, $messageData)
    {
        try {
            $client = new Client();
            
            $response = $client->post($webhookUrl, [
                'json' => $messageData,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'verify' => false, // Solo si tienes problemas con SSL
            ]);
            
            return $response->getBody();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('ClientException al enviar webhook: ' . $e->getMessage());
            $this->error('Error de cliente: ' . $e->getMessage());
            return false;
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            Log::error('ServerException al enviar webhook: ' . $e->getMessage());
            $this->error('Error del servidor: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error('Excepción al enviar webhook: ' . $e->getMessage());
            $this->error('Excepción: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Método alternativo usando Laravel Http (más simple)
     */
    private function sendWebhookMessageSimple($webhookUrl, $messageData)
    {
        try {
            $response = Http::post($webhookUrl, $messageData);
            
            if ($response->successful()) {
                return true;
            } else {
                Log::error('Error en webhook: ' . $response->status() . ' - ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Excepción en webhook: ' . $e->getMessage());
            return false;
        }
    }
}