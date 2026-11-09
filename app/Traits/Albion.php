<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Models\Personaje;
use App\Models\GatheringStatistics;
use App\Models\LifetimeStatistics;
use Carbon\Carbon;
use App\Models\FamaSemanal;
use Illuminate\Support\Facades\DB;
use App\Models\EspecialidadSemanal;
use Illuminate\Support\Facades\Log;

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
			$response = Http::get($url . $identificador);

			$respuesta = $response->getBody()->getContents(); // accedemos a el contenido			

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
			$response = Http::get($url . $text);

			$respuesta = $response->getBody()->getContents(); // accedemos a el contenido			

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
			$response = Http::get($url . $text . '/data');

			$respuesta = $response->getBody()->getContents(); // accedemos a el contenido			

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

		if (empty($linhir_id)) {
			Log::error('integrantesdelgremiolinhir: LINHIR_GREMIO_ID no configurado');
			return false;
		}

		try {
			$url = "https://gameinfo.albiononline.com/api/gameinfo/guilds/{$linhir_id}/members";

			$response = Http::timeout(30)
				->retry(3, 2000)
				->get($url);

			// 1. VERIFICAR ÉXITO ANTES DE TOCAR LA BASE DE DATOS
			if (! $response->successful()) {
				Log::warning('integrantesdelgremiolinhir: API no exitosa, no se modifica la BD', [
					'status' => $response->status(),
					'body'   => \Illuminate\Support\Str::limit($response->body(), 300),
				]);
				return false;
			}

			$integrantes = $response->json();

			// 2. VALIDAR QUE SEA UN ARRAY VÁLIDO Y NO VACÍO
			if (! is_array($integrantes) || empty($integrantes)) {
				Log::warning('integrantesdelgremiolinhir: respuesta vacía o inválida, no se modifica la BD');
				return false;
			}

			// 3. SANITY CHECK: si la API devuelve menos de la mitad de lo que
			//    ya tenemos en la BD, es sospechoso y abortamos.
			$dbCount = Personaje::where('GuildId', $linhir_id)->count();
			if ($dbCount >= 10 && count($integrantes) < $dbCount * 0.5) {
				Log::warning('integrantesdelgremiolinhir: lista sospechosamente pequeña, abortando', [
					'api_count' => count($integrantes),
					'db_count'  => $dbCount,
				]);
				return false;
			}

			$idsIntegrantesActuales = collect($integrantes)
				->pluck('Id')
				->filter()
				->toArray();

			// 4. SOLO aquí procesamos bajas. Con delay para evitar rate-limit.
			$personajesRegistrados = Personaje::where('GuildId', $linhir_id)->get();

			foreach ($personajesRegistrados as $personaje) {
				if (in_array($personaje->Id_albion, $idsIntegrantesActuales)) {
					continue;
				}

				usleep(250_000); // 250 ms entre llamadas

				try {
					$responsePersonaje = Http::timeout(10)
						->retry(2, 1000)
						->get("https://gameinfo.albiononline.com/api/gameinfo/players/{$personaje->Id_albion}");

					if ($responsePersonaje->successful()) {
						$info = $responsePersonaje->json();
						$nuevoGuildId = $info['GuildId'] ?? null;

						$personaje->update([
							'GuildId' => $nuevoGuildId,
							'miembro' => $nuevoGuildId === $linhir_id,
						]);
					} else {
						// API falla → NO tocar la BD (probable rate-limit temporal)
						Log::warning('No se pudo verificar personaje, se conserva su estado actual', [
							'id_albion' => $personaje->Id_albion,
							'status'    => $responsePersonaje->status(),
						]);
					}
				} catch (\Throwable $e) {
					Log::warning('Error verificando personaje, se conserva su estado', [
						'id_albion' => $personaje->Id_albion,
						'error'     => $e->getMessage(),
					]);
				}
			}

			// 5. Alta / actualización de miembros actuales
			foreach ($integrantes as $integrante) {
				if (empty($integrante['Id'])) {
					continue;
				}

				Personaje::updateOrCreate(
					['Id_albion' => $integrante['Id']],
					[
						'Name'    => $integrante['Name'],
						'GuildId' => $integrante['GuildId'] ?? $linhir_id,
						'miembro' => true,
					]
				);
			}

			Log::info('integrantesdelgremiolinhir: sincronización OK', [
				'api_count' => count($integrantes),
			]);

			return true;
		} catch (\Throwable $e) {
			Log::error('integrantesdelgremiolinhir: excepción, no se modifica la BD', [
				'error' => $e->getMessage(),
			]);
			return false;
		}
	}
	/**
	 * Esta función realiza una consulta a la Pagina del gameinfo.albiononline
	 * para buscar información de los personajes registrados
	 */

	public function datospersonaje()
	{
		$personajesRegistrados = Personaje::where('miembro', true)->get();

		foreach ($personajesRegistrados as $personaje) {
			usleep(200_000); // 200 ms entre llamadas → máx ~5 req/s

			$responsePersonaje = Http::timeout(10)
				->retry(2, 1000)
				->get("https://gameinfo.albiononline.com/api/gameinfo/players/{$personaje->Id_albion}");

			if (! $responsePersonaje->successful()) {
				Log::warning('datospersonaje: no se pudo obtener', [
					'id'     => $personaje->Id_albion,
					'status' => $responsePersonaje->status(),
				]);
				continue;
			}

			$infoPersonaje = $responsePersonaje->json();

			// Validar estructura antes de acceder
			if (! isset($infoPersonaje['LifetimeStatistics']['Timestamp'])) {
				continue;
			}

			// … resto del código tal cual …
		}
	}


	public function lideresEspecialidades()
	{
		$especialidades = [
			'Hide'          => ['nombre' => 'Peletero',   'image' => 'hera/peletero.png'],
			'FishingFame'   => ['nombre' => 'Pescador',   'image' => 'hera/pescador.png'],
			'Rock'          => ['nombre' => 'Cantero',    'image' => 'hera/cantero.png'],
			'Ore'           => ['nombre' => 'Minero',     'image' => 'hera/minero.png'],
			'Fiber'         => ['nombre' => 'Cosechador', 'image' => 'hera/cosechador.png'],
			'Wood'          => ['nombre' => 'Leñador',    'image' => 'hera/lenador.png'],
			'Crafting_Total' => ['nombre' => 'Crafters',  'image' => 'hera/crafters.png'],
			'FarmingFame'   => ['nombre' => 'Agricultor', 'image' => 'hera/agricultor.png'],
		];

		$semanaInicio = now()->startOfWeek()->toDateString();
		$resultados = [];

		foreach ($especialidades as $campo => $config) {
			$lider = EspecialidadSemanal::selectRaw('personajes.Name, (valor_fin - valor_inicio) as ganancia')
				->leftJoin('personajes', 'especialidades_semanales.personaje_id', '=', 'personajes.id')
				->where('personajes.miembro', true)
				->where('especialidades_semanales.semana_inicio', $semanaInicio)
				->where('especialidades_semanales.tipo', $campo)
				->orderBy('ganancia', 'desc')
				->first();

			$resultados[] = [
				'especialidad' => $config['nombre'],
				'name'         => $lider ? $lider->Name : 'Sin datos',
				'fame'         => $lider ? $lider->ganancia : 0,
				'image'        => $config['image'],
			];
		}

		return $resultados;
	}

	/**
	 * Esta función realiza una consulta a la Pagina del gameinfo.albiononline 
	 * para buscar información de una alianza segun su id. 
	 * 
	 * @param string   $identificador cadena de texto que contiene el id de albion 
	 * de la alianza
	 *
	 * @return Retorna un array.
	 */

	public function alianza($identificador)
	{
		try {
			$url = 'https://gameinfo.albiononline.com/api/gameinfo/alliances/';
			$response = Http::get($url . $identificador);

			$respuesta = $response->getBody()->getContents(); // accedemos a el contenido			

			$respuesta = json_decode($respuesta); //convertimos en json	

			return $respuesta;
		} catch (\Illuminate\Http\Client\ConnectionException $e) {
			//report($e);	 
			return false;
		}
	}

	/**
	 * Actualiza los registros de fama semanal para todos los miembros
	 */
	public function actualizarFamaSemanal()
	{
		$fechaInicioSemana = now()->startOfWeek()->format('Y-m-d');
		$miembros = Personaje::where('miembro', true)->get();

		foreach ($miembros as $miembro) {
			$url = "https://gameinfo.albiononline.com/api/gameinfo/players/{$miembro->Id_albion}";
			$response = Http::get($url);

			if ($response->successful()) {
				$data = $response->json();
				$pveTotal = $data['LifetimeStatistics']['PvE']['Total'] ?? 0;
				$pvpTotal = $data['KillFame'] ?? 0;

				// Buscar o crear registro
				$registro = FamaSemanal::firstOrNew([
					'personaje_id' => $miembro->id,
					'semana_inicio' => $fechaInicioSemana
				]);

				// Si es un nuevo registro, guardar como inicio
				if (!$registro->exists) {
					$registro->fama_pve_inicio = $pveTotal;
					$registro->fama_pvp_inicio = $pvpTotal;
				}

				// Actualizar valores finales
				$registro->fama_pve_fin = $pveTotal;
				$registro->fama_pvp_fin = $pvpTotal;
				$registro->save();
			}
		}
	}

	/**
	 * Obtiene los top 3 de fama PvE y PvP para la semana actual
	 * 
	 * @return array
	 */
	public function topFamaSemanal()
	{
		$fechaInicioSemana = now()->startOfWeek()->format('Y-m-d');

		// Top PvE
		$topPvE = FamaSemanal::with('personaje')
			->where('semana_inicio', $fechaInicioSemana)
			->get()
			->map(function ($item) {
				return [
					'nombre' => $item->personaje->Name,
					'fama_inicio' => $item->fama_pve_inicio,
					'fama_fin' => $item->fama_pve_fin,
					'diferencia' => $item->diferencia_pve
				];
			})
			->sortByDesc('diferencia')
			->take(3)
			->values();

		// Top PvP
		$topPvP = FamaSemanal::with('personaje')
			->where('semana_inicio', $fechaInicioSemana)
			->get()
			->map(function ($item) {
				return [
					'nombre' => $item->personaje->Name,
					'fama_inicio' => $item->fama_pvp_inicio,
					'fama_fin' => $item->fama_pvp_fin,
					'diferencia' => $item->diferencia_pvp
				];
			})
			->sortByDesc('diferencia')
			->take(3)
			->values();

		return [
			'pve' => $topPvE,
			'pvp' => $topPvP,
			'semana' => $fechaInicioSemana
		];
	}
}
