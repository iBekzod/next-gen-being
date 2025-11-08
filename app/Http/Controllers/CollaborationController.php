<?php

namespace App\Http\Controllers;

use App\Models\CollaborationInvitation;
use App\Models\Post;
use App\Services\CollaborationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CollaborationController extends Controller
{
    protected CollaborationService $collaborationService;

    public function __construct(CollaborationService $collaborationService)
    {
        $this->collaborationService = $collaborationService;
        $this->middleware('auth');
        $this->middleware('verified');
    }

    /**
     * Show collaboration panel for a post
     */
    public function show(Post $post): View
    {
        $this->authorize('view-collaborations', $post);

        $stats = $this->collaborationService->getCollaborationStats($post);

        return view('collaboration.show', [
            'post' => $post,
            'stats' => $stats,
            'canManage' => $this->collaborationService->canManageCollaborators($post, Auth::user()),
        ]);
    }

    /**
     * Accept a collaboration invitation
     */
    public function acceptInvitation(Request $request): RedirectResponse
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('dashboard')
                ->with('error', 'Invalid invitation link');
        }

        $invitation = CollaborationInvitation::where('token', $token)->first();

        if (!$invitation) {
            return redirect()->route('dashboard')
                ->with('error', 'Invitation not found');
        }

        if (!$invitation->isValid()) {
            return redirect()->route('dashboard')
                ->with('error', 'This invitation has expired or been cancelled');
        }

        try {
            $user = Auth::user();

            // If invitation doesn't have a user_id yet, set it
            if (!$invitation->user_id) {
                $invitation->update(['user_id' => $user->id]);
            }

            $this->collaborationService->acceptInvitation($invitation, $user);

            return redirect()->route('posts.edit', $invitation->post)
                ->with('success', "You have been added as a {$invitation->role} on '{$invitation->post->title}'");

        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Error accepting invitation: ' . $e->getMessage());
        }
    }

    /**
     * Decline a collaboration invitation
     */
    public function declineInvitation(CollaborationInvitation $invitation): RedirectResponse
    {
        if (Auth::user()->id !== $invitation->user_id && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $invitation->decline();

        return redirect()->route('dashboard')
            ->with('success', 'Invitation declined');
    }

    /**
     * Get collaboration notifications for user
     */
    public function notifications(): View
    {
        $user = Auth::user();

        $pendingInvitations = $user->getPendingInvitations();
        $activeCollaborations = $user->getActiveCollaborations();

        return view('collaboration.notifications', [
            'pendingInvitations' => $pendingInvitations,
            'activeCollaborations' => $activeCollaborations,
        ]);
    }

    /**
     * Get collaboration history for a post
     */
    public function history(Post $post): View
    {
        $this->authorize('view-collaborations', $post);

        $history = $this->collaborationService->getCollaborationHistory($post);

        return view('collaboration.history', [
            'post' => $post,
            'history' => $history,
        ]);
    }

    /**
     * Export collaboration report
     */
    public function exportReport(Post $post): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('view-collaborations', $post);

        $filename = "collaboration-report-{$post->id}-" . now()->format('Y-m-d-His') . '.csv';

        return response()->download(
            $this->generateCollaborationReport($post),
            $filename,
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }

    /**
     * Generate collaboration report data
     */
    private function generateCollaborationReport(Post $post): string
    {
        $stats = $this->collaborationService->getCollaborationStats($post);
        $history = $this->collaborationService->getCollaborationHistory($post);

        $csv = "Collaboration Report for: {$post->title}\n";
        $csv .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";

        $csv .= "SUMMARY\n";
        $csv .= "Total Collaborators,{$stats['total_collaborators']}\n";
        $csv .= "Editors,{$stats['total_editors']}\n";
        $csv .= "Reviewers,{$stats['total_reviewers']}\n";
        $csv .= "Viewers,{$stats['total_viewers']}\n";
        $csv .= "Total Comments,{$stats['total_comments']}\n";
        $csv .= "Open Comments,{$stats['open_comments']}\n";
        $csv .= "Total Versions,{$stats['total_versions']}\n";

        $csv .= "\n\nACTIVITY LOG\n";
        $csv .= "Date,User,Action,Description\n";

        foreach ($history as $activity) {
            $csv .= "\"{$activity->created_at}\",\"{$activity->user->name}\",\"{$activity->action}\",\"{$activity->description}\"\n";
        }

        return $csv;
    }
}
