<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full system access and management',
                'permissions' => [
                    'manage_users',
                    'manage_posts',
                    'manage_comments',
                    'manage_categories',
                    'manage_tags',
                    'manage_subscriptions',
                    'manage_settings',
                    'view_analytics',
                    'manage_ai_suggestions',
                ]
            ],
            [
                'name' => 'Content Manager',
                'slug' => 'content_manager',
                'description' => 'Manage content and moderate comments',
                'permissions' => [
                    'manage_posts',
                    'manage_comments',
                    'manage_categories',
                    'manage_tags',
                    'view_analytics',
                    'manage_ai_suggestions',
                ]
            ],
            [
                'name' => 'Lead',
                'slug' => 'lead',
                'description' => 'Team lead with extended permissions',
                'permissions' => [
                    'create_posts',
                    'edit_own_posts',
                    'publish_posts',
                    'view_analytics',
                    'moderate_comments',
                ]
            ],
            [
                'name' => 'Blogger',
                'slug' => 'blogger',
                'description' => 'Create and manage own blog posts',
                'permissions' => [
                    'create_posts',
                    'edit_own_posts',
                    'publish_posts',
                ]
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }
    }
}
