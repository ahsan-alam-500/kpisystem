<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pub;
use App\Models\User;
use Illuminate\Http\Request;

class PubController extends Controller
{
    public function index(Request $r)
    {
        $q = Pub::query()->with(['manager:id,name,email']);

        if ($r->filled('search')) {
            $s = $r->string('search');
            $q->where(fn($x) => $x->where('name', 'like', "%$s%")
                ->orWhere('pub_number', 'like', "%$s%"));
        }

        if ($r->filled('manager_id')) $q->where('manager_id', $r->integer('manager_id'));

        // role-based scoping: Employees only see their pub
        if ($r->user()->hasRole('Employee')) {
            $q->where('id', $r->user()->pub_id);
        }

        $pubs = $q->orderBy('pub_number')->paginate($r->integer('per_page', 15));
        return response()->json($pubs);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'pub_number' => 'required|string|max:20|unique:pubs,pub_number',
            'name'       => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $pub = Pub::create($data);
        return response()->json($pub->load('manager'), 201);
    }

    public function show(Pub $pub)
    {
        $pub->load(['manager:id,name,email']);
        return response()->json($pub);
    }

    public function update(Request $r, Pub $pub)
    {
        $data = $r->validate([
            'pub_number' => "sometimes|string|max:20|unique:pubs,pub_number,{$pub->id}",
            'name'       => 'sometimes|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $pub->update($data);
        return response()->json($pub->load('manager'));
    }

    public function destroy(Pub $pub)
    {
        $pub->delete();
        return response()->json(['deleted' => true]);
    }
}
