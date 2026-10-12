<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Composition;
use App\Models\CompositionWeapon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CompositionController extends Controller
{
    /**
     * GET /api/bot/compositions
     * Lista todas las composiciones activas con sus armas.
     */
    public function index(): JsonResponse
    {
        $comps = Composition::where('active', true)
            ->with('weapons:id,composition_id,weapon_name,weapon_icon,sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return response()->json(['success' => true, 'compositions' => $comps]);
    }

    /**
     * POST /api/bot/compositions
     * Crea una composición con sus armas en un solo paso.
     *
     * Payload:
     * {
     *   "name": "ZvZ 20",
     *   "description": "...",
     *   "weapons": [
     *     {"name": "Hallowfall", "icon": "🛡️"},
     *     {"name": "Great Holy", "icon": "✨"}
     *   ]
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:120|unique:compositions,name',
            'description'           => 'nullable|string|max:500',
            'weapons'               => 'required|array|min:1',
            'weapons.*.name'        => 'required|string|max:120',
            'weapons.*.icon'        => 'nullable|string|max:64',
        ]);

        $comp = DB::transaction(function () use ($data) {
            $comp = Composition::create([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'active'      => true,
            ]);

            foreach ($data['weapons'] as $i => $w) {
                CompositionWeapon::create([
                    'composition_id' => $comp->id,
                    'weapon_name'    => $w['name'],
                    'weapon_icon'    => $w['icon'] ?? null,
                    'sort_order'     => $i,
                ]);
            }

            return $comp->load('weapons');
        });

        return response()->json(['success' => true, 'composition' => $comp]);
    }

    /**
     * DELETE /api/bot/compositions/{id}
     * Soft-delete (active=false). No borra por si hay CTAs históricas.
     */
    public function destroy(int $id): JsonResponse
    {
        $comp = Composition::find($id);
        if (!$comp) {
            return response()->json(['success' => false, 'reason' => 'Composición no encontrada.'], 200);
        }

        $comp->update(['active' => false]);
        return response()->json(['success' => true]);
    }
}
