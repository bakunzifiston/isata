<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('messages', 'organization_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->foreignId('organization_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                $table->foreignId('social_account_id')->nullable()->after('channel_id')->constrained()->nullOnDelete();
                $table->string('social_platform')->nullable()->after('social_account_id');
                $table->json('media_paths')->nullable()->after('attachment_file');
                $table->string('external_post_id')->nullable()->after('status');
                $table->text('error_message')->nullable()->after('external_post_id');
                $table->timestamp('published_at')->nullable()->after('error_message');
            });
        }

        if (! Schema::hasTable('social_posts')) {
            $this->migrateSocialEngagementToMessages();

            return;
        }

        $channelId = DB::table('channels')->where('slug', 'social_media')->value('id');
        if (! $channelId) {
            return;
        }

        $postIdToMessageId = [];

        foreach (DB::table('social_posts')->orderBy('id')->get() as $post) {
            if (! $post->event_id) {
                continue;
            }

            $status = $post->status === 'published' ? 'sent' : $post->status;

            $messageId = DB::table('messages')->insertGetId([
                'organization_id' => $post->organization_id,
                'event_id' => $post->event_id,
                'channel_id' => $channelId,
                'social_account_id' => $post->social_account_id,
                'social_platform' => $post->platform,
                'subject' => null,
                'content' => $post->content,
                'content_type' => 'text',
                'media_paths' => $post->media_paths,
                'scheduled_at' => $post->scheduled_at,
                'status' => $status,
                'external_post_id' => $post->external_post_id,
                'error_message' => $post->error_message,
                'published_at' => $post->published_at,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ]);

            $postIdToMessageId[$post->id] = $messageId;
        }

        if (Schema::hasTable('social_engagement') && ! Schema::hasColumn('social_engagement', 'message_id')) {
            Schema::table('social_engagement', function (Blueprint $table) {
                $table->foreignId('message_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });

            foreach ($postIdToMessageId as $postId => $messageId) {
                DB::table('social_engagement')
                    ->where('social_post_id', $postId)
                    ->update(['message_id' => $messageId]);
            }

            Schema::table('social_engagement', function (Blueprint $table) {
                $table->dropForeign(['social_post_id']);
            });

            Schema::table('social_engagement', function (Blueprint $table) {
                $table->dropUnique(['social_post_id', 'engagement_type']);
                $table->dropColumn('social_post_id');
                $table->unique(['message_id', 'engagement_type']);
            });
        }

        Schema::dropIfExists('social_posts');
    }

    public function down(): void
    {
        if (! Schema::hasTable('social_posts')) {
            Schema::create('social_posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('social_account_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('platform');
                $table->text('content');
                $table->json('media_paths')->nullable();
                $table->string('status')->default('draft');
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->string('external_post_id')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
                $table->index(['organization_id', 'status']);
                $table->index(['scheduled_at', 'status']);
            });
        }

        $channelId = DB::table('channels')->where('slug', 'social_media')->value('id');

        if ($channelId) {
            $messageIdToPostId = [];

            foreach (DB::table('messages')->where('channel_id', $channelId)->orderBy('id')->get() as $message) {
                $status = $message->status === 'sent' ? 'published' : $message->status;

                $postId = DB::table('social_posts')->insertGetId([
                    'organization_id' => $message->organization_id,
                    'event_id' => $message->event_id,
                    'social_account_id' => $message->social_account_id,
                    'platform' => $message->social_platform ?? 'facebook',
                    'content' => $message->content,
                    'media_paths' => $message->media_paths,
                    'status' => $status,
                    'scheduled_at' => $message->scheduled_at,
                    'published_at' => $message->published_at,
                    'external_post_id' => $message->external_post_id,
                    'error_message' => $message->error_message,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                ]);

                $messageIdToPostId[$message->id] = $postId;
            }

            if (Schema::hasTable('social_engagement') && Schema::hasColumn('social_engagement', 'message_id')) {
                Schema::table('social_engagement', function (Blueprint $table) {
                    $table->dropUnique(['message_id', 'engagement_type']);
                    $table->foreignId('social_post_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                });

                foreach ($messageIdToPostId as $messageId => $postId) {
                    DB::table('social_engagement')
                        ->where('message_id', $messageId)
                        ->update(['social_post_id' => $postId]);
                }

                Schema::table('social_engagement', function (Blueprint $table) {
                    $table->dropForeign(['message_id']);
                    $table->dropColumn('message_id');
                    $table->unique(['social_post_id', 'engagement_type']);
                });
            }

            DB::table('messages')->where('channel_id', $channelId)->delete();
        }

        if (Schema::hasColumn('messages', 'organization_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropForeign(['social_account_id']);
                $table->dropColumn([
                    'organization_id',
                    'social_account_id',
                    'social_platform',
                    'media_paths',
                    'external_post_id',
                    'error_message',
                    'published_at',
                ]);
            });
        }
    }

    private function migrateSocialEngagementToMessages(): void
    {
        if (! Schema::hasTable('social_engagement') || ! Schema::hasColumn('social_engagement', 'message_id')) {
            return;
        }

        if (Schema::hasColumn('social_engagement', 'social_post_id')) {
            Schema::table('social_engagement', function (Blueprint $table) {
                $table->dropForeign(['social_post_id']);
            });

            Schema::table('social_engagement', function (Blueprint $table) {
                $table->dropUnique(['social_post_id', 'engagement_type']);
                $table->dropColumn('social_post_id');
                $table->unique(['message_id', 'engagement_type']);
            });
        }
    }
};
