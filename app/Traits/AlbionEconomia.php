<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\GoldPrice;


trait AlbionEconomia
{    


    public function consultarvalordeloro()
    {
        $url = 'https://old.west.albion-online-data.com/api/v2/stats/Gold?count=1';
        
        try {
            $response = Http::get($url);
            
            if ($response->successful()) {
                $oroData = $response->json();
                
                if (empty($oroData)) {
                    throw new \Exception("La API devolvió datos vacíos");
                }
                
                $precioOro = $oroData[0]['price'] ?? null;
                $timestampAPI = $oroData[0]['timestamp'] ?? null;
                
                if (!$precioOro || !$timestampAPI) {
                    throw new \Exception("Datos incompletos en la respuesta de la API");
                }
                
                $fechaAPI = Carbon::parse($timestampAPI);
                $ultimoRegistro = GoldPrice::latest('id')->first();
                
                // Verificar si el precio es diferente
                if ($ultimoRegistro && $ultimoRegistro->price == $precioOro) {
                    return [
                        'status' => 'no-change',
                        'precio_actual' => $precioOro,
                        'timestamp' => $fechaAPI,
                        'mensaje' => 'El precio del oro no ha cambiado',
                        'ultimo_registro' => $ultimoRegistro,
                        'nuevo_registro' => null
                    ];
                }
                
                // Almacenar nuevo valor
                $nuevoRegistro = GoldPrice::create([
                    'price' => $precioOro,
                    'timestamp' => $fechaAPI
                ]);
                
                return [
                    'status' => 'success',
                    'precio_actual' => $precioOro,
                    'timestamp' => $fechaAPI,
                    'mensaje' => 'Nuevo precio almacenado',
                    'ultimo_registro' => $ultimoRegistro,
                    'nuevo_registro' => $nuevoRegistro
                ];
            } else {
                throw new \Exception("Error en la API: " . $response->status());
            }
        } catch (\Exception $e) {
            $ultimoRegistro = GoldPrice::latest('id')->first();
            
            return [
                'status' => 'error',
                'precio_actual' => $ultimoRegistro ? $ultimoRegistro->price : null,
                'mensaje' => $e->getMessage(),
                'error_details' => [
                    'code' => $e->getCode(),
                    'timestamp' => now()->toDateTimeString()
                ]
            ];
        }
    }

} 
