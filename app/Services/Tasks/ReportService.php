<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\TaskReport;
use App\Models\UserPoint;
use App\Models\PointTransaction;
use App\Services\Discord\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Crear un reporte de tarea completada.
     * @throws \Exception
     */
    public function createReport(int $taskId, string $discordUserId, ?string $evidence = null): TaskReport
    {
        $task = Task::findOrFail($taskId);
        if (!$task->active) {
            throw new \Exception('La tarea no está activa.');
        }

        // Si no es repetible, verificar si ya fue aprobada anteriormente
        if (!$task->repeatable) {
            $exists = TaskReport::where('task_id', $taskId)
                ->where('discord_user_id', $discordUserId)
                ->where('status', 'approved')
                ->exists();
            if ($exists) {
                throw new \Exception('Ya completaste esta tarea anteriormente.');
            }
        }

        return TaskReport::create([
            'task_id' => $taskId,
            'discord_user_id' => $discordUserId,
            'evidence' => $evidence,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Listar reportes pendientes.
     */
    public function listPending(): Collection
    {
        return TaskReport::with('task')
            ->where('status', 'pending')
            ->orderBy('submitted_at', 'asc')
            ->get();
    }

    /**
     * Listar todos los reportes (con filtros opcionales).
     */
    public function listAll(?string $status = null): Collection
    {
        $query = TaskReport::with('task');
        if ($status) {
            $query->where('status', $status);
        }
        return $query->orderBy('submitted_at', 'desc')->get();
    }

    /**
     * Aprobar un reporte.
     * @throws \Exception
     */
    public function approve(int $reportId, string $reviewerDiscordId, ?string $notes = null): TaskReport
    {
        return DB::transaction(function () use ($reportId, $reviewerDiscordId, $notes) {
            $report = TaskReport::with('task')->findOrFail($reportId);
            if ($report->status !== 'pending') {
                throw new \Exception('El reporte ya fue procesado.');
            }

            // Actualizar reporte
            $report->update([
                'status' => 'approved',
                'reviewed_by_discord_id' => $reviewerDiscordId,
                'reviewed_at' => now(),
                'notes' => $notes,
            ]);

            // Sumar puntos
            $points = $report->task->points;
            $userPoint = UserPoint::firstOrNew(['discord_user_id' => $report->discord_user_id]);
            $userPoint->total_points += $points;
            $userPoint->save();

            // Registrar transacción
            PointTransaction::create([
                'discord_user_id' => $report->discord_user_id,
                'amount' => $points,
                'reason' => "Tarea completada: {$report->task->name}",
                'performed_by_discord_id' => $reviewerDiscordId,
            ]);

            // Enviar notificación DM
            $this->notificationService->sendDirectMessage(
                $report->discord_user_id,
                "✅ **¡Tarea Aprobada!**\nHas recibido **{$points} puntos** por completar la tarea **{$report->task->name}**."
            );

            return $report;
        });
    }

    /**
     * Rechazar un reporte.
     * @throws \Exception
     */
    public function reject(int $reportId, string $reviewerDiscordId, string $reason): TaskReport
    {
        $report = TaskReport::findOrFail($reportId);
        if ($report->status !== 'pending') {
            throw new \Exception('El reporte ya fue procesado.');
        }

        $report->update([
            'status' => 'rejected',
            'reviewed_by_discord_id' => $reviewerDiscordId,
            'reviewed_at' => now(),
            'notes' => $reason,
        ]);

        // Enviar notificación DM
        $this->notificationService->sendDirectMessage(
            $report->discord_user_id,
            "❌ **Tarea Rechazada**\nMotivo: {$reason}"
        );

        return $report;
    }

    /**
     * Obtener un reporte por ID.
     */
    public function find(int $reportId): TaskReport
    {
        return TaskReport::with('task')->findOrFail($reportId);
    }
}