<?php

namespace App\Services\Guild;

use App\Models\GuildTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuildBankService
{
    public function balance(): int
    {
        return GuildTransaction::balance();
    }

    public function register(array $data): GuildTransaction
    {
        $type   = $data['type'];   // inicial | ingreso | egreso
        $amount = (int) $data['amount'];

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'El monto debe ser positivo.']);
        }

        if ($type === 'egreso' && $this->balance() < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Saldo del gremio insuficiente.',
            ]);
        }

        return DB::transaction(function () use ($data, $type, $amount) {
            return GuildTransaction::create([
                'type'                    => $type,
                'amount'                  => $amount,
                'reason'                  => $data['reason'],
                'image_url'               => $data['image_url'] ?? null,
                'performed_by_discord_id' => $data['performed_by_discord_id'],
                'performed_by_name'       => $data['performed_by_name'],
            ]);
        });
    }
}
