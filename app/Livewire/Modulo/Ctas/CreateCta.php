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

    // NUEVOS CAMPOS: Visibilidad, Loot y Anuncio
    public string $visibility = 'linhir'; // 'linhir' o 'alliance'
    public string $loot_type = 'taxed_split'; // 'taxed_split', 'full_split', 'pro_guild'
    public string $announce_before = 'now'; // 'now', '15m', '1h', '2h', '1d'

    /** Imagen destacada (opcional). */
    public $image = null;

    // ------------------------------------------------------------------ //
    //                        Pre-asignación                               //
    // ------------------------------------------------------------------ //

    public array $assignments = [];
    public string $search = '';

    // ------------------------------------------------------------------ //
    //                        Estado del form                              //
    // ------------------------------------------------------------------ //

    public bool $creating = false;
    public ?string $errorMessage = null;

    protected function rules(): array
    {
        return [
            'title'            => 'required|string|max:120',
            'location'         => 'required|string|max:120',
            'start_time'       => 'required|date|after:now',
            'end_time'         => 'required|date|after:start_time',
            'composition_id'   => 'required|exists:compositions,id',
            'reminder_minutes' => 'required|integer|min:1|max:120',
            'visibility'       => 'required|in:linhir,alliance',
            'loot_type'        => 'required|in:taxed_split,full_split,pro_guild',
            'announce_before'  => 'required|in:now,15m,1h,2h,1d',
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
            'visibility.required'     => 'Selecciona la visibilidad.',
            'loot_type.required'      => 'Selecciona el tipo de botín.',
            'image.file'              => 'El archivo no se subió correctamente.',
            'image.mimes'             => 'Formato no soportado. Usa JPG, PNG, WEBP o GIF.',
            'image.max'               => 'La imagen no debe superar 5 MB.',
        ];
    }

    public function render()
    {
        $compositions = Composition::where('active', true)->orderBy('name')->get(['id', 'name']);
        $compositionActiva = $this->composition_id ? Composition::with('weapons')->find($this->composition_id) : null;

        $personajes = Personaje::where('miembro', true)->whereNotNull('discord_user_id')->orderBy('Name')->get(['id', 'Name', 'discord_user_id']);
        $search = trim($this->search);
        $personajesFiltrados = $search === '' ? $personajes : $personajes->filter(fn($p) => stripos($p->Name, $search) !== false)->values();

        return view('livewire.modulo.ctas.create-cta', [
            'compositions'        => $compositions,
            'compositionActiva'   => $compositionActiva,
            'personajes'          => $personajes,
            'personajesFiltrados' => $personajesFiltrados,
        ]);
    }

    public function updatedCompositionId(): void
    {
        $this->assignments = [];
        if (!$this->composition_id) return;

        $comp = Composition::with('weapons')->find($this->composition_id);
        if (!$comp) return;

        foreach ($comp->weapons as $w) {
            $this->assignments[$w->id] = null;
        }
    }

    // Validación reactiva: Si es alianza, forzar full_split
    public function updatedVisibility($value): void
    {
        if ($value === 'alliance') {
            $this->loot_type = 'full_split';
        }
    }

    public function save(DiscordMessageService $discord): void
    {
        $this->errorMessage = null;

        try {
            $this->validate();
        } catch (ValidationException $e) {
            if ($e->validator->errors()->has('image')) {
                Log::warning('CreateCta: validación de imagen falló', [
                    'errors' => $e->validator->errors()->get('image'),
                ]);
            }
            throw $e;
        }

        $start = Carbon::parse($this->start_time)->utc();
        $end   = Carbon::parse($this->end_time)->utc();

        // Calcular fecha de anuncio
        $announceAt = match($this->announce_before) {
            '15m' => $start->copy()->subMinutes(15),
            '1h'  => $start->copy()->subHour(),
            '2h'  => $start->copy()->subHours(2),
            '1d'  => $start->copy()->subDay(),
            default => now(), // 'now'
        };

        // Si la fecha de anuncio ya pasó, anunciar inmediatamente
        if ($announceAt->isPast()) {
            $announceAt = now();
        }

        // Validaciones de asignación
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

        $personajes = Personaje::whereIn('id', $personajeIds)->where('miembro', true)->whereNotNull('discord_user_id')->get()->keyBy('id');
        if ($personajes->count() !== count(array_unique($personajeIds))) {
            $this->addError('assignments', 'Alguno de los personajes seleccionados no está disponible.');
            return;
        }

        $this->creating = true;

        try {
            $user            = Auth::user();
            $discordProvider = $user->authProviders()->where('provider', 'discord')->first();
            
            // Definir el rol según visibilidad
            $roleId = $this->visibility === 'alliance' 
                ? config('app.hrl_role_id') 
                : config('app.linhir_role_id'); // Asegúrate de tener esto en config/app.php

            $cta = DB::transaction(function () use ($start, $end, $announceAt, $discordProvider, $user, $asignados, $personajes, $roleId) {
                $cta = Cta::create([
                    'composition_id'        => $this->composition_id,
                    'title'                 => $this->title,
                    'location'              => $this->location,
                    'visibility'            => $this->visibility,
                    'loot_type'             => $this->loot_type,
                    'start_time'            => $start,
                    'end_time'              => $end,
                    'reminder_minutes'      => $this->reminder_minutes,
                    'reminder_at'           => $start->copy()->subMinutes($this->reminder_minutes),
                    'announce_at'           => $announceAt,
                    'announced'             => false,
                    'status'                => 'scheduled',
                    'paid'                  => false,
                    'pro_guild'             => ($this->loot_type === 'pro_guild'),
                    'discord_role_id'       => $roleId,
                    'created_by_discord_id' => $discordProvider?->provider_id ?? (string) $user->id,
                    'created_by_name'       => $user->name,
                ]);

                foreach ($asignados as $weaponId => $personajeId) {
                    $p = $personajes->get($personajeId);
                    if (!$p) continue;

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

            // Subir imagen
            $imageUrl = null;
            if ($this->image) {
                $path     = $this->image->store('ctas', 'public');
                $imageUrl = Storage::disk('public')->url($path);
                $cta->update(['image_url' => $imageUrl]);
            }

            // Lógica de publicación en Discord:
            // Si el anuncio es "ahora", lo publicamos de inmediato y marcamos como anunciado.
            // Si es diferido, el bot se encargará de publicarlo más tarde.
            
            if ($this->announce_before === 'now') {
                $cta->load('composition.weapons', 'participants.weapon');
                $channelId = $this->visibility === 'alliance' 
                    ? config('app.cta_content_channel_id') 
                    : config('app.cta_announce_channel_id');

                if ($channelId) {
                    $embed   = CtaEmbedBuilder::buildEmbed($cta, $imageUrl);
                    $buttons = CtaEmbedBuilder::buildButtons($cta);
                    $payload = ['embeds' => [$embed]];
                    if (!empty($buttons)) $payload['components'] = $buttons;

                    $result = $discord->sendMessage($channelId, $payload);
                    if ($result && !empty($result['id'])) {
                        $cta->update([
                            'discord_message_id' => $result['id'],
                            'discord_channel_id' => $result['channel_id'] ?? $channelId,
                            'announced'          => true,
                        ]);
                    }
                }
            }

            $this->creating = false;
            
            $msg = $this->announce_before === 'now' 
                ? 'CTA creada y publicada en Discord correctamente.' 
                : 'CTA programada. El bot la anunciará en Discord el ' . $announceAt->format('d/m/Y H:i') . ' UTC.';
            
            session()->flash('message', $msg);
            $this->redirectRoute('ctas.show', ['id' => $cta->id]);

        } catch (\Throwable $e) {
            $this->creating = false;
            Log::error('CreateCta: excepción', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Ocurrió un error al crear la CTA: ' . $e->getMessage();
        }
    }
}