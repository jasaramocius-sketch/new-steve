<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consolidate Support Tickets into the Questions / Inquiries inbox.
     *
     * Ticket categories, priorities and replies are preserved on contacts.
     * Exact duplicate product inquiries are merged into the existing contact
     * instead of being inserted a second time.
     */
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->default('General')->after('product_id');
            $table->string('priority', 20)->nullable()->default('medium')->after('category');
            $table->unsignedBigInteger('legacy_ticket_id')->nullable()->after('priority');
            $table->index('category', 'contacts_category_index');
        });

        if (! Schema::hasTable('support_tickets')) {
            return;
        }

        $hasReplies = Schema::hasTable('support_ticket_replies');
        $now = now()->format('Y-m-d H:i:s');

        DB::table('support_tickets')->orderBy('id')->each(function ($ticket) use ($hasReplies, $now) {
            $replies = $hasReplies
                ? DB::table('support_ticket_replies')
                    ->where('support_ticket_id', $ticket->id)
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get()
                : collect();

            $adminReplies = $replies->where('sender_type', 'admin');
            $lastAdminReply = $adminReplies->isNotEmpty() ? $adminReplies->last() : null;

            $header = sprintf(
                '[Migrated from Support Ticket #%d | Category: %s | Priority: %s | Status: %s]',
                $ticket->id,
                $ticket->category ?: 'General',
                $ticket->priority ?: 'medium',
                $ticket->status ?: 'open'
            );

            $thread = $header."\n\n".trim((string) $ticket->message);
            foreach ($replies as $reply) {
                $label = $reply->sender_type === 'admin' ? 'Support reply' : 'Customer reply';
                $thread .= "\n\n--- {$label} ({$reply->created_at}) ---\n".trim((string) $reply->message);
            }

            $replyText = $lastAdminReply ? trim((string) $lastAdminReply->message) : null;
            if ($replyText === null && in_array($ticket->status, ['resolved', 'closed'], true)) {
                $replyText = 'This support ticket was marked '.$ticket->status.' during migration.';
            }

            $repliedAt = $lastAdminReply->created_at ?? ($replyText ? ($ticket->updated_at ?: $now) : null);
            $repliedBy = $lastAdminReply->sender_id ?? null;

            $match = DB::table('contacts')
                ->where('email', $ticket->email)
                ->where('message', $ticket->message)
                ->orderBy('id')
                ->first();

            if (! $match && $ticket->subject) {
                $match = DB::table('contacts')
                    ->where('email', $ticket->email)
                    ->where('subject', $ticket->subject)
                    ->orderBy('id')
                    ->first();
            }

            $category = $ticket->category ?: 'General';
            $priority = $ticket->priority ?: 'medium';
            $updatedAt = $ticket->updated_at ?: $now;

            if ($match) {
                $update = [
                    'category' => $category,
                    'priority' => $priority,
                    'legacy_ticket_id' => $ticket->id,
                    'updated_at' => $updatedAt,
                ];

                if (! $match->reply && $replyText) {
                    $update['reply'] = $replyText;
                    $update['replied_by'] = $repliedBy;
                    $update['replied_at'] = $repliedAt;
                }

                DB::table('contacts')->where('id', $match->id)->update($update);

                return;
            }

            DB::table('contacts')->insert([
                'user_id' => $ticket->user_id,
                'product_id' => null,
                'name' => $ticket->name,
                'email' => $ticket->email,
                'phone' => null,
                'subject' => 'Support ticket: '.$ticket->subject,
                'message' => $thread,
                'category' => $category,
                'priority' => $priority,
                'legacy_ticket_id' => $ticket->id,
                'reply' => $replyText,
                'replied_by' => $repliedBy,
                'replied_at' => $repliedAt,
                'created_at' => $ticket->created_at ?: $now,
                'updated_at' => $updatedAt,
            ]);
        });

        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
    }

    public function down(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->string('category')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('open');
            $table->text('message');
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_type')->default('admin');
            $table->text('message');
            $table->timestamps();
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_category_index');
            $table->dropColumn(['category', 'priority', 'legacy_ticket_id']);
        });
    }
};
