<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
        $table->id();
        $table->string('supplier_name');
        $table->string('contact')->nullable();
        $table->string('email')->nullable();

        // New fields
       // $table->string('item_supplied')->nullable();  // what item they supply
        $table->decimal('cost_per_unit',10,2)->nullable(); // user can input cost

        $table->timestamps();
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
