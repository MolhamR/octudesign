<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShamCashWalletSupport extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'sham_cash_account')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('sham_cash_account', 64)->nullable()->after('role');
            });
        }

        if (!Schema::hasColumn('wallet_transactions', 'status')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->string('status', 20)->default('completed')->after('description');
            });
        }

        if (!Schema::hasColumn('wallet_transactions', 'sham_cash_tran_id')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->string('sham_cash_tran_id', 100)->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('wallet_transactions', 'verified_at')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->timestamp('verified_at')->nullable()->after('sham_cash_tran_id');
            });
        }

        try {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->unique('sham_cash_tran_id');
            });
        } catch (\Exception $e) {
            // index may already exist
        }

        if (!Schema::hasTable('system_wallet_transactions')) {
            Schema::create('system_wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('type', 20); // in / out
                $table->decimal('amount', 10, 2)->default(0);
                $table->string('note', 255)->nullable();
                $table->timestamp('entry_date')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sham_cash_account');
        });
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['status', 'sham_cash_tran_id', 'verified_at']);
        });
        Schema::dropIfExists('system_wallet_transactions');
    }
}