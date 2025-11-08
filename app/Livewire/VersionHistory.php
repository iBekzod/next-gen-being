<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Post;
use App\Models\PostVersion;
use App\Services\CollaborationService;
use Illuminate\Support\Facades\Auth;

class VersionHistory extends Component
{
    use WithPagination;

    public Post $post;
    public array $versions = [];
    public ?int $selectedVersionId = null;
    public array $versionDetails = [];
    public bool $showCompareModal = false;
    public ?int $compareWithVersionId = null;

    protected $paginationTheme = 'tailwind';

    public function mount(Post $post)
    {
        $this->post = $post;

        if (!$this->canViewVersions()) {
            abort(403, 'You do not have permission to view version history');
        }

        $this->loadVersions();
    }

    public function canViewVersions(): bool
    {
        return Auth::user()->id === $this->post->author_id || $this->post->hasCollaborator(Auth::user());
    }

    public function canRestoreVersion(): bool
    {
        return Auth::user()->id === $this->post->author_id || $this->post->canBeEditedBy(Auth::user());
    }

    public function loadVersions()
    {
        $collaborationService = app(CollaborationService::class);
        $versions = $collaborationService->getVersionHistory($this->post, 20);

        $this->versions = $versions->getCollection()->map(function ($version) {
            return [
                'id' => $version->id,
                'editor_name' => $version->editor->name,
                'editor_avatar' => $version->editor->getFirstMediaUrl('avatars'),
                'change_type' => $version->change_type,
                'change_summary' => $version->getChangeSummary(),
                'created_at' => $version->created_at->format('M d, Y H:i'),
                'title' => $version->title,
                'content_preview' => substr(strip_tags($version->content), 0, 150) . '...',
            ];
        })->toArray();
    }

    public function selectVersion(int $versionId)
    {
        try {
            $version = PostVersion::findOrFail($versionId);

            $this->versionDetails = [
                'id' => $version->id,
                'editor_name' => $version->editor->name,
                'editor_avatar' => $version->editor->getFirstMediaUrl('avatars'),
                'title' => $version->title,
                'content' => $version->content,
                'change_type' => $version->change_type,
                'change_summary' => $version->getChangeSummary(),
                'created_at' => $version->created_at->format('M d, Y H:i'),
                'full_content' => $version->content,
            ];

            $this->selectedVersionId = $versionId;

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error loading version', type: 'error');
        }
    }

    public function restoreVersion(int $versionId)
    {
        if (!$this->canRestoreVersion()) {
            $this->dispatch('notify', message: 'You do not have permission to restore versions', type: 'error');
            return;
        }

        try {
            $version = PostVersion::findOrFail($versionId);
            $collaborationService = app(CollaborationService::class);

            $collaborationService->restoreVersion($version, Auth::user());

            $this->dispatch('notify', message: 'Version restored successfully');
            $this->dispatch('version-restored');
            $this->reset(['versionDetails', 'selectedVersionId']);
            $this->loadVersions();

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error restoring version: ' . $e->getMessage(), type: 'error');
        }
    }

    public function openCompareModal(int $versionId)
    {
        $this->compareWithVersionId = $versionId;
        $this->showCompareModal = true;
    }

    public function compareVersions(int $versionId1, int $versionId2)
    {
        try {
            $version1 = PostVersion::findOrFail($versionId1);
            $version2 = PostVersion::findOrFail($versionId2);

            $comparison = $version1->compareTo($version2);

            $this->dispatch('show-comparison', [
                'version1' => $version1->created_at->format('M d, Y H:i'),
                'version2' => $version2->created_at->format('M d, Y H:i'),
                'comparison' => $comparison,
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error comparing versions', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.version-history');
    }
}
