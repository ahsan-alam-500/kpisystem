<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    ShiftCompliance, ShiftComplianceItem, ComplianceTask, Pub, Week
};

class ShiftComplianceController extends Controller
{
    // GET /api/shift?pub_id=&week_id=
    public function index(Request $r)
    {
        $q = ShiftCompliance::query()
            ->with(['pub:id,name,pub_number','week:id,period_id,week_no,start_date,end_date','user:id,name']);

        if ($r->filled('pub_id'))  $q->where('pub_id', $r->integer('pub_id'));
        if ($r->filled('week_id')) $q->where('week_id', $r->integer('week_id'));

        // employees can only see their pub
        if ($r->user()->hasRole('Employee') && $r->user()->pub_id) {
            $q->where('pub_id', $r->user()->pub_id);
        }

        $list = $q->latest('id')->paginate($r->integer('per_page', 15));
        return response()->json($list);
    }

    // GET /api/shift/{id}
    public function show($id)
    {
        $shift = ShiftCompliance::with([
            'pub:id,name,pub_number','week','user:id,name',
            'items:id,shift_compliance_id,compliance_task_id,done,note',
            'items.task:id,name'
        ])->findOrFail($id);

        return response()->json($shift);
    }

    // POST /api/shift  (create/update tasks in one go)
    // Body:
    // {
    //   "pub_id": 1, "week_id": 3,
    //   "tasks": [{"task_id": 5, "done": true, "note": "ok"}, ...]
    // }
    public function upsert(Request $r)
    {
        $data = $r->validate([
            'pub_id'  => 'required|exists:pubs,id',
            'week_id' => 'required|exists:weeks,id',
            'tasks'   => 'array',
            'tasks.*.task_id' => 'required|exists:compliance_tasks,id',
            'tasks.*.done'    => 'boolean',
            'tasks.*.note'    => 'nullable|string',
        ]);

        // Employees can only update their own pub
        if ($r->user()->hasRole('Employee') && $r->user()->pub_id != $data['pub_id']) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $shift = ShiftCompliance::firstOrCreate(
            ['pub_id' => $data['pub_id'], 'week_id' => $data['week_id']],
            ['user_id' => $r->user()->id]
        );

        if (!empty($data['tasks'])) {
            foreach ($data['tasks'] as $row) {
                ShiftComplianceItem::updateOrCreate(
                    ['shift_compliance_id' => $shift->id, 'compliance_task_id' => $row['task_id']],
                    ['done' => (bool)($row['done'] ?? false), 'note' => $row['note'] ?? null]
                );
            }
        }

        // compute score
        $total = ComplianceTask::where('is_active', true)->count();
        $done  = $shift->items()->where('done', true)->count();
        $shift->update(['score' => $total ? (int) round($done * 100 / $total) : 0]);

        return response()->json(
            $shift->load(['items.task:id,name','pub:id,name,pub_number','week','user:id,name']),
            $shift->wasRecentlyCreated ? 201 : 200
        );
    }
}
