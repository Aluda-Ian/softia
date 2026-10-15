<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Short (max two-word) name for the main menu, e.g. "Chemical Care".
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('menu_label', 40)->nullable()->after('name');
        });

        foreach ([
            'paper-products' => 'Paper Products',
            'chemical-care' => 'Chemical Care',
            'institutional-hardware' => 'Institutional Hardware',
            'aluminium-foil-packaging' => 'Aluminium Foil',
        ] as $slug => $label) {
            DB::table('categories')->where('slug', $slug)->update(['menu_label' => $label]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('menu_label');
        });
    }
};
