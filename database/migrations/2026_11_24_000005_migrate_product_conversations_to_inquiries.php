<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fold Product Conversations into the Questions / Inquiries inbox.
     *
     * The full message thread is kept on the contact message, while the last
     * staff reply is mirrored into the normal reply fields. Existing contact
     * duplicates are merged instead of inserted twice.
     */
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_conversation_id')->nullable()->after('legacy_ticket_id');
        });

        if (! Schema::hasTable('product_conversations')) {
            return;
        }

        $hasMessages = Schema::hasTable('product_conversation_messages');
        $now = now()->format('Y-m-d H:i:s');

        DB::table('product_conversations')->orderBy('id')->each(function ($conversation) use ($hasMessages, $now) {
            $messages = $hasMessages
                ? DB::table('product_conversation_messages')
                    ->where('product_conversation_id', $conversation->id)
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get()
                : collect();

            $product = $conversation->product_id
                ? DB::table('products')->where('id', $conversation->product_id)->first(['id', 'name', 'slug'])
                : null;
            $user = $conversation->user_id
                ? DB::table('users')->where('id', $conversation->user_id)->first(['id', 'name', 'email'])
                : null;

            $productName = $product->name ?? 'Deleted product';
            $productUrl = $product ? route('product', $product->slug) : '';
            $status = $conversation->status ?: 'open';
            $header = "[Migrated from Product Conversation #{$conversation->id} | Status: {$status}]";

            $firstCustomer = $messages->firstWhere('sender_type', 'customer') ?: $messages->first();
            $staffMessages = $messages->filter(fn ($message) => $message->sender_type !== 'customer');
            $lastStaff = $staffMessages->isNotEmpty() ? $staffMessages->last() : null;

            $replyText = $lastStaff ? trim((string) $lastStaff->message) : null;
            if ($replyText === null && in_array($status, ['resolved', 'closed'], true)) {
                $replyText = 'This product conversation was marked '.$status.' during migration.';
            }
            $repliedAt = $lastStaff->created_at ?? ($replyText ? ($conversation->last_message_at ?: $conversation->updated_at ?: $now) : null);
            $repliedBy = $lastStaff->sender_id ?? null;

            $thread = "Product: {$productName}\nProduct URL: {$productUrl}\n\n{$header}\n";
            foreach ($messages as $message) {
                $label = ucfirst($message->sender_type ?: 'customer');
                $thread .= "--- {$label} ({$message->created_at}) ---\n".trim((string) $message->message)."\n\n";
            }
            $thread = trim($thread);

            $match = DB::table('contacts')
                ->where('user_id', $conversation->user_id)
                ->where('product_id', $conversation->product_id)
                ->when($firstCustomer, function ($query) use ($firstCustomer) {
                    $needle = addcslashes(trim((string) $firstCustomer->message), '%_\\');
                    $query->where('message', 'like', '%'.$needle.'%');
                })
                ->orderBy('id')
                ->first();

            if (! $match && $conversation->created_at) {
                $match = DB::table('contacts')
                    ->where('user_id', $conversation->user_id)
                    ->where('product_id', $conversation->product_id)
                    ->where('created_at', $conversation->created_at)
                    ->orderBy('id')
                    ->first();
            }

            $updatedAt = $conversation->updated_at ?: $now;

            if ($match) {
                $update = [
                    'category' => 'Product',
                    'priority' => 'medium',
                    'legacy_conversation_id' => $conversation->id,
                    'updated_at' => $updatedAt,
                ];

                if (strpos((string) $match->message, $header) === false) {
                    $append = "\n\n{$header}\n";
                    foreach ($messages as $message) {
                        if ($firstCustomer && (int) $message->id === (int) $firstCustomer->id) {
                            continue;
                        }
                        $label = ucfirst($message->sender_type ?: 'customer');
                        $append .= "--- {$label} ({$message->created_at}) ---\n".trim((string) $message->message)."\n\n";
                    }
                    $update['message'] = rtrim((string) $match->message)."\n\n".trim($append);
                }

                if (! $match->reply && $replyText) {
                    $update['reply'] = $replyText;
                    $update['replied_by'] = $repliedBy;
                    $update['replied_at'] = $repliedAt;
                }

                DB::table('contacts')->where('id', $match->id)->update($update);

                return;
            }

            DB::table('contacts')->insert([
                'user_id' => $conversation->user_id,
                'product_id' => $conversation->product_id,
                'name' => $user->name ?? 'Customer',
                'email' => $user->email ?? '',
                'phone' => null,
                'subject' => $conversation->subject ?: 'Product conversation: '.$productName,
                'message' => $thread,
                'category' => 'Product',
                'priority' => 'medium',
                'legacy_conversation_id' => $conversation->id,
                'reply' => $replyText,
                'replied_by' => $repliedBy,
                'replied_at' => $repliedAt,
                'created_at' => $conversation->created_at ?: $now,
                'updated_at' => $updatedAt,
            ]);
        });

        Schema::dropIfExists('product_conversation_messages');
        Schema::dropIfExists('product_conversations');
    }

    public function down(): void
    {
        Schema::create('product_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->string('status')->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'last_message_at']);
        });

        Schema::create('product_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_conversation_id')->constrained('product_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_type')->default('admin');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('legacy_conversation_id');
        });
    }
};
