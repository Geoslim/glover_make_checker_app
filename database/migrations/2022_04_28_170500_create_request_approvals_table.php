<?php

use App\Models\RequestApproval;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('request_approvals', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('approvable');
            $table->string('request_type');
            $table->string('status')->default(RequestApproval::STATUS_PENDING);
            $table->json('data');
            $table->foreignId('requested_id')->nullable()->constrained('admins');
            $table->foreignId('approved_id')->nullable()->constrained('admins');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('request_approvals');
    }
}
