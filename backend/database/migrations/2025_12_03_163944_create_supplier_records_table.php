<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_records', function (Blueprint $table) {
        $table->id();

        $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
        $table->foreignId('item_id')->constrained('items')->onDelete('cascade');

        $table->integer('quantity_supplied');
        $table->decimal('total_cost',10,2)->nullable(); // <— new
        $table->decimal('cost_per_unit',10,2); // <— new

        $table->timestamp('date')->useCurrent();
        $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_records');
    }
};
