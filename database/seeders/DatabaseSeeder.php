<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        // ══════════════════════════════════════════════
        // 1. TỔ CHUYÊN MÔN
        // ══════════════════════════════════════════════
        $departments = [
            ['name' => 'KHTN'],
            ['name' => 'T-A-TI-TC-QP'],
            ['name' => 'KHXH'],
            ['name' => 'VĂN PHÒNG'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['name' => $dept['name']]);
        }

        // ══════════════════════════════════════════════
        // 2. TÀI KHOẢN NGƯỜI DÙNG
        // ══════════════════════════════════════════════
        $this->call(RealStaffSeeder::class);

        $admin = User::where('email', 'admin@truong.edu.vn')->first();

        // ══════════════════════════════════════════════
        // 3. PHÒNG HỌC / KHO
        // ══════════════════════════════════════════════
        $rooms = [
            ['name' => 'Kho Tổng', 'type' => 'warehouse', 'manager_email' => $admin?->email ?? 'admin@truong.edu.vn', 'location' => 'Tầng 1, Dãy A'],
            ['name' => 'Kho QPAN', 'type' => 'warehouse', 'manager_email' => $admin?->email ?? 'admin@truong.edu.vn', 'location' => 'Tầng 1, Dãy C'],
            ['name' => 'Phòng TH Vật lý', 'type' => 'lab', 'manager_email' => 'lan.tran@truong.edu.vn', 'location' => 'Tầng 2, Dãy B', 'capacity' => 40],
            ['name' => 'Phòng TH Hóa học', 'type' => 'lab', 'manager_email' => 'hung.pham@truong.edu.vn', 'location' => 'Tầng 2, Dãy B', 'capacity' => 40],
            ['name' => 'Phòng TH Sinh học', 'type' => 'lab', 'manager_email' => 'hoa.hoang@truong.edu.vn', 'location' => 'Tầng 3, Dãy B', 'capacity' => 40],
            ['name' => 'Phòng Tin học', 'type' => 'lab', 'manager_email' => 'mai.le@truong.edu.vn', 'location' => 'Tầng 3, Dãy A', 'capacity' => 45],
        ];

        foreach ($rooms as $room) {
            $manager = User::where('email', $room['manager_email'])->first();

            Room::updateOrCreate(
                ['name' => $room['name']],
                [
                    'type' => $room['type'],
                    'manager_id' => $manager?->id,
                    'location' => $room['location'],
                    'capacity' => $room['capacity'] ?? null,
                ]
            );
        }

        // ══════════════════════════════════════════════
        // 4. DANH MỤC THIẾT BỊ + CÁ THỂ
        // ══════════════════════════════════════════════
        $this->call(RealEquipmentSeeder::class);

        $this->command->info('✅ Seeded: ' . Department::count() . ' tổ chuyên môn');
        $this->command->info('✅ Seeded: ' . User::count() . ' tài khoản');
        $this->command->info('✅ Seeded: ' . Room::count() . ' phòng/kho');
        $this->command->info('✅ Seeded: ' . Equipment::count() . ' danh mục thiết bị');
        $this->command->info('✅ Seeded: ' . EquipmentItem::count() . ' cá thể vật lý');
        $this->command->info('🎉 Database seeding hoàn tất!');
    }
}
