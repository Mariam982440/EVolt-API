<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ConnectorType;


class ConnectorTypeController extends Controller
{
    public function index()
    {
        $types = ConnectorType::all();
        return response()->json($types, 200);
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:connector_types,name',
            'description' => 'nullable|string'
        ]);

        $connectorType = ConnectorType::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']), // Génère "type-2" à partir de "Type 2"
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Type de connecteur créé avec succès',
            'data' => $connectorType
        ], 201);
    }

    
    public function update(Request $request, $id)
    {
        $connectorType = ConnectorType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|unique:connector_types,name,' . $id,
            'description' => 'nullable|string'
        ]);

        $connectorType->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? $connectorType->description,
        ]);

        return response()->json([
            'message' => 'Type mis à jour',
            'data' => $connectorType
        ], 200);
    }

    /**
     * Supprimer un type (Réservé à l'Admin)
     */
    public function destroy($id)
    {
        $connectorType = ConnectorType::findOrFail($id);
        $connectorType->delete();

        return response()->json(['message' => 'Type supprimé avec succès'], 200);
    }
}
