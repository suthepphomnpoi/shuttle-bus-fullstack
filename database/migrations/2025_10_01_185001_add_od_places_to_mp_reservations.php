<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mp_reservations', function (Blueprint $table) {
            // 1) เพิ่มคอลัมน์ เฉพาะถ้ายังไม่มี
            if (!Schema::hasColumn('mp_reservations', 'origin_place_id')) {
                $table->unsignedBigInteger('origin_place_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('mp_reservations', 'destination_place_id')) {
                $table->unsignedBigInteger('destination_place_id')->nullable()->after('origin_place_id');
            }
        });

        // 2) Index (สร้างถ้ายังไม่มี)
        if (!$this->oracleIndexExists('IX_RESV_ORG')) {
            DB::statement('CREATE INDEX IX_RESV_ORG ON MP_RESERVATIONS (ORIGIN_PLACE_ID)');
        }
        if (!$this->oracleIndexExists('IX_RESV_DST')) {
            DB::statement('CREATE INDEX IX_RESV_DST ON MP_RESERVATIONS (DESTINATION_PLACE_ID)');
        }

        // 3) Foreign Keys (เฉพาะถ้ายังไม่มี)
        if (!$this->oracleConstraintExists('FK_RESV_ORG')) {
            DB::statement('ALTER TABLE MP_RESERVATIONS ADD CONSTRAINT FK_RESV_ORG FOREIGN KEY (ORIGIN_PLACE_ID) REFERENCES MP_PLACES (PLACE_ID)');
        }
        if (!$this->oracleConstraintExists('FK_RESV_DST')) {
            DB::statement('ALTER TABLE MP_RESERVATIONS ADD CONSTRAINT FK_RESV_DST FOREIGN KEY (DESTINATION_PLACE_ID) REFERENCES MP_PLACES (PLACE_ID)');
        }
    }

    public function down(): void
    {
        // ลบ FK ถ้ามี
        if ($this->oracleConstraintExists('FK_RESV_ORG')) {
            DB::statement('ALTER TABLE MP_RESERVATIONS DROP CONSTRAINT FK_RESV_ORG');
        }
        if ($this->oracleConstraintExists('FK_RESV_DST')) {
            DB::statement('ALTER TABLE MP_RESERVATIONS DROP CONSTRAINT FK_RESV_DST');
        }

        // ลบ Index ถ้ามี
        if ($this->oracleIndexExists('IX_RESV_ORG')) {
            DB::statement('DROP INDEX IX_RESV_ORG');
        }
        if ($this->oracleIndexExists('IX_RESV_DST')) {
            DB::statement('DROP INDEX IX_RESV_DST');
        }

        // ลบคอลัมน์ ถ้ามี
        Schema::table('mp_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('mp_reservations', 'origin_place_id')) {
                $table->dropColumn('origin_place_id');
            }
            if (Schema::hasColumn('mp_reservations', 'destination_place_id')) {
                $table->dropColumn('destination_place_id');
            }
        });
    }

    // ===== Helpers สำหรับ Oracle =====
    private function oracleConstraintExists(string $name): bool
    {
        $sql = "SELECT COUNT(*) AS CNT FROM USER_CONSTRAINTS WHERE CONSTRAINT_NAME = UPPER(?)";
        $row = DB::selectOne($sql, [$name]);
        return (int)($row->cnt ?? 0) > 0;
    }

    private function oracleIndexExists(string $name): bool
    {
        $sql = "SELECT COUNT(*) AS CNT FROM USER_INDEXES WHERE INDEX_NAME = UPPER(?)";
        $row = DB::selectOne($sql, [$name]);
        return (int)($row->cnt ?? 0) > 0;
    }
};
