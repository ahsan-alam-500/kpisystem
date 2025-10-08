<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{ShiftCompliance, ComplianceTask};

class CompareController extends Controller
{
    // GET /api/compare?pub_id=&week_a=&week_b=
    public function compare(Request $r)
    {
        $data = $r->validate([
            'pub_id' => 'required|integer|exists:pubs,id',
            'week_a' => 'required|integer|exists:weeks,id',
            'week_b' => 'required|integer|exists:weeks,id',
        ]);

        // employee pub scope
        if ($r->user()->hasRole('Employee') && $r->user()->pub_id != $data['pub_id']) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $a = ShiftCompliance::with(['items','items.task:id,name','week'])->where($data)->where('week_id', $data['week_a'])->first();
        $b = ShiftCompliance::with(['items','items.task:id,name','week'])->where($data)->where('week_id', $data['week_b'])->first();

        $tasks = ComplianceTask::active()->get(['id','name']);

        $matrix = $tasks->map(function ($t) use ($a, $b) {
            $ai = optional(optional($a)->items)->firstWhere('compliance_task_id', $t->id);
            $bi = optional(optional($b)->items)->firstWhere('compliance_task_id', $t->id);
            return [
                'task_id' => $t->id,
                'task'    => $t->name,
                'week_a'  => (bool)optional($ai)->done,
                'week_b'  => (bool)optional($bi)->done,
                'delta'   => (int)( (int)(bool)optional($bi)->done - (int)(bool)optional($ai)->done ),
            ];
        });

        $scoreA = $a->score ?? 0;
        $scoreB = $b->score ?? 0;

        return response()->json([
            'pub_id' => $data['pub_id'],
            'week_a' => ['id' => (int)$data['week_a'], 'score' => $scoreA],
            'week_b' => ['id' => (int)$data['week_b'], 'score' => $scoreB],
            'score_delta' => $scoreB - $scoreA,
            'tasks' => $matrix,
        ]);
    }
}
