<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = DB::table('companies')->where('domain', 'miysoft.com.tr')->value('id');
        if (!$companyId) {
            $companyId = DB::table('companies')->first()->id;
        }

        $personelIds = DB::table('personels')->where('company_id', $companyId)->pluck('id');
        $deptIds = DB::table('departments')->where('company_id', $companyId)->pluck('id');
        $posIds = DB::table('positions')->where('company_id', $companyId)->pluck('id');

        // Add more personnel for richer data (total ~30 for this company)
        $existingCount = $personelIds->count();
        $extraGenders = ['M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F'];
        $extraStatuses = ['active', 'active', 'active', 'active', 'active', 'on_leave', 'terminated'];
        for ($i = 0; $i < max(0, 30 - $existingCount); $i++) {
            $gen = $extraGenders[$i % count($extraGenders)];
            $fn = fake('tr_TR')->firstName($gen === 'F' ? 'female' : 'male');
            $ln = fake('tr_TR')->lastName();
            DB::table('personels')->insert([
                'company_id'    => $companyId,
                'first_name'    => $fn,
                'last_name'     => $ln,
                'email'         => strtolower($fn . '.' . $ln . rand(10, 999) . '@miysoft.com.tr'),
                'phone'         => fake('tr_TR')->phoneNumber(),
                'birth_date'    => Carbon::today()->subYears(rand(22, 55))->subDays(rand(1, 365))->toDateString(),
                'gender'        => $gen,
                'department_id' => $deptIds->random(),
                'position_id'   => $posIds->random(),
                'salary'        => rand(20000, 80000),
                'currency'      => 'TRY',
                'hire_date'     => Carbon::today()->subMonths(rand(1, 72))->toDateString(),
                'status'        => $extraStatuses[array_rand($extraStatuses)],
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Refresh personel IDs
        $personelIds = DB::table('personels')->where('company_id', $companyId)->pluck('id');

        // Update birthdays - more for this month (for birthday widget)
        foreach ($personelIds->take(5) as $pid) {
            $bd = Carbon::today()->addDays(rand(0, 27))->setYear(rand(1980, 2000));
            DB::table('personels')->where('id', $pid)->update(['birth_date' => $bd->toDateString()]);
        }
        // Set hire date to this month for anniversary widget
        foreach ($personelIds->slice(3, 4) as $pid) {
            $hd = Carbon::today()->subYears(rand(2, 15))->addDays(rand(0, 27));
            DB::table('personels')->where('id', $pid)->update([
                'hire_date' => $hd->toDateString(),
                'status'    => 'active',
            ]);
        }
        // Set hire dates across last 6 months for trend chart
        foreach ($personelIds->take(10) as $i => $pid) {
            $monthsAgo = intdiv($i, 2) + 1;
            $hd = Carbon::today()->subMonths($monthsAgo)->subDays(rand(0, 20));
            DB::table('personels')->where('id', $pid)->update([
                'hire_date' => $hd->toDateString(),
                'status'    => 'active',
            ]);
        }
        // Set some termination dates across last 6 months for trend chart
        foreach ($personelIds->slice(6, 4) as $i => $pid) {
            $monthsAgo = $i + 1;
            DB::table('personels')->where('id', $pid)->update([
                'status'           => 'terminated',
                'is_active'        => false,
                'termination_date' => Carbon::today()->subMonths($monthsAgo)->subDays(rand(0, 15))->toDateString(),
            ]);
        }
        // Set some as on_leave or suspended for status distribution
        foreach ($personelIds->slice(10, 3) as $pid) {
            DB::table('personels')->where('id', $pid)->update([
                'status'    => 'on_leave',
                'is_active' => true,
            ]);
        }
        foreach ($personelIds->slice(13, 2) as $pid) {
            DB::table('personels')->where('id', $pid)->update([
                'status'    => 'suspended',
                'is_active' => false,
            ]);
        }
        // Set a terminated person this month
        $termPid = $personelIds->last();
        DB::table('personels')->where('id', $termPid)->update([
            'status'           => 'terminated',
            'is_active'        => false,
            'termination_date' => Carbon::today()->subDays(rand(1, 20))->toDateString(),
        ]);

        // ─── Admin User ───────────────────────────────────────────────────────
        $adminUser = DB::table('users')->where('email', 'admin@miysoft.com.tr')->first();
        if (!$adminUser) {
            $userId = DB::table('users')->insertGetId([
                'company_id' => $companyId,
                'name'       => 'Admin',
                'email'      => 'admin@miysoft.com.tr',
                'password'   => Hash::make('password'),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('model_has_roles')->insert([
                'role_id'    => DB::table('roles')->where('name', 'super_admin')->value('id'),
                'model_type' => 'App\Models\User',
                'model_id'   => $userId,
            ]);
        }

        // ─── Leave Types ──────────────────────────────────────────────────────
        $leaveTypes = [
            ['company_id' => $companyId, 'name' => 'Yıllık İzin',         'paid' => true,  'max_annual_days' => 20, 'requires_approval' => true],
            ['company_id' => $companyId, 'name' => 'Hastalık İzni',       'paid' => true,  'max_annual_days' => 10, 'requires_approval' => true],
            ['company_id' => $companyId, 'name' => 'Ücretsiz İzin',       'paid' => false, 'max_annual_days' => 30, 'requires_approval' => true],
            ['company_id' => $companyId, 'name' => 'Doğum İzni',          'paid' => true,  'max_annual_days' => 112, 'requires_approval' => true],
            ['company_id' => $companyId, 'name' => 'Babalık İzni',        'paid' => true,  'max_annual_days' => 10, 'requires_approval' => true],
            ['company_id' => $companyId, 'name' => 'Evlilik İzni',        'paid' => true,  'max_annual_days' => 5, 'requires_approval' => true],
            ['company_id' => $companyId, 'name' => 'Ölüm İzni',           'paid' => true,  'max_annual_days' => 3, 'requires_approval' => true],
        ];
        foreach ($leaveTypes as $lt) {
            DB::table('leave_types')->updateOrInsert(
                ['company_id' => $companyId, 'name' => $lt['name']],
                array_merge($lt, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
        $leaveTypeIds = DB::table('leave_types')->where('company_id', $companyId)->pluck('id');

        // ─── Leave Balances ──────────────────────────────────────────────────
        foreach ($personelIds as $pid) {
            foreach ($leaveTypeIds as $lid) {
                $entitled = match (DB::table('leave_types')->where('id', $lid)->value('max_annual_days')) {
                    20      => 20,
                    112     => 112,
                    10      => 10,
                    5       => 5,
                    3       => 3,
                    default => 30,
                };
                DB::table('leave_balances')->updateOrInsert(
                    ['personel_id' => $pid, 'leave_type_id' => $lid, 'year' => now()->year],
                    [
                        'entitled_days'  => $entitled,
                        'used_days'      => rand(0, min(8, (int)$entitled)),
                        'remaining_days' => $entitled - rand(0, min(8, (int)$entitled)),
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]
                );
            }
        }

        // ─── Leave Requests ──────────────────────────────────────────────────
        $statuses = ['pending', 'pending', 'approved', 'approved', 'approved', 'rejected', 'cancelled'];
        // Guarantee someone on leave today
        DB::table('leave_requests')->insert([
            'company_id'    => $companyId,
            'personel_id'   => $personelIds[0],
            'leave_type_id' => $leaveTypeIds->random(),
            'start_date'    => Carbon::today()->subDays(1)->toDateString(),
            'end_date'      => Carbon::today()->addDays(2)->toDateString(),
            'total_days'    => 4,
            'reason'        => 'Yıllık izin',
            'status'        => 'approved',
            'created_at'    => now()->subDays(7),
            'updated_at'    => now(),
        ]);
        // Guarantee pending requests
        foreach ($personelIds->take(3) as $pid) {
            $start = Carbon::today();
            $end = (clone $start)->addDays(rand(2, 5));
            DB::table('leave_requests')->insert([
                'company_id'    => $companyId,
                'personel_id'   => $pid,
                'leave_type_id' => $leaveTypeIds->random(),
                'start_date'    => $start->toDateString(),
                'end_date'      => $end->toDateString(),
                'total_days'    => $start->diffInDays($end) + 1,
                'reason'        => fake('tr_TR')->sentence(6),
                'status'        => 'pending',
                'created_at'    => now()->subDays(rand(1, 5)),
                'updated_at'    => now(),
            ]);
        }
        // Random historical leaves
        foreach ($personelIds->take(22) as $pid) {
            $ltId = $leaveTypeIds->random();
            $start = Carbon::today()->subDays(rand(1, 60));
            $end = (clone $start)->addDays(rand(1, 5));
            DB::table('leave_requests')->insert([
                'company_id'    => $companyId,
                'personel_id'   => $pid,
                'leave_type_id' => $ltId,
                'start_date'    => $start->toDateString(),
                'end_date'      => $end->toDateString(),
                'total_days'    => $start->diffInDays($end) + 1,
                'reason'        => fake('tr_TR')->sentence(6),
                'status'        => $statuses[array_rand($statuses)],
                'created_at'    => $start->subDays(rand(3, 10)),
                'updated_at'    => now(),
            ]);
        }

        // ─── Time Records (bugünkü giriş/çıkış kayıtları) ────────────────────
        $sources = ['web', 'mobile', 'biometric'];
        foreach ($personelIds->take(rand(15, 25)) as $pid) {
            $checkIn = Carbon::today()->setHour(rand(8, 10))->setMinute(rand(0, 59));
            DB::table('time_records')->insert([
                'company_id'  => $companyId,
                'personel_id' => $pid,
                'type'        => 'in',
                'recorded_at' => $checkIn,
                'source'      => $sources[array_rand($sources)],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            if (rand(0, 1)) {
                DB::table('time_records')->insert([
                    'company_id'  => $companyId,
                    'personel_id' => $pid,
                    'type'        => 'out',
                    'recorded_at' => Carbon::today()->setHour(rand(17, 19))->setMinute(rand(0, 59)),
                    'source'      => $sources[array_rand($sources)],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // Son 6 ay için chart verisi (time_records + leave_requests)
        for ($m = 5; $m >= 1; $m--) {
            $monthDate = now()->subMonths($m);
            $personelCount = DB::table('personels')
                ->where('company_id', $companyId)
                ->whereMonth('hire_date', '<=', $monthDate->month)
                ->whereYear('hire_date', '<=', $monthDate->year)
                ->count();

            $numCheckIns = rand(max(1, (int)($personelCount * 0.4)), max(2, $personelCount));
            for ($i = 0; $i < $numCheckIns; $i++) {
                $day = $monthDate->copy()->day(rand(1, min(28, $monthDate->daysInMonth)));
                DB::table('time_records')->insert([
                    'company_id'  => $companyId,
                    'personel_id' => $personelIds->random(),
                    'type'        => 'in',
                    'recorded_at' => $day->setHour(rand(8, 10))->setMinute(rand(0, 59)),
                    'source'      => $sources[array_rand($sources)],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            $numLeaves = rand(1, max(2, (int)($personelCount * 0.15)));
            for ($i = 0; $i < $numLeaves; $i++) {
                $day = $monthDate->copy()->day(rand(1, min(28, $monthDate->daysInMonth)));
                DB::table('leave_requests')->insert([
                    'company_id'    => $companyId,
                    'personel_id'   => $personelIds->random(),
                    'leave_type_id' => $leaveTypeIds->random(),
                    'start_date'    => $day->toDateString(),
                    'end_date'      => (clone $day)->addDays(rand(1, 3))->toDateString(),
                    'total_days'    => rand(2, 5),
                    'status'        => 'approved',
                    'created_at'    => $day->subDays(7),
                    'updated_at'    => now(),
                ]);
            }
        }

        // ─── Overtime Requests ───────────────────────────────────────────────
        $otStatuses = ['pending', 'approved', 'approved', 'rejected'];
        // Guarantee today approved overtime
        foreach ($personelIds->take(3) as $pid) {
            $from = Carbon::today()->setHour(18);
            $to = (clone $from)->addHours(rand(2, 4));
            DB::table('overtime_requests')->insert([
                'company_id'  => $companyId,
                'personel_id' => $pid,
                'from'        => $from,
                'to'          => $to,
                'hours'       => $from->diffInMinutes($to) / 60,
                'reason'      => fake('tr_TR')->sentence(6),
                'status'      => 'approved',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
        foreach ($personelIds->take(rand(8, 15)) as $pid) {
            $from = Carbon::today()->setHour(rand(18, 20))->setMinute(0);
            $to = (clone $from)->addHours(rand(1, 4));
            DB::table('overtime_requests')->insert([
                'company_id'  => $companyId,
                'personel_id' => $pid,
                'from'        => $from,
                'to'          => $to,
                'hours'       => $from->diffInMinutes($to) / 60,
                'status'      => $otStatuses[array_rand($otStatuses)],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // ─── Advance Requests ────────────────────────────────────────────────
        $advStatuses = ['pending', 'pending', 'approved', 'approved', 'rejected', 'cancelled', 'repaid'];
        // Guarantee pending advances
        foreach ($personelIds->take(3) as $pid) {
            DB::table('advance_requests')->insert([
                'company_id'  => $companyId,
                'personel_id' => $pid,
                'amount'      => rand(3000, 15000),
                'currency'    => 'TRY',
                'reason'      => fake('tr_TR')->sentence(8),
                'status'      => 'pending',
                'created_at'  => now()->subDays(rand(1, 10)),
                'updated_at'  => now(),
            ]);
        }
        foreach ($personelIds->take(rand(4, 8)) as $pid) {
            DB::table('advance_requests')->insert([
                'company_id'  => $companyId,
                'personel_id' => $pid,
                'amount'      => rand(1000, 20000),
                'currency'    => 'TRY',
                'reason'      => fake('tr_TR')->sentence(8),
                'status'      => $advStatuses[array_rand($advStatuses)],
                'created_at'  => now()->subDays(rand(10, 60)),
                'updated_at'  => now(),
            ]);
        }

        // ─── Expense Categories ──────────────────────────────────────────────
        $expCatNames = ['Yemek', 'Ulaşım', 'Kırtasiye', 'Konaklama', 'Araç Gideri', 'Eğitim', 'Teknoloji', 'Temsil Ağırlama'];
        foreach ($expCatNames as $name) {
            DB::table('expense_categories')->updateOrInsert(
                ['company_id' => $companyId, 'name' => $name],
                ['is_active' => true, 'limit_per_item' => rand(500, 10000), 'requires_receipt' => (bool)rand(0, 1), 'created_at' => now(), 'updated_at' => now()]
            );
        }
        $expCatIds = DB::table('expense_categories')->where('company_id', $companyId)->pluck('id');

        // ─── Expense Requests ─────────────────────────────────────────────────
        $expenseStatuses = ['pending', 'pending', 'approved', 'approved', 'rejected', 'paid', 'cancelled'];
        foreach ($personelIds->take(rand(5, 12)) as $pid) {
            DB::table('expense_requests')->insert([
                'company_id'    => $companyId,
                'personel_id'   => $pid,
                'category_id'   => $expCatIds->random(),
                'amount'        => rand(200, 5000),
                'currency'      => 'TRY',
                'description'   => fake('tr_TR')->sentence(10),
                'expense_date'  => Carbon::today()->subDays(rand(1, 30)),
                'status'        => $expenseStatuses[array_rand($expenseStatuses)],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // ─── Travel Requests ─────────────────────────────────────────────────
        $destinations = ['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya', 'Adana', 'Trabzon', 'Gaziantep'];
        $travelStatuses = ['pending', 'pending', 'approved', 'approved', 'rejected'];
        // Guarantee pending travels
        foreach ($personelIds->take(3) as $pid) {
            $departure = Carbon::today()->addDays(rand(3, 15));
            $return = (clone $departure)->addDays(rand(1, 4));
            DB::table('travel_requests')->insert([
                'company_id'          => $companyId,
                'personel_id'         => $pid,
                'destination'         => $destinations[array_rand($destinations)],
                'departure_date'      => $departure->toDateString(),
                'return_date'         => $return->toDateString(),
                'purpose'             => fake('tr_TR')->sentence(8),
                'transportation_mode' => ['uçak', 'otomobil', 'tren', 'otobüs'][array_rand([0, 1, 2, 3])],
                'estimated_cost'      => rand(2000, 15000),
                'currency'            => 'TRY',
                'status'              => 'pending',
                'created_at'          => now()->subDays(rand(1, 5)),
                'updated_at'          => now(),
            ]);
        }
        // Historical/approved travels
        foreach ($personelIds->take(rand(3, 6)) as $pid) {
            $departure = Carbon::today()->subDays(rand(10, 60));
            $return = (clone $departure)->addDays(rand(1, 5));
            DB::table('travel_requests')->insert([
                'company_id'          => $companyId,
                'personel_id'         => $pid,
                'destination'         => $destinations[array_rand($destinations)],
                'departure_date'      => $departure->toDateString(),
                'return_date'         => $return->toDateString(),
                'purpose'             => fake('tr_TR')->sentence(8),
                'transportation_mode' => ['uçak', 'otomobil', 'tren', 'otobüs'][array_rand([0, 1, 2, 3])],
                'estimated_cost'      => rand(2000, 15000),
                'currency'            => 'TRY',
                'status'              => $travelStatuses[array_rand($travelStatuses)],
                'created_at'          => now()->subDays(rand(10, 60)),
                'updated_at'          => now(),
            ]);
        }

        // ─── Asset Types ─────────────────────────────────────────────────────
        $assetTypeNames = ['Bilgisayar', 'Telefon', 'Masa', 'Sandalye', 'Yazıcı', 'Monitör', 'Tablet', 'Araç'];
        foreach ($assetTypeNames as $name) {
            DB::table('asset_types')->updateOrInsert(
                ['company_id' => $companyId, 'name' => $name],
                ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
        $assetTypeIds = DB::table('asset_types')->where('company_id', $companyId)->pluck('id');

        // ─── Assets ──────────────────────────────────────────────────────────
        $assetStatuses = ['available', 'assigned', 'assigned', 'maintenance', 'retired'];
        $brands = ['Lenovo', 'HP', 'Dell', 'Apple', 'Samsung', 'Xiaomi', 'Huawei', 'Canon'];
        foreach (range(1, 20) as $i) {
            $atId = $assetTypeIds->random();
            $status = $assetStatuses[array_rand($assetStatuses)];
            $assignedTo = $status === 'assigned' ? $personelIds->random() : null;
            DB::table('assets')->insert([
                'company_id'    => $companyId,
                'asset_type_id' => $atId,
                'name'          => $brands[array_rand($brands)] . ' ' . fake('tr_TR')->word() . ' ' . rand(100, 999),
                'serial'        => strtoupper(fake()->bothify('SN-####-????')),
                'purchase_date' => Carbon::today()->subDays(rand(30, 1000))->toDateString(),
                'warranty_end'  => Carbon::today()->addDays(rand(-30, 365))->toDateString(),
                'price'         => rand(500, 50000),
                'status'        => $status,
                'assigned_to'   => $assignedTo,
                'location'      => ['Kat 1', 'Kat 2', 'Kat 3', 'Zemin', 'Depo'][array_rand([0, 1, 2, 3, 4])],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // ─── Visitors ────────────────────────────────────────────────────────
        foreach (range(1, 15) as $i) {
            $host = rand(0, 1) ? $personelIds->random() : null;
            DB::table('visitors')->insert([
                'company_id'      => $companyId,
                'name'            => fake('tr_TR')->name(),
                'visitor_company' => fake('tr_TR')->company(),
                'phone'           => fake('tr_TR')->phoneNumber(),
                'email'           => fake('tr_TR')->email(),
                'host_personel_id' => $host,
                'visit_date'      => Carbon::today()->subDays(rand(1, 60))->setHour(rand(9, 17))->setMinute(rand(0, 59)),
                'checkin_at'      => Carbon::today()->subDays(rand(1, 60))->setHour(rand(9, 17))->setMinute(rand(0, 59)),
                'checkout_at'     => Carbon::today()->subDays(rand(1, 60))->setHour(rand(16, 18))->setMinute(rand(0, 59)),
                'badge_printed'   => (bool)rand(0, 1),
                'purpose'         => fake('tr_TR')->sentence(6),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // ─── Services ────────────────────────────────────────────────────────
        $serviceNames = ['Yemek Hizmeti', 'Temizlik Hizmeti', 'Güvenlik Hizmeti', 'Bakım Hizmeti', 'Danışmanlık', 'Kargo Hizmeti', 'Otopark Hizmeti', 'Teknik Destek'];
        foreach ($serviceNames as $name) {
            DB::table('services')->updateOrInsert(
                ['company_id' => $companyId, 'name' => $name],
                ['description' => fake('tr_TR')->sentence(12), 'is_active' => (bool)rand(0, 1), 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // ─── Vehicles ─────────────────────────────────────────────────────────
        $vehicleBrands = ['Toyota', 'Hyundai', 'Ford', 'Volkswagen', 'Fiat', 'Renault', 'Honda', 'BMW'];
        $vehicleStatuses = ['active', 'active', 'active', 'maintenance', 'out_of_service'];
        foreach (range(1, 8) as $i) {
            DB::table('vehicles')->insert([
                'company_id'            => $companyId,
                'plate'                 => strtoupper(fake()->bothify('## ??? ###')),
                'brand'                 => $vehicleBrands[array_rand($vehicleBrands)],
                'model'                 => fake('tr_TR')->word() . ' ' . rand(2018, 2025),
                'year'                  => rand(2018, 2025),
                'color'                 => ['Beyaz', 'Siyah', 'Gri', 'Mavi', 'Kırmızı'][array_rand([0, 1, 2, 3, 4])],
                'fuel_type'             => ['Benzin', 'Dizel', 'Elektrik', 'Hibrit'][array_rand([0, 1, 2, 3])],
                'acquisition_date'      => Carbon::today()->subDays(rand(100, 1500))->toDateString(),
                'acquisition_cost'      => rand(200000, 2000000),
                'status'                => $vehicleStatuses[array_rand($vehicleStatuses)],
                'assigned_personel_id'  => rand(0, 1) ? $personelIds->random() : null,
                'last_maintenance_date' => Carbon::today()->subDays(rand(10, 200))->toDateString(),
                'next_maintenance_date' => Carbon::today()->addDays(rand(1, 90))->toDateString(),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }

        // ─── Announcements ───────────────────────────────────────────────────
        $announcementTitles = [
            'Yeni Yıl Kutlaması',
            'Şirket Pikniği Duyurusu',
            'Yazılım Güncellemesi',
            'Personel Toplantısı',
            'Yangın Tatbikatı',
            'Doğum Günü Kutlamaları',
            'Yeni Ürün Lansmanı',
            'İftar Yemeği Organizasyonu',
        ];
        foreach ($announcementTitles as $i => $title) {
            DB::table('announcements')->insert([
                'company_id'   => $companyId,
                'title'        => $title,
                'content'      => fake('tr_TR')->paragraph(4),
                'type'         => 'general',
                'visibility'   => 'all',
                'is_pinned'    => $i < 2,
                'is_published' => $i < 6,
                'publish_at'   => now()->subDays(rand(1, 30)),
                'expires_at'   => now()->addDays(rand(10, 60)),
                'created_at'   => now()->subDays(rand(5, 30)),
                'updated_at'   => now(),
            ]);
        }

        // ─── Polls ───────────────────────────────────────────────────────────
        $pollQuestions = [
            'Hangi sosyal etkinliği tercih edersiniz?',
            'Uzaktan çalışma modelini destekliyor musunuz?',
            'Yemek hizmetinden memnun musunuz?',
            'Hangi eğitim konusunu istersiniz?',
            'Ofis saatleri değişikliğini onaylıyor musunuz?',
        ];
        foreach ($pollQuestions as $q) {
            DB::table('polls')->insert([
                'company_id'      => $companyId,
                'question'        => $q,
                'options'         => json_encode(['Evet', 'Hayır', 'Fikrim Yok']),
                'multiple_choice' => false,
                'anonymous'       => (bool)rand(0, 1),
                'ends_at'         => now()->addDays(rand(5, 30)),
                'is_active'       => (bool)rand(0, 1),
                'created_at'      => now()->subDays(rand(3, 15)),
                'updated_at'      => now(),
            ]);
        }

        // ─── Holidays ────────────────────────────────────────────────────────
        $holidays = [
            ['name' => 'Cumhuriyet Bayramı',      'date' => now()->year . '-10-29', 'is_national' => true],
            ['name' => 'Zafer Bayramı',           'date' => now()->year . '-08-30', 'is_national' => true],
            ['name' => 'Emek ve Dayanışma Günü',  'date' => now()->year . '-05-01', 'is_national' => true],
            ['name' => 'Kurban Bayramı (1. Gün)', 'date' => now()->addDays(rand(5, 20))->format('Y-m-d'), 'is_national' => true],
            ['name' => 'Ramazan Bayramı (1. Gün)','date' => now()->subDays(rand(60, 120))->format('Y-m-d'), 'is_national' => true],
            ['name' => 'Yılbaşı Tatili',          'date' => now()->year . '-01-01', 'is_national' => true],
            ['name' => 'Çocuk Bayramı',           'date' => now()->year . '-04-23', 'is_national' => true],
            ['name' => 'Gençlik ve Spor Bayramı', 'date' => now()->year . '-05-19', 'is_national' => true],
            ['name' => 'Demokrasi ve Milli Birlik Günü', 'date' => now()->year . '-07-15', 'is_national' => true],
            ['name' => 'Yaz Ortası Tatili',       'date' => now()->addDays(rand(10, 25))->format('Y-m-d'), 'is_national' => false],
            ['name' => 'Şirket İçi Eğitim Günü',  'date' => now()->addDays(rand(3, 8))->format('Y-m-d'), 'is_national' => false],
        ];
        foreach ($holidays as $h) {
            DB::table('holidays')->updateOrInsert(
                ['company_id' => $companyId, 'name' => $h['name'], 'date' => $h['date']],
                [
                    'company_id'   => $companyId,
                    'country_code' => 'TR',
                    'is_national'  => $h['is_national'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );
        }

        // ─── Process Templates ───────────────────────────────────────────────
        $templates = [
            ['name' => 'İşe Giriş Süreci',     'type' => 'onboarding', 'steps' => json_encode(['Ön kayıt', 'Evrak teslim', 'Oryantasyon', 'Ekipman atama', 'Sistem hesabı']),
                'description' => 'Yeni personel işe giriş süreci adımları'],
            ['name' => 'İşten Ayrılış Süreci', 'type' => 'offboarding', 'steps' => json_encode(['İstifa dilekçesi', 'Çıkış evrakları', 'Zimmet teslimi', 'Veda toplantısı', 'Sistem hesabı kapatma']),
                'description' => 'Personel işten ayrılış süreci adımları'],
            ['name' => 'İzin Süreci',           'type' => 'approval', 'steps' => json_encode(['Başvuru', 'Yönetici onayı', 'İK onayı', 'İzin güncellemesi']),
                'description' => 'İzin talebi onay süreci'],
            ['name' => 'Avans Süreci',          'type' => 'approval', 'steps' => json_encode(['Başvuru', 'Yönetici onayı', 'Finans onayı', 'Ödeme']),
                'description' => 'Avans talebi onay ve ödeme süreci'],
        ];
        $templateIds = [];
        foreach ($templates as $t) {
            $id = DB::table('process_templates')->insertGetId([
                'company_id'   => $companyId,
                'name'         => $t['name'],
                'type'         => $t['type'],
                'description'  => $t['description'],
                'steps'        => $t['steps'],
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            $templateIds[] = $id;
        }

        // ─── Process Instances ────────────────────────────────────────────────
        $procStatuses = ['pending', 'in_progress', 'in_progress', 'completed', 'completed', 'completed', 'cancelled'];
        foreach ($personelIds->take(rand(5, 10)) as $pid) {
            DB::table('process_instances')->insert([
                'template_id'           => $templateIds[array_rand($templateIds)],
                'personel_id'           => $pid,
                'company_id'            => $companyId,
                'status'                => $procStatuses[array_rand($procStatuses)],
                'context'               => json_encode(['initiated_by' => $pid]),
                'completed_steps'       => json_encode([]),
                'created_at'            => now()->subDays(rand(1, 30)),
                'updated_at'            => now(),
            ]);
        }

        // ─── Shift Plans & Shifts ────────────────────────────────────────────
        $shiftIds = [];
        $shiftNames = ['Sabah', 'Öğle', 'Akşam', 'Gece'];
        foreach ($shiftNames as $i => $sn) {
            $id = DB::table('shifts')->insertGetId([
                'company_id'   => $companyId,
                'name'         => $sn . ' Vardiyası',
                'start_time'   => sprintf('%02d:00:00', [7, 13, 19, 23][$i]),
                'end_time'     => sprintf('%02d:00:00', [15, 21, 3, 7][$i]),
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            $shiftIds[] = $id;
        }

        $shiftPlanId = DB::table('shift_plans')->insertGetId([
            'company_id' => $companyId,
            'name'       => 'Haftalık Standart Plan',
            'pattern'    => json_encode(['weekly']),
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // bu haftaki vardiya atamaları
        $weekStart = now()->startOfWeek();
        foreach ($personelIds->take(rand(15, 25)) as $pid) {
            for ($d = 0; $d < 5; $d++) {
                if (rand(0, 1)) continue;
                DB::table('shift_assignments')->insert([
                    'shift_plan_id' => $shiftPlanId,
                    'shift_id'      => $shiftIds[array_rand($shiftIds)],
                    'personel_id'   => $pid,
                    'date'          => (clone $weekStart)->addDays($d)->toDateString(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        // ─── Audit Logs (son aktiviteler) ─────────────────────────────────────
        $adminUserId = DB::table('users')->where('company_id', $companyId)->value('id') ?? 1;
        $actions = ['created', 'updated', 'deleted', 'approved', 'rejected', 'logged_in', 'exported'];
        $models = ['Personel', 'LeaveRequest', 'AdvanceRequest', 'ExpenseRequest', 'Asset', 'Visitor', 'Vehicle', 'User'];
        foreach (range(1, 30) as $i) {
            DB::table('audit_logs')->insert([
                'company_id'  => $companyId,
                'action'      => $actions[array_rand($actions)],
                'model_type'  => 'App\Models\\' . $models[array_rand($models)],
                'model_id'    => rand(1, 50),
                'user_id'     => $adminUserId,
                'ip'          => fake()->ipv4(),
                'user_agent'  => fake()->userAgent(),
                'created_at'  => now()->subMinutes(rand(1, 1440)),
            ]);
        }

        // ─── Personel Documents ───────────────────────────────────────────────
        $docTypeNames = ['Özgeçmiş', 'Kimlik Fotokopisi', 'İş Sözleşmesi', 'SGK Bildirgesi', 'Sağlık Raporu', 'Eğitim Belgesi', 'Referans Mektubu'];
        $docExts = ['pdf', 'docx', 'jpg'];
        $now = Carbon::now();
        foreach ($personelIds as $pid) {
            $numDocs = rand(2, 4);
            $usedTypes = [];
            for ($di = 0; $di < $numDocs; $di++) {
                $t = $docTypeNames[array_rand($docTypeNames)];
                if (in_array($t, $usedTypes)) continue;
                $usedTypes[] = $t;
                $ext = $docExts[array_rand($docExts)];
                $expiry = rand(0, 1) ? $now->copy()->addDays(rand(-30, 365)) : null;
                DB::table('personel_documents')->insert([
                    'personel_id' => $pid,
                    'type' => $t,
                    'file_path' => 'uploads/personel/' . $pid . '/' . strtolower(str_replace([' ','İ','ı','Ş','ş','Ç','ç','Ü','ü','Ö','ö','Ğ','ğ'], ['_','i','i','s','s','c','c','u','u','o','o','g','g'], $t)) . '.' . $ext,
                    'original_name' => $t . '.' . $ext,
                    'mime' => $ext === 'pdf' ? 'application/pdf' : ($ext === 'docx' ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : 'image/jpeg'),
                    'file_size' => rand(100000, 2000000),
                    'expiry_at' => $expiry,
                    'created_by' => $adminUserId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // More personel-specific audit logs for card view activity tab
        $personelActions = ['created', 'updated', 'updated', 'created', 'approved', 'rejected'];
        foreach ($personelIds as $pid) {
            $numLogs = rand(1, 3);
            for ($li = 0; $li < $numLogs; $li++) {
                DB::table('audit_logs')->insert([
                    'user_id' => $adminUserId,
                    'company_id' => $companyId,
                    'action' => $personelActions[array_rand($personelActions)],
                    'model_type' => 'App\Modules\Personel\Models\Personel',
                    'model_id' => $pid,
                    'ip' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'created_at' => $now->copy()->subHours(rand(1, 72)),
                ]);
            }
        }

        // ─── Other companies' data too ────────────────────────────────────────
        $otherCompanyIds = DB::table('companies')->where('id', '!=', $companyId)->pluck('id');
        foreach ($otherCompanyIds as $ocId) {
            $ocPersonelIds = DB::table('personels')->where('company_id', $ocId)->pluck('id');
            $ocDeptIds = DB::table('departments')->where('company_id', $ocId)->pluck('id');

            // Leave types
            foreach ($leaveTypes as $lt) {
                DB::table('leave_types')->updateOrInsert(
                    ['company_id' => $ocId, 'name' => $lt['name']],
                    array_merge($lt, ['company_id' => $ocId, 'name' => $lt['name'], 'is_active' => true, 'created_at' => now(), 'updated_at' => now()])
                );
            }
            $ocLtIds = DB::table('leave_types')->where('company_id', $ocId)->pluck('id');

            // Some leave requests for other companies too
            foreach ($ocPersonelIds->take(rand(3, 5)) as $pid) {
                $start = Carbon::today()->subDays(rand(1, 30));
                $end = (clone $start)->addDays(rand(1, 3));
                DB::table('leave_requests')->insert([
                    'company_id'    => $ocId,
                    'personel_id'   => $pid,
                    'leave_type_id' => $ocLtIds->random(),
                    'start_date'    => $start->toDateString(),
                    'end_date'      => $end->toDateString(),
                    'total_days'    => $start->diffInDays($end) + 1,
                    'status'        => $statuses[array_rand($statuses)],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // Some time records
            foreach ($ocPersonelIds->take(rand(3, 8)) as $pid) {
                DB::table('time_records')->insert([
                    'company_id'  => $ocId,
                    'personel_id' => $pid,
                    'type'        => 'in',
                    'recorded_at' => Carbon::today()->setHour(rand(8, 10))->setMinute(rand(0, 59)),
                    'source'      => 'web',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // Expense categories
            foreach ($expCatNames as $name) {
                DB::table('expense_categories')->updateOrInsert(
                    ['company_id' => $ocId, 'name' => $name],
                    ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            // A few assets
            foreach (range(1, rand(3, 6)) as $i) {
                DB::table('assets')->insert([
                    'company_id'    => $ocId,
                    'asset_type_id' => $assetTypeIds->first() ?? 1,
                    'name'          => fake('tr_TR')->word() . ' ' . rand(100, 999),
                    'serial'        => strtoupper(fake()->bothify('SN-####-????')),
                    'status'        => 'available',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // Visitors
            foreach (range(1, rand(2, 5)) as $i) {
                DB::table('visitors')->insert([
                    'company_id'  => $ocId,
                    'name'        => fake('tr_TR')->name(),
                    'visit_date'  => Carbon::today()->subDays(rand(1, 30))->setHour(rand(9, 17)),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        $this->command->info('Demo veriler başarıyla eklendi!');
    }
}
