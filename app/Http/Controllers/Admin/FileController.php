<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\File;
use App\Models\Project;
use App\Models\Contract;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class FileController extends Controller
{
    public function index()
    {
        // Fetch files for projects, contracts, and tasks the user has access to
        // $files = File::whereIn('fileable_id', function ($query) {
        //     // Projects the user manages or is a team member of
        //     $query->select('id')->from('projects')
        //         ->where('project_manager', Auth::id())
        //         ->orWhereIn('id', function ($subQuery) {
        //             $subQuery->select('project_id')->from('project_user')
        //                 ->where('user_id', Auth::id());
        //         });
        // })
        //     ->orWhereIn('fileable_id', function ($query) {
        //         // Contracts linked to accessible projects
        //         $query->select('id')->from('contracts')
        //             ->whereIn('project_id', function ($subQuery) {
        //                 $subQuery->select('id')->from('projects')
        //                     ->where('project_manager', Auth::id())
        //                     ->orWhereIn('id', function ($subSubQuery) {
        //                         $subSubQuery->select('project_id')->from('project_user')
        //                             ->where('user_id', Auth::id());
        //                     });
        //             });
        //     })
        //     ->orWhereIn('fileable_id', function ($query) {
        //         // Tasks assigned to the user
        //         $query->select('id')->from('tasks')
        //             ->whereIn('id', function ($subQuery) {
        //                 $subQuery->select('task_id')->from('task_user')
        //                     ->where('user_id', Auth::id());
        //             });
        //     })
        //     ->with(['fileable', 'user'])
        //     ->latest()
        //     ->get();

        $files = File::with(['fileable', 'user'])->latest()->get();

        return view('admin.files.index', compact('files'));
    }

    public function store(Request $request, $model, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip', // 10MB max
        ]);

        $modelInstance = match ($model) {
            'project' => Project::findOrFail($id),
            'contract' => Contract::findOrFail($id),
            'task' => Task::findOrFail($id),
            default => abort(404),
        };

        // Authorize upload
        //$this->authorize('uploadFile', $modelInstance);

        $file = $request->file('file');
        $path = $file->store('files', 'public');
        $fileRecord = $modelInstance->files()->create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'file_type' => $file->extension(),
            'file_size' => $file->getSize(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'File uploaded successfully.');
    }

    public function download(File $file)
    {
        //$this->authorize('view', $file);

        if (!Storage::disk('public')->exists($file->path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($file->path, $file->filename);
    }

    public function destroy(File $file)
    {
        //$this->authorize('delete', $file);

        Storage::disk('public')->delete($file->path);
        $file->delete();

        return redirect()->back()->with('success', 'File deleted successfully.');
    }
}
