<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\Serverstatus;

trait Estadodelservidor 
{
    /**
	* Esta función realiza una consulta a la Pagina de https://serverstatus.albiononline.com/ 
    * para buscar información del estado del servidor. * 
	*
	*
	* @return Retorna un array con el estado del servidor y un mensaje.
	*/
    
    public function consultar_estado_del_servidor()
    {
        try {
            $url = 'https://serverstatus.albiononline.com/';
            $response = Http::get($url);
            $respuesta = $response->getBody()->getContents();// accedemos a el contenido	
            $respuesta = json_decode($respuesta);
            return $respuesta;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return false;
        }
    }

    /**
	* Esta función realiza una consulta sobre el estado del servidor y la compara 
    * con la ultima informacion conocida para luego enviar un mensaje al discord
	* del gremio linhir con esa informacion
	*
	* @return Retorna un array con el estado del servidor y un mensaje.
	*/

    public function guardar_estado()
    {
        $status = $this->consultar_estado_del_servidor();

        if ($status == false) {
            $status = 'error';
        } else {
            $ultimoregistro = Serverstatus::orderBy('created_at', 'desc')->first();

            if ($ultimoregistro == null) {                
                $registro = Serverstatus::create([
                    'status' => $status->status,
                    'message' => $status->message,
                ]);
                $this->status_server($status);
                
            } else {
                
                if ($ultimoregistro->status == $status->status) {                   
                    
                    return $status;
                } else {
                    $registro = Serverstatus::create([
                        'status' => $status->status,
                        'message' => $status->message,
                    ]);
                    $this->status_server($status);
                    return $status;
                }                
            }            
        } 
        
    }

     /**
	* Esta función envia un mensaje a discord informando el estado del servidor
	*
	* @return Retorna un array con el estado del servidor y un mensaje.
	*/

    public function status_server($statusData)
    {
        // Normalizar estado a minúsculas para validación
        $serverStatus = strtolower($statusData->status);
        
        // Validar estados permitidos
        if (!in_array($serverStatus, ['online', 'offline'])) {
            return response()->json(['error' => 'Estado no soportado: '.$statusData->status], 400);
        }

        $canal_estado_servidor = config('app.canal_estado_servidor');     
        $client = new Client();
        $escudo = asset('/imagenes/linhir_escudo_180.png');
        
        // Configuración dinámica según estado
        $isOnline = ($serverStatus == 'online');
        $color = $isOnline ? 5763719 : 15548997; // Verde azulado/Rojo discord
        $titulo = $isOnline ? "✅ SERVIDOR EN LÍNEA" : "❌ SERVIDOR OFFLINE";
        $estadoIcono = $isOnline ? "🟢" : "🔴";
        
        $embed = [
            "title" => $titulo,
            "color" => $color,
            "thumbnail" => ["url" => $escudo],
            "description" => "**Estado actualizado del servidor de Albion Online**",
            "fields" => [
                [
                    "name" => "Estado del Servidor",
                    "value" => "{$estadoIcono} **" . ucfirst($serverStatus) . "**",
                    "inline" => true
                ],
                [
                    "name" => "Mensaje del Sistema",
                    "value" => "```{$statusData->message}```",
                    "inline" => true
                ],
                [
                    "name" => "Última Verificación",
                    "value" => Carbon::now()->format('d/m/Y H:i:s') . " UTC",
                    "inline" => true
                ],
                [
                    "name" => "Plataforma",
                    "value" => "```Albion Online - Servidor America```",
                    "inline" => false
                ]
            ],
            "footer" => [
                "text" => "Linhir System Monitor • Actualizado",
                "icon_url" => $escudo
            ],
            "timestamp" => Carbon::now()->toIso8601String()
        ];

        $response = Http::post($canal_estado_servidor, [
            'json' => [
                'username'   => 'Linhir System Monitor',
                'avatar_url' => $escudo,
                'embeds'     => [$embed],
            ],
        ]);

        return $response->body();
    }
        

}