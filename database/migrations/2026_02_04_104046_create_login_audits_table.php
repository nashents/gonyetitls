<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('login_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamp('logged_in_at')->index();

            // Network / device
            $table->string('ip', 45)->nullable()->index();          // IPv4/IPv6
            $table->text('user_agent')->nullable();

            // Geo (IP-based, approximate)
            $table->string('country', 100)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('zip', 30)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // Extra metadata (optional but useful)
            $table->string('provider', 100)->nullable(); // ISP/Org if available
            $table->string('source', 50)->default('web'); // web, api, mobile, etc.

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'logged_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_audits');
    }
};
