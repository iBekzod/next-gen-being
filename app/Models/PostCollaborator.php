<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostCollaborator extends Model
{
    protected $table = 'post_collaborators';

    protected $fillable = [
        'post_id',
        'user_id',
        'role',
        'permissions',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('left_at');
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeOwners($query)
    {
        return $query->where('role', 'owner');
    }

    public function scopeEditors($query)
    {
        return $query->where('role', 'editor');
    }

    public function scopeReviewers($query)
    {
        return $query->where('role', 'reviewer');
    }

    // Methods
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function isReviewer(): bool
    {
        return $this->role === 'reviewer';
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    public function isActive(): bool
    {
        return $this->left_at === null;
    }

    public function hasPermission(string $permission): bool
    {
        // Check role-based permissions
        $rolePermissions = $this->getRolePermissions();

        if (in_array($permission, $rolePermissions)) {
            return true;
        }

        // Check custom permissions
        if ($this->permissions && in_array($permission, $this->permissions)) {
            return true;
        }

        return false;
    }

    public function getRolePermissions(): array
    {
        return match($this->role) {
            'owner' => ['view', 'edit', 'review', 'invite', 'manage_collaborators', 'delete'],
            'editor' => ['view', 'edit', 'review'],
            'reviewer' => ['view', 'review'],
            'viewer' => ['view'],
            default => []
        };
    }

    public function leave(): void
    {
        $this->update(['left_at' => now()]);
    }

    public function updateRole(string $newRole): void
    {
        $this->update(['role' => $newRole]);
    }
}
