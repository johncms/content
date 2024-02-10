<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $schema = Capsule::schema();
        $schema->create('content_element_files', function (Blueprint $table) {
            $table->foreignId('element_id')->constrained('content_elements');
            $table->foreignId('file_id')->constrained('files');

            $table->unique(['element_id', 'file_id'], 'element_file');
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
        $schema->dropIfExists('content_element_files');
    }
};
