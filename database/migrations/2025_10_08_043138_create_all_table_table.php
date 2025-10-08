<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000000_create_core_tables.php
return new class extends Migration {
  public function up(): void {
    Schema::create('pubs', function (Blueprint $t) {
      $t->id();
      $t->string('pub_number')->unique();   // e.g., 6641 (leading zeros client rule)
      $t->string('name');
      $t->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
      $t->timestamps();
    });

    Schema::create('kpi_categories', function (Blueprint $t) {
      $t->id(); $t->string('code')->unique(); $t->string('name'); $t->timestamps();
    });

    Schema::create('periods', function (Blueprint $t) {
      $t->id();              // 1..12
      $t->string('year');    // 2025
      $t->timestamps();
    });

    Schema::create('weeks', function (Blueprint $t) {
      $t->id();
      $t->foreignId('period_id')->constrained()->cascadeOnDelete();
      $t->unsignedTinyInteger('week_no'); // 1..5
      $t->date('start_date'); $t->date('end_date');
      $t->unique(['period_id','week_no']);
      $t->timestamps();
    });

    Schema::create('kpi_records', function (Blueprint $t) {
      $t->id();
      $t->foreignId('pub_id')->constrained()->cascadeOnDelete();
      $t->foreignId('period_id')->constrained()->cascadeOnDelete();
      $t->foreignId('kpi_category_id')->constrained('kpi_categories');
      $t->decimal('value',10,2)->nullable();
      $t->json('meta')->nullable();
      $t->timestamps();
      $t->unique(['pub_id','period_id','kpi_category_id']);
    });

    Schema::create('compliance_tasks', function (Blueprint $t) {
      $t->id();
      $t->string('name');
      $t->boolean('is_active')->default(true);
      $t->timestamps();
    });

    Schema::create('shift_compliances', function (Blueprint $t) {
      $t->id();
      $t->foreignId('pub_id')->constrained()->cascadeOnDelete();
      $t->foreignId('week_id')->constrained('weeks')->cascadeOnDelete();
      $t->foreignId('user_id')->constrained()->cascadeOnDelete(); // who submitted
      $t->unsignedTinyInteger('score')->nullable(); // e.g., overall %
      $t->json('summary')->nullable();
      $t->timestamps();
      $t->unique(['pub_id','week_id']);
    });

// database/migrations/xxxx_create_shift_compliance_items_table.php

Schema::create('shift_compliance_items', function (Blueprint $t) {
    $t->id();
    $t->foreignId('shift_compliance_id')->constrained()->cascadeOnDelete();
    $t->foreignId('compliance_task_id')->constrained()->cascadeOnDelete();
    $t->boolean('done')->default(false);
    $t->text('note')->nullable();
    $t->timestamps();

    // 👇 custom short unique index name
    $t->unique(
        ['shift_compliance_id', 'compliance_task_id'],
        'uniq_shift_task'
    );
});


    // Spatie permission tables
    // php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  }
};

