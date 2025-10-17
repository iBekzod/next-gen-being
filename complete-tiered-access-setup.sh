#!/bin/bash

echo "🚀 Setting Up Tiered Content Access System..."
echo "=============================================="

GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

# Step 1: Create migration files
echo -e "${BLUE}📝 Creating migration files...${NC}"

# Migration 1: Add premium tier to posts
cat > database/migrations/2025_10_17_131433_add_premium_tier_to_posts_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('premium_tier', 20)->nullable()->after('is_premium');
            $table->unsignedInteger('preview_percentage')->default(30)->after('premium_tier');
            $table->text('paywall_message')->nullable()->after('preview_percentage');

            $table->index('premium_tier');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['premium_tier', 'preview_percentage', 'paywall_message']);
        });
    }
};
EOF

# Migration 2: Create content_views table
cat > database/migrations/2025_10_17_131435_create_content_views_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_premium_content')->default(false);
            $table->boolean('viewed_as_trial')->default(false);
            $table->boolean('converted_to_paid')->default(false);
            $table->unsignedInteger('time_on_page')->nullable();
            $table->unsignedInteger('scroll_depth')->nullable();
            $table->boolean('clicked_upgrade')->default(false);
            $table->string('referrer')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            $table->index('user_id');
            $table->index('post_id');
            $table->index('session_id');
            $table->index('viewed_at');
            $table->index('is_premium_content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};
EOF

# Migration 3: Create paywall_interactions table
cat > database/migrations/2025_10_17_131438_create_paywall_interactions_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paywall_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->string('interaction_type', 50);
            $table->string('paywall_type', 50);
            $table->boolean('converted')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('interacted_at')->useCurrent();
            $table->timestamps();

            $table->index('user_id');
            $table->index('post_id');
            $table->index('interaction_type');
            $table->index('converted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paywall_interactions');
    }
};
EOF

# Migration 4: Add metering fields to users
cat > database/migrations/2025_10_17_131441_add_metering_fields_to_users_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('free_articles_used')->default(0)->after('email_verified_at');
            $table->timestamp('free_articles_reset_at')->nullable()->after('free_articles_used');
            $table->timestamp('last_upgrade_prompt_at')->nullable()->after('free_articles_reset_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['free_articles_used', 'free_articles_reset_at', 'last_upgrade_prompt_at']);
        });
    }
};
EOF

echo -e "${GREEN}✅ Migration files created${NC}"

# Step 2: Create Models
echo -e "${BLUE}📦 Creating model files...${NC}"

cat > app/Models/ContentView.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentView extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'session_id',
        'ip_address',
        'user_agent',
        'is_premium_content',
        'viewed_as_trial',
        'converted_to_paid',
        'time_on_page',
        'scroll_depth',
        'clicked_upgrade',
        'referrer',
        'viewed_at',
    ];

    protected $casts = [
        'is_premium_content' => 'boolean',
        'viewed_as_trial' => 'boolean',
        'converted_to_paid' => 'boolean',
        'clicked_upgrade' => 'boolean',
        'viewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium_content', true);
    }

    public function scopeConverted($query)
    {
        return $query->where('converted_to_paid', true);
    }
}
EOF

cat > app/Models/PaywallInteraction.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaywallInteraction extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'session_id',
        'interaction_type',
        'paywall_type',
        'converted',
        'metadata',
        'interacted_at',
    ];

    protected $casts = [
        'converted' => 'boolean',
        'metadata' => 'array',
        'interacted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeConverted($query)
    {
        return $query->where('converted', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('interaction_type', $type);
    }
}
EOF

echo -e "${GREEN}✅ Model files created${NC}"

# Step 3: Run migrations
echo -e "${BLUE}🗄️  Running database migrations...${NC}"
php artisan migrate --force

# Step 4: Clear caches
echo -e "${BLUE}🧹 Clearing caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "================================"
echo -e "${GREEN}✅ Tiered Content Access Setup Complete!${NC}"
echo "================================"
echo ""
echo "📋 WHAT WAS CREATED:"
echo ""
echo "✅ Database Tables:"
echo "   - posts: Added premium_tier, preview_percentage, paywall_message"
echo "   - content_views: Track all content access"
echo "   - paywall_interactions: Track paywall engagement"
echo "   - users: Added free_articles_used, free_articles_reset_at"
echo ""
echo "✅ Models:"
echo "   - ContentView"
echo "   - PaywallInteraction"
echo ""
echo "📋 NEXT STEPS:"
echo ""
echo "1. Create ContentMeteringService"
echo "2. Create ContentAccessService"
echo "3. Build ProgressivePaywall Livewire component"
echo "4. Add paywall to PostShow component"
echo "5. Update Post model with tier checking"
echo "6. Test free article limits"
echo ""
echo "📚 Documentation:"
echo "   - TIERED_CONTENT_ACCESS_PLAN.md"
echo ""
echo "🎉 Ready to 2-3x your subscription conversions!"
EOF

chmod +x complete-tiered-access-setup.sh

echo -e "${GREEN}✅ Setup script created${NC}"
