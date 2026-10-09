<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('library_categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->default(0)->index();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->integer('sort')->default(0);
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        Schema::create('library_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->index();
            $table->bigInteger('category_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('file')->nullable();
            $table->integer('is_paid')->default(0);
            $table->double('price', 10, 2)->default(0);
            $table->integer('discount_flag')->default(0);
            $table->double('discounted_price', 10, 2)->default(0);
            $table->string('status')->default('active');
            $table->bigInteger('downloads')->default(0);
            $table->timestamps();
        });

        Schema::create('library_purchases', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->index();
            $table->bigInteger('library_item_id')->nullable()->index();
            $table->string('purchase_type')->nullable();
            $table->string('invoice')->nullable();
            $table->string('payment_type')->nullable();
            $table->double('amount', 10, 2)->default(0);
            $table->double('tax', 10, 2)->default(0);
            $table->string('coupon')->nullable();
            $table->double('admin_revenue', 10, 2)->default(0);
            $table->double('instructor_revenue', 10, 2)->default(0);
            $table->text('transaction_id')->nullable();
            $table->string('session_id')->nullable();
            $table->bigInteger('entry_date')->nullable();
            $table->timestamps();
        });

        Schema::create('library_cart_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->index();
            $table->bigInteger('library_item_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('library_cart_items');
        Schema::dropIfExists('library_purchases');
        Schema::dropIfExists('library_items');
        Schema::dropIfExists('library_categories');
    }
};
