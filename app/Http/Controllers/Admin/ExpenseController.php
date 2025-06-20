<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function create(Project $project)
    {
        return view('admin.expenses.create', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date',
        ]);

        $project->expenses()->create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return redirect()->route('admin.project.show', $project)->with('success', 'Expense added.');
    }
}
