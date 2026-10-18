<?php

namespace App\Livewire\Modulo\Ctas;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Composition;
use App\Models\Cta;
use App\Models\Personaje;
use App\Models\CtaParticipant;
use App\Services\Discord\DiscordMessageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Crear CTA desde la Web.
 *
 * Combina dos modalidades:
 *   1. Pre-asignación de armas por staff (web) → status='confirmed'
 *   2. Botones de Discord → usuarios se auto-inscriben en armas libres
 *
 * El backend (`CtaService::join()`) bloquea armas ya ocupadas, así
 * que las pre-asignadas desde la web no pueden ser tomadas por otros.
 */

class CreateCta extends Component
{
    use WithFileUploads;

    // ------------------------------------------------------------------ //
    //                        Datos generales                              //
    // ------------------------------------------------------------------ //

    public string $title = '';
    public string $location = '';
    public string $start_time = '';
    public string $end_time = '';
    public ?int $composition_id = null;
    public int $reminder_minutes = 10;

    /** Imagen destacada (opcional). */
    public $image = null;

    // ------------------------------------------------------------------ //
    //                        Pre-asignación                               //
    // ------------------------------------------------------------------ //

    /**
     * Mapa { composition_weapon_id (int) => personaje_id (int|string|null) }
     * Se carga al seleccionar composición y se envía al guardar.
     */
    public array $assignments = [];

    /** Filtro de búsqueda para el selector de personajes. */
    public string $search = '';

    // ------------------------------------------------------------------ //
    //                        Estado del form                             //
    // ------------------------------------------------------------------ //

    public bool $creating = false;
    public ?string $errorMessage = null;

    // ------------------------------------------------------------------ //
    //                        Reglas y mensajes                            //
    // ------------------------------------------------------------------ //

    protected function rules(): array
    {
        return [
            'title'            => 'required|string|max:120',
            'location'         => 'required|string|max:120',
            'start_time'       => 'required|date|after:now',
            'end_time'         => 'required|date|after:start_time',
            'composition_id'   => 'required|exists:compositions,id',
            'reminder_minutes' => 'required|integer|min:1|max:120',
            'image'            => 'nullable|image|max:5120',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required'          => 'El título es obligatorio.',
            'location.required'       => 'La ubicación es obligatoria.',
            'start_time.required'     => 'La hora de inicio es obligatoria.',
            'start_time.after'        => 'La hora de inicio debe ser futura.',
            'end_time.required'       => 'La hora de fin es obligatoria.',
            'end_time.after'          => 'La hora de fin debe ser posterior al inicio.',
            'composition_id.required' => 'Debes seleccionar una composición.',
            'composition_id.exists'   => 'La composición no existe.',
            'reminder_minutes.min'    => 'El recordatorio debe ser al menos 1 minuto.',
            'reminder_minutes.max'    => 'El recordatorio no puede superar 120 minutos.',
            'image.image'             => 'El archivo debe ser una imagen.',
            'image.max'               => 'La imagen no debe superar 5 MB.',
        ];
    }

    // ------------------------------------------------------------------ //
    //                              Render                                 //
    // ------------------------------------------------------------------ //

    public function render()
    {
        $compositions = Composition::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $compositionActiva = null;
        if ($this->composition_id) {
            $compositionActiva = Composition::with('weapons')
                ->find($this->composition_id);
        }

        // Lista completa de personajes disponibles
        $personajes = Personaje::where('miembro', true)
            ->whereNotNull('discord_user_id')
            ->orderBy('Name')
            ->get(['id', 'Name', 'discord_user_id']);

        // Filtro de búsqueda
        $search = trim($this->search);
        $personajesFiltrados = $search === ''
            ? $personajes
            : $personajes->filter(fn($p) => stripos($p->Name, $search) !== false)->values();

        return view('livewire.modulo.ctas.create-cta', [
            'compositions'        => $compositions,
            'compositionActiva'   => $compositionActiva,
            'personajes'          => $personajes,
            'personajesFiltrados' => $personajesFiltrados,
        ]);
    }

    // ------------------------------------------------------------------ //
    //                        Reactividad                                  //
    // ------------------------------------------------------------------ //

    /**
     * Al cambiar la composición, precarga las armas con asignación vacía.
     */
    public function updatedCompositionId(): void
    {
        $this->assignments = [];

        if (!$this->composition_id) {
            return;
        }

        $comp = Composition::with('weapons')->find($this->composition_id);
        if (!$comp) {
            return;
        }

        foreach ($comp->weapons as $w) {
            $this->assignments[$w->id] = null;
        }
    }

    // ------------------------------------------------------------------ //
    //                              Guardar                                //
    // ------------------------------------------------------------------ //

    /**
     * Flujo completo:
     *   1. Validar datos generales
     *   2. Validar asignaciones (al menos 1, sin duplicados, miembros válidos)
     *   3. Crear CTA + participantes en transacción
     *   4. Subir imagen (si hay)
     *   5. Publicar embed en Discord con botones
     *   6. Guardar IDs de Discord en la CTA
     *   7. Redirigir al detalle
     */
    public function save(DiscordMessageService $discord): void
    {
        $this->errorMessage = null;
        $this->validate();

        $start = Carbon::parse($this->start_time)->utc();
        $end   = Carbon::parse($this->end_time)->utc();

        // -----------------------------------------------------------------
        // Validaciones de asignación
        // -----------------------------------------------------------------
        $asignados = array_filter($this->assignments, fn($v) => !empty($v));

        if (empty($asignados)) {
            $this->addError('assignments', 'Debes asignar al menos un personaje a un arma.');
            return;
        }

        $personajeIds = array_values($asignados);
        if (count($personajeIds) !== count(array_unique($personajeIds))) {
            $this->addError('assignments', 'Un mismo personaje no puede ocupar dos armas.');
            return;
        }

        // Cargar personajes válidos (miembros activos con Discord)
        $personajes = Personaje::whereIn('id', $personajeIds)
            ->where('miembro', true)
            ->whereNotNull('discord_user_id')
            ->get()
            ->keyBy('id');

        if ($personajes->count() !== count(array_unique($personajeIds))) {
            $this->addError('assignments', 'Alguno de los personajes seleccionados no está disponible.');
            return;
        }

        $this->creating = true;

        try {
            // -------------------------------------------------------------
            // 1. Crear CTA + participantes (transacción)
            // -------------------------------------------------------------
            $user            = Auth::user();
            $discordProvider = $user->authProviders()->where('provider', 'discord')->first();

            $cta = DB::transaction(function () use (
                $start, $end, $discordProvider, $user, $asignados, $personajes
            ) {
                $cta = Cta::create([
                    'composition_id'        => $this->composition_id,
                    'title'                 => $this->title,
                    'location'              => $this->location,
                    'start_time'            => $start,
                    'end_time'              => $end,
                    'reminder_minutes'      => $this->reminder_minutes,
                    'reminder_at'           => $start->copy()->subMinutes($this->reminder_minutes),
                    'status'                => 'scheduled',
                    'paid'                  => false,
                    'pro_guild'             => false,
                    'created_by_discord_id' => $discordProvider?->provider_id ?? (string) $user->id,
                    'created_by_name'       => $user->name,
                ]);

                // Pre-asignación: crea CtaParticipant confirmados
                foreach ($asignados as $weaponId => $personajeId) {
                    $p = $personajes->get($personajeId);
                    if (!$p) {
                        continue;
                    }

                    CtaParticipant::create([
                        'cta_id'                => $cta->id,
                        'discord_user_id'       => $p->discord_user_id,
                        'user_name'             => $p->Name,
                        'personaje_id'          => $p->id,
                        'composition_weapon_id' => (int) $weaponId,
                        'status'                => 'confirmed',
                        'participation_percent' => 100,
                    ]);
                }

                return $cta;
            });

            // -------------------------------------------------------------
            // 2. Subir imagen (si hay)
            // -------------------------------------------------------------
            $imageUrl = null;
            if ($this->image) {
                $path     = $this->image->store('ctas', 'public');
                $imageUrl = Storage::disk('public')->url($path);
            }

            // -------------------------------------------------------------
            // 3. Publicar en Discord
            // -------------------------------------------------------------
            $cta->load('composition.weapons', 'participants.weapon');

            $channelId = config('app.cta_announce_channel_id');

            if (empty($channelId)) {
                $this->errorMessage = 'Falta configurar el canal de anuncios de CTAs '
                    . '(CTA_ANNOUNCE_CHANNEL_ID). La CTA se guardó en la web pero no se publicó en Discord.';
                Log::warning('CreateCta: CTA creada sin canal de Discord', [
                    'cta_id' => $cta->id,
                ]);
                $this->creating = false;
                $this->redirectRoute('ctas.show', ['id' => $cta->id]);
                return;
            }

            $embed = $this->buildEmbed($cta, $imageUrl);

            $result = $discord->sendMessage($channelId, [
                'embeds'     => [$embed],
                'components' => $this->buildButtons($cta->id),
            ]);

            if (!$result || empty($result['id'])) {
                Log::warning('CreateCta: publicación en Discord fallida', [
                    'cta_id' => $cta->id,
                ]);
                $this->errorMessage = 'La CTA se creó correctamente en la web, pero no se pudo '
                    . 'publicar en Discord. Un administrador puede reintentar la publicación.';
                $this->creating = false;
                $this->redirectRoute('ctas.show', ['id' => $cta->id]);
                return;
            }

            // -------------------------------------------------------------
            // 4. Persistir IDs de Discord
            // -------------------------------------------------------------
            $cta->update([
                'discord_message_id' => $result['id'],
                'discord_channel_id' => $result['channel_id'] ?? $channelId,
            ]);

            Log::info('CTA creada desde web y publicada en Discord', [
                'cta_id'     => $cta->id,
                'message_id' => $result['id'],
                'asignados'  => count($asignados),
                'por'        => $user->name,
            ]);

            $this->creating = false;
            session()->flash('message', 'CTA creada y publicada en Discord correctamente.');
            $this->redirectRoute('ctas.show', ['id' => $cta->id]);

        } catch (\Throwable $e) {
            $this->creating = false;
            Log::error('CreateCta: excepción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->errorMessage = 'Ocurrió un error al crear la CTA: ' . $e->getMessage();
        }
    }

    // ================================================================== //
    //                        CONSTRUCTORES DE EMBED                       //
    // ================================================================== //

    /**
     * Construye el embed que se publica en Discord.
     *
     * Muestra:
     *   - Datos generales de la CTA
     *   - Lista de armas con marca 🔒 (reservado) o ⚪ (disponible)
     *   - Imagen destacada (si hay)
     */
    private function buildEmbed(Cta $cta, ?string $imageUrl): array
    {
        $startTs = Carbon::parse($cta->start_time)->utc()->timestamp;
        $endTs   = Carbon::parse($cta->end_time)->utc()->timestamp;

        // Mapa weapon_id → nombre del personaje asignado
        $asignaciones = $cta->participants
            ->whereNotNull('composition_weapon_id')
            ->mapWithKeys(fn($p) => [
                $p->composition_weapon_id => $p->user_name,
            ])
            ->toArray();

        // Listado de armas con estado
        $lineas     = [];
        $totalArmas = $cta->composition->weapons->count();
        $asignadas  = 0;

        foreach ($cta->composition->weapons as $i => $w) {
            $icono    = $w->weapon_icon ?? '•';
            $ocupante = $asignaciones[$w->id] ?? null;

            if ($ocupante) {
                $asignadas++;
            }

            // 🔒 reservado por staff · ⚪ disponible
            $estado = $ocupante
                ? "🔒 **{$ocupante}**"
                : "⚪ *disponible*";

            $lineas[] = "`" . str_pad((string)($i + 1), 2, ' ', STR_PAD_LEFT)
                      . ".` {$icono} {$w->weapon_name} — {$estado}";
        }

        $armasText = implode("\n", array_slice($lineas, 0, 20));
        if (count($lineas) > 20) {
            $armasText .= "\n… y " . (count($lineas) - 20) . " más (ver en la web)";
        }

        $descripcion = "**📍 Ubicación:** {$cta->location}\n"
                     . "**🎭 Composición:** {$cta->composition->name}\n\n"
                     . "**Ocupación:** `{$asignadas}/{$totalArmas}` armas reservadas por staff\n\n"
                     . "🔒 Reservado · ⚪ Disponible para unirse con el botón\n\n"
                     . "> Pulsa **Unirse** para escoger un arma libre. "
                     . "Las armas reservadas (🔒) no se pueden tomar.";

        $embed = [
            'title'       => '⚔️ ' . $cta->title,
            'description' => $descripcion,
            'color'       => 0xC54B47,
            'timestamp'   => Carbon::now()->toIso8601String(),
            'fields'      => [
                [
                    'name'   => '⏰ Inicio',
                    'value'  => "<t:{$startTs}:F> (<t:{$startTs}:R>)",
                    'inline' => true,
                ],
                [
                    'name'   => '🏁 Fin',
                    'value'  => "<t:{$endTs}:t>",
                    'inline' => true,
                ],
                [
                    'name'   => '🔔 Recordatorio',
                    'value'  => "{$cta->reminder_minutes} min antes",
                    'inline' => true,
                ],
                [
                    'name'   => "⚔️ Armas y asignación ({$asignadas}/{$totalArmas})",
                    'value'  => $armasText ?: '*Sin armas definidas*',
                    'inline' => false,
                ],
            ],
            'footer' => [
                'text'     => 'Linhir · CTA gestionada por staff',
                'icon_url' => asset('imagenes/escudo_512x512.png'),
            ],
        ];

        if ($imageUrl) {
            $embed['image'] = ['url' => $imageUrl];
        }

        return $embed;
    }

    /**
     * Construye los botones de acción (Unirse / Ausente).
     *
     * IMPORTANTE — Integración con el bot:
     * El bot debe tener una vista persistente con custom_ids que sigan
     * el patrón "linhir:cta:join:{id}" y "linhir:cta:absent:{id}".
     * El backend bloquea armas ocupadas automáticamente.
     */
    private function buildButtons(int $ctaId): array
    {
        return [[
            'type' => 1, // Action Row
            'components' => [
                [
                    'type'      => 2, // Button
                    'style'     => 3, // Success
                    'label'     => 'Unirse',
                    'emoji'     => ['name' => '⚔️'],
                    'custom_id' => "linhir:cta:join:{$ctaId}",
                ],
                [
                    'type'      => 2,
                    'style'     => 2, // Secondary
                    'label'     => 'Ausente',
                    'emoji'     => ['name' => '⏰'],
                    'custom_id' => "linhir:cta:absent:{$ctaId}",
                ],
            ],
        ]];
    }
}
