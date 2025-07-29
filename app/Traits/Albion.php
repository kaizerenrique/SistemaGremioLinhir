<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Models\PersonajeAlbion;
use App\Models\EstadisticaAlbion;
use Carbon\Carbon;

trait Albion 
{

    /**
	* Esta función realiza una consulta a la Pagina del gameinfo.albiononline 
    * para buscar información de los personajes por su nombre. 
	* 
	* @param string   $text	cadena de texto que contiene el nombre del personaje
	*
	* @return Retorna un array.
	*/	

    public function buscarpersonajepornombre($text)
	{
		// Validar que el texto no esté vacío
		if (empty($text)) {
			return false;
		}

		// Clave única para almacenar en caché
		$cacheKey = 'player_search_' . md5($text);

		// Intentar obtener los resultados desde la caché
		if (Cache::has($cacheKey)) {
			return Cache::get($cacheKey);
		}

		try {
			$url = 'https://gameinfo.albiononline.com/api/gameinfo/search?q=';
			$response = Http::timeout(10)->get($url . urlencode($text)); // Codificar el texto para la URL

			// Verificar si la respuesta fue exitosa
			if ($response->successful()) {
				$respuesta = json_decode($response->getBody()->getContents());

				// Verificar si hay resultados
				if (!empty($respuesta->players)) {
					// Almacenar en caché por 5 minutos (300 segundos)
					Cache::put($cacheKey, $respuesta->players, 300);
					return $respuesta->players;
				}
			}

			// Si no hay resultados o la respuesta falló
			return false;

		} catch (\Illuminate\Http\Client\ConnectionException $e) {
			// Manejar excepciones de conexión
			return false;
		}
	}

    /**
	* Esta función realiza una consulta a la Pagina del gameinfo.albiononline 
    * para buscar información de los personajes segun su id. 
	* 
	* @param string   $identificador cadena de texto que contiene el id de albion 
	* del personaje
	*
	* @return Retorna un array.
	*/

	public function consultarpersonaje($identificador) 
	{
		try {
            $url = 'https://gameinfo.albiononline.com/api/gameinfo/players/';
			$response = Http::get($url.$identificador);

			$respuesta = $response->getBody()->getContents();// accedemos a el contenido			

            $respuesta = json_decode($respuesta); //convertimos en json	

			return $respuesta;
				

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            //report($e);	 
	        return false;
        }

	}

    /**
	* Esta función realiza una consulta a la Pagina del gameinfo.albiononline 
    * para buscar información de los gremios por nombre. 
	* 
	* @param string   $text	cadena de texto que contiene el nombre del gremio
	*
	* @return Retorna un array.
	*/	

    public function buscargremiopornombre($text)
	{
        try {
            $url = 'https://gameinfo.albiononline.com/api/gameinfo/search?q=';
			$response = Http::get($url.$text);

			$respuesta = $response->getBody()->getContents();// accedemos a el contenido			

            $respuesta = json_decode($respuesta); //convertimos en json	

			if (!empty($respuesta->guilds)) {
				return $respuesta->guilds;
			} else {
				return false;
			}	

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            //report($e);	 
	        return false;
        }
	}

    /**
	* Esta función realiza una consulta a la Pagina del gameinfo.albiononline 
    * para buscar información de los gremios por su id. 
	* 
	* @param string   $text	cadena de texto que contiene el ID
	*
	* @return Retorna un array.
	*/	

    public function consultargremio($text)
	{
        try {
            $url = 'https://gameinfo.albiononline.com/api/gameinfo/guilds/';
			$response = Http::get($url.$text.'/data');

			$respuesta = $response->getBody()->getContents();// accedemos a el contenido			

            $respuesta = json_decode($respuesta); //convertimos en json	
			
			return $respuesta;			

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            //report($e);	 
	        return false;
        }
	}
    
    /**
	* Esta función realiza una consulta a la Pagina del gameinfo.albiononline 
    * para buscar información de los integrantes de linhir
	* 
	* @param string   $text	cadena de texto que contiene el ID
	*
	* @return Retorna un array.
	*/	

    public function integrantesdelgremiolinhir()
	{
		$linhir_id = config('app.linhir_gremio_id');
        try {
            $url = 'https://gameinfo.albiononline.com/api/gameinfo/guilds/';
			$response = Http::get($url.$linhir_id.'/members');

			$respuesta = $response->getBody()->getContents();// accedemos a el contenido	

            $integrantes = json_decode($respuesta);

			// Obtener los IDs de los miembros actuales del gremio
			$idsIntegrantesActuales = collect($integrantes)->pluck('Id')->toArray();

			// Obtener todos los personajes registrados en la base de datos que pertenecen al gremio
			$personajesRegistrados = PersonajeAlbion::where('GuildId', $linhir_id)->get();

			// Marcar como "no miembro" y actualizar el GuildId de los personajes que ya no están en el gremio
			foreach ($personajesRegistrados as $personaje) {
				if (!in_array($personaje->Id_albion, $idsIntegrantesActuales)) {
					// Buscar información actual del personaje en la API
					$urlPersonaje = 'https://gameinfo.albiononline.com/api/gameinfo/players/' . $personaje->Id_albion;
					$responsePersonaje = Http::get($urlPersonaje);
	
					if ($responsePersonaje->successful()) {
						$infoPersonaje = json_decode($responsePersonaje->getBody()->getContents());
	
						// Actualizar el GuildId con el nuevo gremio o null si no tiene
						$personaje->update([
							'GuildId' => $infoPersonaje->GuildId ?? null,
							'miembro' => false,
						]);
					} else {
						// Si no se puede obtener la información, marcar como "no miembro" y GuildId como null
						$personaje->update([
							'GuildId' => null,
							'miembro' => false,
						]);
					}
				}
			}
	
			// Registrar nuevos miembros o actualizar los existentes
			foreach ($integrantes as $integrante) {
				PersonajeAlbion::updateOrCreate(
					['Id_albion' => $integrante->Id],
					[
						'Name' => $integrante->Name,
						'GuildId' => $integrante->GuildId,
						'miembro' => true,
					]
				);

				$this->guardarEstadisticasDiarias($personaje->id);
			}
	
			return true;
			

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            //report($e);	 
	        return false;
        }
	}

	/**
	 * Esta función realiza una consulta a la Pagina del gameinfo.albiononline
	 * para buscar información de los personajes registrados
	 */
	private function guardarEstadisticasDiarias($personajeId)
	{
		$personaje = PersonajeAlbion::find($personajeId);
		if (!$personaje) return;

		try {
			$urlPlayer = 'https://gameinfo.albiononline.com/api/gameinfo/players/' . $personaje->Id_albion;
			$responsePlayer = Http::retry(3, 500)->get($urlPlayer);

			if ($responsePlayer->successful()) {
				$playerData = $responsePlayer->json();
				$stats = $playerData['LifetimeStatistics'] ?? null;
				
				if ($stats) {
					// Verificar si ya existe registro hoy
					$existing = EstadisticaAlbion::where('personaje_albion_id', $personajeId)
						->whereDate('fecha_registro', today())
						->exists();
					
					if (!$existing) {
						EstadisticaAlbion::create([
							'personaje_albion_id' => $personajeId,
							'pve_total' => $stats['PvE']['Total'] ?? 0,
							'pve_royal' => $stats['PvE']['Royal'] ?? 0,
							'pve_outlands' => $stats['PvE']['Outlands'] ?? 0,
							'pve_avalon' => $stats['PvE']['Avalon'] ?? 0,
							'gathering_total' => $stats['Gathering']['All']['Total'] ?? 0,
							'gathering_fiber' => $stats['Gathering']['Fiber']['Total'] ?? 0,
							'gathering_hide' => $stats['Gathering']['Hide']['Total'] ?? 0,
							'gathering_ore' => $stats['Gathering']['Ore']['Total'] ?? 0,
							'gathering_rock' => $stats['Gathering']['Rock']['Total'] ?? 0,
							'gathering_wood' => $stats['Gathering']['Wood']['Total'] ?? 0,
							'crafting_total' => $stats['Crafting']['Total'] ?? 0,
							'fishing_fame' => $stats['FishingFame'] ?? 0,
							'farming_fame' => $stats['FarmingFame'] ?? 0,
							'fecha_registro' => now()
						]);
					}
				}
			}
			
			usleep(200000); // 200ms
			
		} catch (\Exception $e) {
			Log::error("Error guardando estadísticas para {$personaje->Id_albion}: " . $e->getMessage());
		}
	}
}