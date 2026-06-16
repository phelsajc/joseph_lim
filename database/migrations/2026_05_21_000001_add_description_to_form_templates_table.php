<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToFormTemplatesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('form_templates')) {
            return;
        }

        Schema::table('form_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('form_templates', 'description')) {
                $table->string('description', 1000)->nullable()->after('category');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('form_templates')) {
            return;
        }

        Schema::table('form_templates', function (Blueprint $table) {
            if (Schema::hasColumn('form_templates', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
}
