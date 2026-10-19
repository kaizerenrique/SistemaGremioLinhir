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
use App\Services\Guild\CtaEmbedBuilder;
use Illuminate\Validation\ValidationException;

/**
 * Crear CTA desde la Web.
 *
 * Flujo:
 *   1. Staff completa el formulario (datos generales + pre-asignación de armas).
 *   2. Al guardar:
 *        a. Se crea la CTA y los `CtaParticipant` (status='confirmed')
 *           en una transacción.
 *        b. Se sube la imagen (si hay) a storage/app/public/ctas/
 *        c. Se publica el embed + botones en Discord vía bot token.
 *        d. Se persisten los IDs de Discord (message_id, channel_id).
 *        e. Se guarda `image_url` en la CTA.
 *
 * Notas:
 *   - Si Discord falla, la CTA queda creada en la web y se puede
 *     re-publicar desde `/ctas/{id}` (botón "Sincronizar Discord").
 *   - Las armas pre-asignadas quedan bloqueadas para usuarios de Discord.
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
    //                        Estado del form                              //
    // ------------------------------------------------------------------ //

    public bool $creating = false;
    public ?string $errorMessage = null;

    // ================================================================== //
    //                        Reglas y mensajes                            //
    // ================================================================== //

    /**
     * Reglas de validación.
     *
     * Sobre la imagen:
     *   - `mimes:` explícito en vez de `image` (evita dependencia de gd
     *     y da mensajes claros sobre formatos).
     *   - `max:5120` (5 MB) es la regla Laravel. El límite efectivo depende
     *     de `upload_max_filesize` / `post_max_size` de PHP — ver
     *     `php artisan lin:check-uploads`.
     */
    protected function rules(): array
    {
        return [
            'title'            => 'required|string|max:120',
            'location'         => 'required|string|max:120',
            'start_time'       => 'required|date|after:now',
            'end_time'         => 'required|date|after:start_time',
            'composition_id'   => 'required|exists:compositions,id',
            'reminder_minutes' => 'required|integer|min:1|max:120',
            'image'            => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120',
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

            // Imagen
            'image.file'      => 'El archivo no se subió correctamente. '
                               . 'Suele ser por el límite del servidor. '
                               . 'Ejecuta `php artisan lin:check-uploads`.',
            'image.mimes'     => 'Formato no soportado. Usa JPG, PNG, WEBP o GIF. '
                               . '(Los iPhone HEIC no están soportados — conviértelos primero).',
            'image.max'       => 'La imagen no debe superar 5 MB.',
            'image.uploaded'  => 'La imagen no se pudo subir. '
                               . 'Puede que supere el límite del servidor (normalmente 2 MB por defecto). '
                               . 'Pide a un administrador que ejecute `php artisan lin:check-uploads`.',
        ];
    }

    // ================================================================== //
    //                              Render                                 //
    // ================================================================== //

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

        // Personajes disponibles (miembros activos con Discord vinculado)
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

    // ================================================================== //
    //                        Reactividad                                  //
    // ================================================================== //

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

    // ================================================================== //
    //                              Guardar                                //
    // ================================================================== //

    /**
     * Flujo completo:
     *   1. Validar datos generales
     *   2. Validar asignaciones (al menos 1, sin duplicados, miembros válidos)
     *   3. Crear CTA + participantes en transacción
     *   4. Subir imagen (si hay)
     *   5. Publicar embed en Discord con botones (vía CtaEmbedBuilder)
     *   6. Guardar IDs de Discord + image_url en la CTA
     *   7. Redirigir al detalle
     */
    public function save(DiscordMessageService $discord): void
    {
        $this->errorMessage = null;

        // -----------------------------------------------------------------
        // 1. Validación general
        // -----------------------------------------------------------------
        try {
            $this->validate();
        } catch (ValidationException $e) {
            // Diagnóstico adicional cuando falla la imagen
            if ($e->validator->errors()->has('image')) {
                Log::warning('CreateCta: validación de imagen falló', [
                    'errors'         => $e->validator->errors()->get('image'),
                    'upload_max'     => ini_get('upload_max_filesize'),
                    'post_max'       => ini_get('post_max_size'),
                    'has_file_error' => !empty($_FILES['image']['error'] ?? null),
                    'file_error'     => $_FILES['image']['error'] ?? null,
                    'file_size'      => $_FILES['image']['size'] ?? null,
                ]);
            }
            throw $e;
        }

        $start = Carbon::parse($this->start_time)->utc();
        $end   = Carbon::parse($this->end_time)->utc();

        // -----------------------------------------------------------------
        // 2. Validaciones de asignación
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
            // 3. Crear CTA + participantes (transacción)
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
            // 4. Subir imagen (si hay)
            // -------------------------------------------------------------
            $imageUrl = null;
            if ($this->image) {
                $path     = $this->image->store('ctas', 'public');
                $imageUrl = Storage::disk('public')->url($path);
                $cta->update(['image_url' => $imageUrl]);

                Log::info('CreateCta: imagen subida', [
                    'cta_id' => $cta->id,
                    'path'   => $path,
                    'url'    => $imageUrl,
                    'size'   => $this->image->getSize(),
                ]);
            }

            // -------------------------------------------------------------
            // 5. Publicar en Discord
            // -------------------------------------------------------------
            $cta->load('composition.weapons', 'participants.weapon');

            $channelId = config('app.cta_announce_channel_id');

            if (empty($channelId)) {
                Log::warning('CreateCta: CTA creada sin canal de Discord', [
                    'cta_id' => $cta->id,
                ]);
                $this->creating = false;
                session()->flash(
                    'warning',
                    'CTA creada pero sin canal de Discord configurado. '
                    . 'Añade CTA_ANNOUNCE_CHANNEL_ID en .env y re-publica desde el detalle.'
                );
                $this->redirectRoute('ctas.show', ['id' => $cta->id]);
                return;
            }

            // Usar el builder compartido
            $embed   = CtaEmbedBuilder::buildEmbed($cta, $imageUrl);
            $buttons = CtaEmbedBuilder::buildButtons($cta);

            $payload = ['embeds' => [$embed]];
            if (!empty($buttons)) {
                $payload['components'] = $buttons;
            }

            $result = $discord->sendMessage($channelId, $payload);

            if (!$result || empty($result['id'])) {
                Log::warning('CreateCta: publicación en Discord fallida', [
                    'cta_id' => $cta->id,
                ]);
                $this->creating = false;
                session()->flash(
                    'warning',
                    'La CTA se guardó correctamente en la web, pero no se pudo '
                    . 'publicar en Discord. Puedes reintentar desde el detalle.'
                );
                $this->redirectRoute('ctas.show', ['id' => $cta->id]);
                return;
            }

            // -------------------------------------------------------------
            // 6. Persistir IDs de Discord
            // -------------------------------------------------------------
            $cta->update([
                'discord_message_id' => $result['id'],
                'discord_channel_id' => $result['channel_id'] ?? $channelId,
            ]);

            Log::info('CTA creada desde web y publicada en Discord', [
                'cta_id'     => $cta->id,
                'message_id' => $result['id'],
                'asignados'  => count($asignados),
                'has_image'  => !empty($imageUrl),
                'por'        => $user->name,
            ]);

            $this->creating = false;
            session()->flash('message', 'CTA creada y publicada en Discord correctamente.');
            $this->redirectRoute('ctas.show', ['id' => $cta->id]);

        } catch (ValidationException $e) {
            $this->creating = false;
            $this->addError('general', collect($e->errors())->flatten()->first());
        } catch (\Throwable $e) {
            $this->creating = false;
            Log::error('CreateCta: excepción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->errorMessage = 'Ocurrió un error al crear la CTA: ' . $e->getMessage();
        }
    }
}
