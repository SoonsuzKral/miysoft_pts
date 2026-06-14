<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $fkLocation = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'location_personel' AND COLUMN_NAME = 'location_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        if (empty($fkLocation)) {
            DB::statement('ALTER TABLE location_personel ADD CONSTRAINT location_personel_location_id_foreign FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE');
        }

        $fkPersonel = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'location_personel' AND COLUMN_NAME = 'personel_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        if (empty($fkPersonel)) {
            DB::statement('ALTER TABLE location_personel ADD CONSTRAINT location_personel_personel_id_foreign FOREIGN KEY (personel_id) REFERENCES personels(id) ON DELETE CASCADE');
        }

        $uniqueExists = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'location_personel' AND CONSTRAINT_NAME = 'loc_per_type_unique'");
        if (empty($uniqueExists)) {
            DB::statement('ALTER TABLE location_personel ADD UNIQUE KEY loc_per_type_unique (location_id, personel_id, type)');
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE location_personel DROP INDEX loc_per_type_unique');
    }
};
