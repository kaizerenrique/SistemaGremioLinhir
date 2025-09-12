<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoldPrice;
use App\Traits\Albion;
use App\Models\Personaje;
use Illuminate\Support\Facades\Validator;

class ApisController extends Controller
{
    use Albion;

    public function valordeloro()
    {
        // Obtener el último registro de la tabla GoldPrice
        $oro = GoldPrice::latest('id')->first();

        // Verificar si se encontró el registro
        if (!$oro) {
            return response()->json([
                'error' => 'No se encontraron registros de oro'
            ], 404);
        }

        // Retornar los datos en formato JSON con los campos requeridos
        return response()->json([
            'price' => $oro->price, // Asegúrate de que el campo se llama 'price'
            'timestamp' => $oro->created_at->format('Y-m-d H:i:s') // Formatea el timestamp
        ]);
    }

    public function nombredepersonaje($nombredelpersonaje)
    {
        // Validar que el nombre del personaje sea una cadena no vacía
        if (!is_string($nombredelpersonaje) || empty(trim($nombredelpersonaje))) {
            return response()->json([
                'error' => 'El nombre del personaje debe ser una cadena de texto válida y no vacía'
            ], 400);
        }

        // Limpiar y normalizar el nombre de búsqueda
        $nombreBuscado = trim($nombredelpersonaje);
        $nombreBuscado = htmlspecialchars($nombreBuscado, ENT_QUOTES, 'UTF-8');

        $informacion = $this->buscarpersonajepornombre($nombreBuscado);

        // Verificar si se encontró información del personaje
        if (empty($informacion)) {
            return response()->json(['error' => 'Personaje no encontrado'], 404);
        }

        // Buscar coincidencia exacta del nombre (case-insensitive)
        $coincidenciaExacta = null;
        $coincidenciasParciales = [];

        foreach ($informacion as $personaje) {
            // Convertir a array si es objeto
            $personajeArray = (array) $personaje;
            
            // Verificar coincidencia exacta (insensible a mayúsculas/minúsculas)
            if (strcasecmp($personajeArray['Name'], $nombreBuscado) === 0) {
                $coincidenciaExacta = $personajeArray;
                break; // Encontramos coincidencia exacta, salimos del loop
            }
            
            // Si no hay coincidencia exacta, guardar todas las coincidencias parciales
            $coincidenciasParciales[] = $personajeArray;
        }

        // Priorizar la coincidencia exacta si existe
        if ($coincidenciaExacta) {
            return response()->json([$coincidenciaExacta]);
        }

        // Filtrar campos específicos
        $filtrarCampos = function($personaje) {
            return [
                'Id' => $personaje['Id'] ?? null,
                'Name' => $personaje['Name'] ?? null,
                'GuildId' => $personaje['GuildId'] ?? null,
                'GuildName' => $personaje['GuildName'] ?? null,
                'AllianceId' => $personaje['AllianceId'] ?? null,
                'AllianceName' => $personaje['AllianceName'] ?? null
            ];
        };

        if ($coincidenciaExacta) {
            return response()->json([$filtrarCampos($coincidenciaExacta)]);
        }

        // Filtrar campos en resultados parciales
        $resultadosFiltrados = array_map($filtrarCampos, $coincidenciasParciales);

        // Si no hay coincidencia exacta, devolver todas las coincidencias parciales
        // pero con un indicador de que no es una coincidencia exacta
        return response()->json([
            'advertencia' => 'No se encontró una coincidencia exacta, mostrando resultados similares',
            'resultados' => $coincidenciasParciales
        ]);
    }

    public function vincularPersonajeDiscord(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'id_personaje' => 'required|string|max:255',
            'id_discord' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos de entrada inválidos',
                'detalles' => $validator->errors()
            ], 400);
        }

        $linhir_id = config('app.linhir_gremio_id');

        try {
            // Obtener los datos validados
            $validated = $validator->validated();
            
            // Buscar el personaje por ID de Albion que pertenezca a Linhir
            $personaje = Personaje::where('Id_albion', $validated['id_personaje'])
                                ->where('GuildId', $linhir_id ) // ID de Linhir
                                ->first();

            if (!$personaje) {
                return response()->json([
                    'error' => 'Personaje no encontrado o no pertenece al gremio Linhir'
                ], 404);
            }

            // Actualizar el ID de Discord
            $personaje->discord_user_id = $validated['id_discord'];
            $personaje->save();

            return response()->json([
                'success' => true,
                'message' => 'Personaje vinculado con Discord correctamente',
                'data' => [
                    'id_personaje' => $personaje->Id_albion,
                    'nombre_personaje' => $personaje->Name,
                    'id_discord' => $personaje->discord_user_id,
                    'gremio' => 'Linhir'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al vincular personaje con Discord: ' . $e->getMessage(), [
                'id_personaje' => $validated['id_personaje'] ?? 'n/a',
                'id_discord' => $validated['id_discord'] ?? 'n/a'
            ]);

            return response()->json([
                'error' => 'Error interno del servidor al procesar la solicitud'
            ], 500);
        }
    }

}
