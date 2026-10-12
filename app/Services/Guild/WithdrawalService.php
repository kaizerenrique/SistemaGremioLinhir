<?php

namespace App\Services\Guild;

use App\Models\BancoGremial;
use App\Models\Personaje;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    public function request(Personaje $personaje, int $amount): WithdrawalRequest
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Monto inválido.']);
        }

        if ($personaje->saldoActual() < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Saldo insuficiente.',
            ]);
        }

        // Evitar múltiples pendientes
        $exists = WithdrawalRequest::where('personaje_id', $personaje->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'amount' => 'Ya tienes una solicitud pendiente.',
            ]);
        }

        return WithdrawalRequest::create([
            'personaje_id'    => $personaje->id,
            'discord_user_id' => $personaje->discord_user_id,
            'user_name'       => $personaje->Name,
            'amount'          => $amount,
            'status'          => 'pending',
        ]);
    }

    public function approve(WithdrawalRequest $req, string $reviewerDiscordId, string $reviewerName): WithdrawalRequest
    {
        if ($req->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Solicitud ya procesada.']);
        }

        return DB::transaction(function () use ($req, $reviewerDiscordId, $reviewerName) {
            $personaje = Personaje::lockForUpdate()->findOrFail($req->personaje_id);

            if ($personaje->saldoActual() < $req->amount) {
                throw ValidationException::withMessages([
                    'amount' => 'El miembro ya no tiene saldo suficiente.',
                ]);
            }

            // Egreso en el ledger personal
            $mov = BancoGremial::create([
                'personaje_id'    => $personaje->id,
                'tipo'            => 'egreso',
                'monto'           => $req->amount,
                'concepto'        => "Retiro aprobado #{$req->id}",
                'referencia'      => "solicitud-{$req->id}",
                'autorizado_por'  => $reviewerName,
            ]);

            $req->update([
                'status'                 => 'approved',
                'reviewed_by_discord_id' => $reviewerDiscordId,
                'reviewed_by_name'       => $reviewerName,
                'reviewed_at'            => now(),
                'banco_gremial_id'       => $mov->id,
            ]);

            return $req;
        });
    }

    public function reject(WithdrawalRequest $req, string $reviewerDiscordId, string $reviewerName, string $reason): WithdrawalRequest
    {
        if ($req->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Solicitud ya procesada.']);
        }

        $req->update([
            'status'                 => 'rejected',
            'reviewed_by_discord_id' => $reviewerDiscordId,
            'reviewed_by_name'       => $reviewerName,
            'reviewed_at'            => now(),
            'notes'                  => $reason,
        ]);

        return $req;
    }
}
