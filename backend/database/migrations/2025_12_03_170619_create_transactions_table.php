<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');

            // Handled by (supplier)
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('supplier_name')->nullable(); // <-- ADDED THIS

            // System user (optional, for internal audit trail)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('type', [
                'stock_in',
                'stock_out',
                'item_added',
                'item_deleted',
                'item_edited'
            ]);

            $table->integer('quantity')->nullable();
            $table->string('purpose')->nullable();
            $table->timestamp('date')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
