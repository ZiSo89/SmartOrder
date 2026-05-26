<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $languages = [
            ['code' => 'el', 'name' => 'Greek',    'direction' => 'ltr', 'position' => 2, 'active' => true],
            ['code' => 'bg', 'name' => 'Bulgarian', 'direction' => 'ltr', 'position' => 3, 'active' => true],
            ['code' => 'tr', 'name' => 'Turkish',   'direction' => 'ltr', 'position' => 4, 'active' => true],
        ];

        foreach ($languages as $lang) {
            $exists = DB::table('languages')->where('code', $lang['code'])->exists();
            if (! $exists) {
                DB::table('languages')->insert($lang);
            }
        }
    }

    public function down(): void
    {
        DB::table('languages')->whereIn('code', ['el', 'bg', 'tr'])->delete();
    }
};
