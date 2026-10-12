<?php

namespace App\Services\Guild;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\BancoGremial;
use App\Models\Cta;
use App\Models\Personaje;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityService
{
    public function __construct(private GuildBankService $bank) {}

    /**
     * Crea y liquida una actividad.
     *
     * $data debe contener:
     *   name, start_time, end_time, liquid_silver, items_value (opcional),
     *   pro_guild (bool), cta_id (opcional),
     *   registered_by_discord_id, registered_by_name,
     *   participants: [{discord_user_id, name, percent}, ...]  // requerido si !pro_guild
     *
     * @throws ValidationException
     */
    public function create(array $data): Activity
    {
        $proGuild = (bool) ($data['pro_guild'] ?? false);

        if (!$proGuild && empty($data['participants'])) {
            throw ValidationException::withMessages([
                'participants' => 'Debes especificar participantes o marcar pro-gremio.',
            ]);
        }

        // CTA: no permitir doble pago
        if (!empty($data['cta_id'])) {
            $cta = Cta::find($data['cta_id']);
            if (!$cta) {
                throw ValidationException::withMessages(['cta_id' => 'CTA no encontrada.']);
            }
            if ($cta->paid) {
                throw ValidationException::withMessages(['cta_id' => 'Esta CTA ya fue pagada.']);
            }
        }

        return DB::transaction(function () use ($data, $proGuild) {
            $liquid = (int) $data['liquid_silver'];
            $items  = (int) ($data['items_value'] ?? 0);

            $start = Carbon::parse($data['start_time'])->utc();
            $end   = Carbon::parse($data['end_time'])->utc();

            if ($end <= $start) {
                throw ValidationException::withMessages([
                    'end_time' => 'La hora de fin debe ser posterior al inicio.',
                ]);
            }

            // ============================================================
            // MODO PRO-GREMIO
            // ============================================================
            if ($proGuild) {
                $totalBruto = $liquid + $items;

                $activity = Activity::create([
                    'cta_id'                    => $data['cta_id'] ?? null,
                    'name'                      => $data['name'],
                    'start_time'                => $start,
                    'end_time'                  => $end,
                    'liquid_silver'             => $liquid,
                    'items_value'               => $items,
                    'guild_tax_rate'            => 0,
                    'management_tax_rate'       => 0,
                    'guild_tax_amount'          => 0,
                    'management_tax_amount'     => 0,
                    'total_distributed'         => 0,
                    'pro_guild'                 => true,
                    'registered_by_discord_id'  => $data['registered_by_discord_id'],
                    'registered_by_name'        => $data['registered_by_name'],
                ]);

                if ($totalBruto > 0) {
                    $this->bank->register([
                        'type'                    => 'ingreso',
                        'amount'                  => $totalBruto,
                        'reason'                  => "Actividad pro-gremio: {$data['name']}",
                        'performed_by_discord_id' => $data['registered_by_discord_id'],
                        'performed_by_name'       => $data['registered_by_name'],
                    ]);
                }

                if (!empty($data['cta_id'])) {
                    Cta::whereKey($data['cta_id'])->update([
                        'paid'      => true,
                        'pro_guild' => true,
                    ]);
                }

                return $activity;
            }

            // ============================================================
            // MODO NORMAL (distribución con impuestos)
            // ============================================================
            $totalPercent = collect($data['participants'])->sum('percent');
            if ($totalPercent <= 0) {
                throw ValidationException::withMessages([
                    'participants' => 'La suma de porcentajes debe ser mayor a cero.',
                ]);
            }

            $guildTax      = (float) config('app.guild_tax_rate', 10) / 100;
            $managementTax = (float) config('app.management_tax_rate', 5) / 100;

            $silverAfterGuild = $liquid * (1 - $guildTax);
            $itemsAfterGuild  = $items  * (1 - $guildTax);
            $itemsAfterMgmt   = $itemsAfterGuild * (1 - $managementTax);

            $totalToDistribute = (int) ($silverAfterGuild + $itemsAfterMgmt);

            $guildTaxAmount      = (int) ($liquid * $guildTax) + (int) ($items * $guildTax);
            $managementTaxAmount = (int) ($itemsAfterGuild * $managementTax);

            $activity = Activity::create([
                'cta_id'                    => $data['cta_id'] ?? null,
                'name'                      => $data['name'],
                'start_time'                => $start,
                'end_time'                  => $end,
                'liquid_silver'             => $liquid,
                'items_value'               => $items,
                'guild_tax_rate'            => $guildTax * 100,
                'management_tax_rate'       => $managementTax * 100,
                'guild_tax_amount'          => $guildTaxAmount,
                'management_tax_amount'     => $managementTaxAmount,
                'total_distributed'         => $totalToDistribute,
                'pro_guild'                 => false,
                'registered_by_discord_id'  => $data['registered_by_discord_id'],
                'registered_by_name'        => $data['registered_by_name'],
            ]);

            foreach ($data['participants'] as $p) {
                $share = (int) round($totalToDistribute * ($p['percent'] / $totalPercent));

                $personaje = Personaje::where('discord_user_id', $p['discord_user_id'])->first();

                ActivityParticipant::create([
                    'activity_id'           => $activity->id,
                    'discord_user_id'       => $p['discord_user_id'],
                    'user_name'             => $p['name'],
                    'personaje_id'          => $personaje?->id,
                    'participation_percent' => $p['percent'],
                    'share'                 => $share,
                ]);

                if ($personaje) {
                    BancoGremial::create([
                        'personaje_id'   => $personaje->id,
                        'tipo'           => 'ingreso',
                        'monto'          => $share,
                        'concepto'       => "Actividad: {$data['name']}",
                        'referencia'     => "actividad-{$activity->id}",
                        'autorizado_por' => $data['registered_by_name'],
                    ]);
                }
            }

            $impuestosTotales = $guildTaxAmount + $managementTaxAmount;
            if ($impuestosTotales > 0) {
                $this->bank->register([
                    'type'                    => 'ingreso',
                    'amount'                  => $impuestosTotales,
                    'reason'                  => "Impuestos actividad: {$data['name']}",
                    'performed_by_discord_id' => $data['registered_by_discord_id'],
                    'performed_by_name'       => $data['registered_by_name'],
                ]);
            }

            if (!empty($data['cta_id'])) {
                Cta::whereKey($data['cta_id'])->update(['paid' => true, 'pro_guild' => false]);
            }

            return $activity->load('participants');
        });
    }
}
