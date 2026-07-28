<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    /**
     * Rapports de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $reports = $request->user()
            ->dailyReports()
            ->orderByDesc('report_date')
            ->get();

        return response()->json(['reports' => $reports]);
    }

    /**
     * Crée un rapport journalier pour l'utilisateur connecté.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'report_date' => ['nullable', 'date'],
        ]);

        $report = $request->user()->dailyReports()->create([
            'report_date' => $data['report_date'] ?? now()->toDateString(),
            'title' => $data['title'],
            'content' => $data['content'],
        ]);

        return response()->json(['report' => $report], 201);
    }

    /**
     * Détail d'un rapport (propriétaire ou DG).
     */
    public function show(Request $request, DailyReport $dailyReport)
    {
        if (! $request->user()->isDg() && $dailyReport->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json(['report' => $dailyReport->load('user:id,name')]);
    }

    /**
     * Vue DG : tous les rapports, avec filtre optionnel par utilisateur.
     */
    public function all(Request $request)
    {
        $query = DailyReport::with('user:id,name,position,department')
            ->orderByDesc('report_date');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        return response()->json(['reports' => $query->get()]);
    }
}
