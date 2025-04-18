<?php

namespace App\Http\Controllers;

use App\Models\Value;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
class ValueController extends Controller
{
    public function index(Request $request)
    {
        $query = Value::with(['subcategories' => function($query) {
            $query->orderBy('order', 'desc')
                ->where('deleted', false);
        }]);

        // Búsqueda
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('subcategories', function ($subQuery) use ($searchTerm) {
                      $subQuery->where('name', 'like', "%{$searchTerm}%")
                              ->where('deleted', false);
                  });
            });
        }

        // Ordenamiento
        $query->orderBy('created_at', 'desc')
            ->where('deleted', false);

        // Paginación
        $perPage = $request->input('per_page', 10);
        $values = $query->paginate($perPage);

        return response()->json($values);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:values',
            'minimum_score' => 'required|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $value = Value::create([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'minimum_score' => $request->minimum_score,
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json([
            'message' => 'Valor creado exitosamente',
            'value' => $value
        ], 201);
    }

    public function update(Request $request, Value $value)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:values,slug,' . $value->id,
            'minimum_score' => 'required|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $value->update([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'minimum_score' => $request->minimum_score,
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'message' => 'Valor actualizado exitosamente',
            'value' => $value
        ]);
    }

    public function destroy(Value $value)
    {
        $value->update([
            'deleted' => true,
            'deleted_at' => now()
        ]);
        return response()->json([
            'message' => 'Valor eliminado exitosamente'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:values,id'
            ]);

            $count = Value::whereIn('id', $request->ids)->update([
                'deleted' => true,
                'deleted_at' => now()
            ]);

            return response()->json([
                'message' => "{$count} valores eliminados exitosamente"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar los valores: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActiveValues()
    {
        try {
            $values = Value::where('is_active', true)
                ->where('deleted', false)
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json($values);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los valores: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActiveValuesSidebar()
    {
        try {
            $user = Auth::user();
            $company = $user->company;
            
            $values = Value::where('is_active', true)
                ->where(function ($query) use ($company) {
                    $query->whereNull('created_at')
                        ->orWhere('created_at', '<=', $company->fecha_inicio_auto_evaluacion);
                })
                ->where('deleted', false)
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json($values);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los valores: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSubcategoriesByValue(Value $value)
    {
        try {
            // Obtener las subcategorías ordenadas por el campo order de forma descendente
            $subcategories = $value->subcategories()
                ->orderBy('order', 'desc')
                ->where('deleted', false)
                ->get();

            // Asegurarse de que todas las subcategorías tengan un valor de orden
            foreach ($subcategories as $subcategory) {
                if ($subcategory->order === null) {
                    $subcategory->order = 0;
                    $subcategory->save();
                }
            }

            return response()->json($subcategories);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener las subcategorías: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSubcategoriesOrder(Request $request)
    {
        try {
            $request->validate([
                'subcategories' => 'required|array',
                'subcategories.*.id' => 'required|exists:subcategories,id',
                'subcategories.*.order' => 'required|integer|min:0'
            ]);

            foreach ($request->subcategories as $subcategory) {
                \App\Models\Subcategory::where('id', $subcategory['id'])
                    ->update(['order' => $subcategory['order']]);
            }

            return response()->json([
                'message' => 'Orden de subcategorías actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar el orden de las subcategorías: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fixSubcategoriesOrder()
    {
        try {
            // Obtener todos los valores
            $values = Value::where('deleted', false)->get();

            foreach ($values as $value) {
                // Obtener las subcategorías de este valor ordenadas por ID (o fecha de creación)
                $subcategories = $value->subcategories()->orderBy('id')->get();
                
                // Asignar un orden descendente (los más recientes tendrán mayor prioridad)
                $totalSubcategories = $subcategories->count();
                
                foreach ($subcategories as $index => $subcategory) {
                    $subcategory->order = $totalSubcategories - $index;
                    $subcategory->save();
                }
            }

            return response()->json([
                'message' => 'Orden de todas las subcategorías actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar el orden de las subcategorías: ' . $e->getMessage()
            ], 500);
        }
    }
} 