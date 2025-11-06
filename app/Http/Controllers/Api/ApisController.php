<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoldPrice;
use App\Traits\Albion;
use Carbon\Carbon;

class ApisController extends Controller
{
    use Albion;

    /**
     * Retorna el valor del oro del juego
     *
     * @return array
     */
    public function valordeloro()
    {
        // Obtener el último registro de la tabla GoldPrice
        $oro = GoldPrice::latest('id')->first();

        // Verificar si se encontró el registro
        if (! $oro) {
            return response()->json([
                'error' => 'No se encontraron registros de oro',
            ], 404);
        }

        // Retornar los datos en formato JSON con los campos requeridos
        return response()->json([
            'price' => $oro->price, // Asegúrate de que el campo se llama 'price'
            'timestamp' => $oro->created_at->format('Y-m-d H:i:s'), // Formatea el timestamp
        ]);
    }

    /**
     * Retorna el horario UTC actual
     *
     * @param  string  $format  Formato personalizado
     * @return string
     */
    public function horario()
    {
        $now = Carbon::now('UTC');
        
        $timezones = [
            'utc' => 'UTC',
            'argentina' => 'America/Argentina/Buenos_Aires',
            'mexico' => 'America/Mexico_City',
            'chile' => 'America/Santiago',
            'colombia' => 'America/Bogota',
            'venezuela' => 'America/Caracas',
            'peru' => 'America/Lima',
            'espana' => 'Europe/Madrid'
        ];
        
        $response = [
            'timestamp' => $now->timestamp,
            'utc' => $now->format('Y-m-d H:i:s') . ' UTC'
        ];
        
        foreach ($timezones as $key => $timezone) {
            if ($key === 'utc') continue;
            
            $localTime = $now->copy()->setTimezone($timezone);
            $response[$key] = $localTime->format('Y-m-d H:i:s');
        }
        
        return $response;

    }
}
