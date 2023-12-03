<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $schema = Capsule::schema();
        $schema->create('content_elements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('content_type_id')->constrained('content_types');
            $table->foreignId('section_id')->nullable()->constrained('content_sections');
            $table->string('name');
            $table->string('code')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $schema = Capsule::schema();
        $schema->dropIfExists('content_elements');
    }
};
