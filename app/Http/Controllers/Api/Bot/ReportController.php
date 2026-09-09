<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tasks\ReportService;
use App\Traits\DiscordRoleChecker;


class ReportController extends Controller
{
    use DiscordRoleChecker;

    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Listar reportes pendientes.
     */
    public function pending()
    {
        $reports = $this->reportService->listPending();

        // Agregar nombre del personaje (si existe)
        $reports->each(function ($report) {
            $report->personaje_name = $report->personaje->Name ?? $report->discord_user_id;
        });

        return response()->json($reports);
    }

    /**
     * Crear un reporte de tarea completada.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'discord_user_id' => 'required|string',
            'evidence' => 'nullable|string',
        ]);

        try {
            $report = $this->reportService->createReport(
                $validated['task_id'],
                $validated['discord_user_id'],
                $validated['evidence'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Reporte creado exitosamente.',
                'report_id' => $report->id,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Aprobar un reporte.
     */
    public function approve(Request $request, int $id)
    {
        $validated = $request->validate([
            'reviewer_discord_id' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Verificar permisos del revisor
        if (!$this->isOfficer($validated['reviewer_discord_id'])) {
            return response()->json(['message' => 'Forbidden: No tienes permisos de oficial.'], 403);
        }

        try {
            $report = $this->reportService->approve(
                $id,
                $validated['reviewer_discord_id'],
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Reporte aprobado correctamente.',
                'report' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Rechazar un reporte.
     */
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'reviewer_discord_id' => 'required|string',
            'reason' => 'required|string',
        ]);

        // Verificar permisos del revisor
        if (!$this->isOfficer($validated['reviewer_discord_id'])) {
            return response()->json(['message' => 'Forbidden: No tienes permisos de oficial.'], 403);
        }

        try {
            $report = $this->reportService->reject(
                $id,
                $validated['reviewer_discord_id'],
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Reporte rechazado correctamente.',
                'report' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener un reporte específico.
     */
    public function show(int $id)
    {
        $report = $this->reportService->find($id);
        $report->personaje_name = $report->personaje->Name ?? $report->discord_user_id;
        return response()->json($report);
    }
}
