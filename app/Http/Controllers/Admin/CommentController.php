<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Task;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class CommentController extends Controller
{
    /**
     * Store a comment for a project.
     */
    public function storeForProject(Request $request, Project $project)
    {
        return $this->storeComment($request, $project, 'admin.project.show');
    }

    /**
     * Store a comment for a task.
     */
    public function storeForTask(Request $request, Task $task, Project $project)
    {
        // Validate project_id matches the provided project
        $request->validate([
            'project_id' => ['required', 'exists:projects,id', 'in:' . $project->id],
        ]);

        // Check if the task is associated with the project
        if (!$task->projects()->where('project_id', $project->id)->exists()) {
            return redirect()->back()->withErrors(['project_id' => 'Task not associated with this project']);
        }

        // Store the comment using shared logic
        $comment = $this->storeComment($request, $task, 'admin.task.show', $project->id);

        return $comment;
    }

    /**
     * Shared logic to store a comment for any commentable model.
     */
    protected function storeComment(Request $request, Model $commentable, string $redirectRoute, ?int $projectId = null)
    {
        $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        $comment = $commentable->comments()->create([
            'content' => $request->input('content'),
            'user_id' => Auth::id(),
            'project_id' => $projectId ?? ($commentable instanceof Project ? $commentable->id : null),
            'parent_id' => $request->input('parent_id'),
        ]);

        // Notify relevant users
        $this->notifyUsers($comment, $commentable);

        return redirect()->route($redirectRoute, $commentable instanceof Task ? [$commentable, $projectId] : $commentable)
            ->with('success', 'Comment added.');
    }

    /**
     * Notify users about a new comment.
     */
    protected function notifyUsers(Comment $comment, Model $commentable)
    {
        $commentAuthor = Auth::user();
        $recipients = collect();

        // Add project/task assignees
        if ($commentable instanceof Project || $commentable instanceof Task) {
            $recipients = $commentable->users->pluck('id');
        }

        // Add thread participants (for replies)
        if ($comment->parent_id) {
            $parentComment = Comment::find($comment->parent_id);
            $threadComments = Comment::where('parent_id', $comment->parent_id)
                ->orWhere('id', $comment->parent_id)
                ->with('user')
                ->get();
            $threadParticipants = $threadComments->pluck('user.id')->unique();
            $recipients = $recipients->merge($threadParticipants);
        }

        // Include project manager for projects
        if ($commentable instanceof Project && $commentable->project_manager_id) {
            $recipients->push($commentable->project_manager_id);
        }

        // Exclude comment author
        $recipients = $recipients->unique()->reject(function ($userId) use ($commentAuthor) {
            return $userId == $commentAuthor->id;
        });

        // Attach users to comment with unread status
        $comment->users()->syncWithoutDetaching($recipients->mapWithKeys(function ($userId) {
            return [$userId => ['read_at' => null]];
        }));
    }
}
