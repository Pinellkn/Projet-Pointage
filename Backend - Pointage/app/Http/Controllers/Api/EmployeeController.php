<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Liste des employés (réservé au DG).
     */
    public function index()
    {
        $employees = User::where('role', 'employee')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'position', 'department', 'created_at']);

        return response()->json(['employees' => $employees]);
    }

    /**
     * Crée un nouvel employé (réservé au DG).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'fullName' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
            'position' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
        ]);

        $employee = User::create([
            'name' => $data['fullName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'employee',
            'position' => $data['position'] ?? null,
            'department' => $data['department'] ?? null,
        ]);

        return response()->json(['employee' => $employee], 201);
    }

    /**
     * Supprime un employé (réservé au DG).
     */
    public function destroy(User $employee)
    {
        if ($employee->isDg()) {
            return response()->json([
                'message' => "Impossible de supprimer un compte DG.",
            ], 403);
        }

        $employee->delete();

        return response()->json(['ok' => true]);
    }
}
