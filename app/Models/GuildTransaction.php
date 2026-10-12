<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuildTransaction extends Model
{
    protected $fillable = [
        'type', 'amount', 'reason', 'image_url',
        'performed_by_discord_id', 'performed_by_name',
    ];

    /** Saldo actual de la cuenta del gremio (derivado del ledger). */
    public static function balance(): int
    {
        return (int) self::query()
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN type IN ('inicial','ingreso') THEN amount
                        ELSE -amount
                    END
                ), 0) as total
            ")
            ->value('total');
    }
}
