<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    /**
     * Historique des pointages de l'utilisateur connecté (le plus récent d'abord).
     */
    public function index(Request $request)
    {
        $checkIns = $request->user()
            ->checkIns()
            ->orderByDesc('check_in_date')
            ->get();

        return response()->json(['checkIns' => $checkIns]);
    }

    /**
     * Pointage du jour pour l'utilisateur connecté (ou null si pas encore fait).
     */
    public function today(Request $request)
    {
        $checkIn = $request->user()
            ->checkIns()
            ->whereDate('check_in_date', now()->toDateString())
            ->first();

        return response()->json(['checkIn' => $checkIn]);
    }

    /**
     * Enregistre le pointage du jour (une seule fois par jour et par utilisateur).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $existing = $request->user()
            ->checkIns()
            ->whereDate('check_in_date', now()->toDateString())
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Vous avez déjà pointé aujourd\'hui.',
                'checkIn' => $existing,
            ], 409);
        }

        $checkIn = $request->user()->checkIns()->create([
            'check_in_date' => now()->toDateString(),
            'check_in_time' => now(),
            'note' => $data['note'] ?? null,
        ]);

        return response()->json(['checkIn' => $checkIn], 201);
    }

    /**
     * Enregistre l'heure de sortie du pointage du jour (une seule fois par jour).
     */
    public function checkout(Request $request)
    {
        $checkIn = $request->user()
            ->checkIns()
            ->whereDate('check_in_date', now()->toDateString())
            ->first();

        if (! $checkIn) {
            return response()->json([
                'message' => "Vous n'avez pas encore pointé votre arrivée aujourd'hui.",
            ], 409);
        }

        if ($checkIn->check_out_time) {
            return response()->json([
                'message' => 'Vous avez déjà enregistré votre sortie aujourd\'hui.',
                'checkIn' => $checkIn,
            ], 409);
        }

        $checkIn->update(['check_out_time' => now()]);

        return response()->json(['checkIn' => $checkIn]);
    }

    /**
     * Vue DG : tous les pointages, avec filtre optionnel par utilisateur ou date.
     */
    public function all(Request $request)
    {
        $query = CheckIn::with('user:id,name,position,department')
            ->orderByDesc('check_in_date');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('check_in_date', $request->date('date'));
        }

        return response()->json(['checkIns' => $query->get()]);
    }
}
