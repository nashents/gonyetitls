<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Generalizes the trip-only edit-authorization workflow into a polymorphic
 * one reusable by any lockable module (trips, bills, and later invoices).
 * Existing trip_edit_authorizers / trip_edit_authorization_requests rows are
 * migrated into the new tables (module = 'trips') and the old tables dropped.
 */
class CreateEditAuthorizersTables extends Migration
{
    public function up()
    {
        Schema::create('edit_authorizers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('module');
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['module', 'status']);
        });

        Schema::create('edit_authorization_requests', function (Blueprint $table) {
            $table->id();
            $table->morphs('editable');
            $table->string('module');
            $table->bigInteger('owner_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('reason')->nullable();

            $table->string('status')->default('pending');
            $table->bigInteger('decided_by')->unsigned()->nullable();
            $table->foreign('decided_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_comments')->nullable();

            $table->timestamp('consumed_at')->nullable();

            $table->timestamps();

            $table->index(['module', 'status']);
            $table->index(['user_id', 'status']);
        });

        if (Schema::hasTable('trip_edit_authorizers')) {
            DB::table('trip_edit_authorizers')->orderBy('id')->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('edit_authorizers')->insert([
                        'user_id' => $row->user_id,
                        'module' => 'trips',
                        'created_by' => $row->created_by,
                        'status' => $row->status,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'deleted_at' => $row->deleted_at,
                    ]);
                }
            });
        }

        if (Schema::hasTable('trip_edit_authorization_requests')) {
            DB::table('trip_edit_authorization_requests')->orderBy('id')->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    $ownerId = DB::table('trips')->where('id', $row->trip_id)->value('user_id');

                    DB::table('edit_authorization_requests')->insert([
                        'editable_type' => \App\Models\Trip::class,
                        'editable_id' => $row->trip_id,
                        'module' => 'trips',
                        'owner_id' => $ownerId,
                        'user_id' => $row->user_id,
                        'reason' => $row->reason,
                        'status' => $row->status,
                        'decided_by' => $row->decided_by,
                        'decided_at' => $row->decided_at,
                        'decision_comments' => $row->decision_comments,
                        'consumed_at' => $row->consumed_at,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            });
        }

        Schema::dropIfExists('trip_edit_authorization_requests');
        Schema::dropIfExists('trip_edit_authorizers');
    }

    public function down()
    {
        Schema::create('trip_edit_authorizers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('trip_edit_authorization_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('trip_id')->unsigned();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('reason')->nullable();

            $table->string('status')->default('pending');
            $table->bigInteger('decided_by')->unsigned()->nullable();
            $table->foreign('decided_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_comments')->nullable();

            $table->timestamp('consumed_at')->nullable();

            $table->timestamps();

            $table->index(['trip_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        if (Schema::hasTable('edit_authorizers')) {
            DB::table('edit_authorizers')->where('module', 'trips')->orderBy('id')->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('trip_edit_authorizers')->insert([
                        'id' => $row->id,
                        'user_id' => $row->user_id,
                        'created_by' => $row->created_by,
                        'status' => $row->status,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'deleted_at' => $row->deleted_at,
                    ]);
                }
            });
        }

        if (Schema::hasTable('edit_authorization_requests')) {
            DB::table('edit_authorization_requests')->where('module', 'trips')->orderBy('id')->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('trip_edit_authorization_requests')->insert([
                        'id' => $row->id,
                        'trip_id' => $row->editable_id,
                        'user_id' => $row->user_id,
                        'reason' => $row->reason,
                        'status' => $row->status,
                        'decided_by' => $row->decided_by,
                        'decided_at' => $row->decided_at,
                        'decision_comments' => $row->decision_comments,
                        'consumed_at' => $row->consumed_at,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            });
        }

        Schema::dropIfExists('edit_authorization_requests');
        Schema::dropIfExists('edit_authorizers');
    }
}
