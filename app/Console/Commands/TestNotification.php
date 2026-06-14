<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\LeaveRequestNotification;
use App\Notifications\ExpenseRequestNotification;
use App\Notifications\AdvanceRequestNotification;
use App\Notifications\TravelRequestNotification;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    protected $signature = 'test:notification {user? : User ID} {type? : Notification type (leave/expense/advance/travel)}';
    protected $description = 'Send a test notification to verify real-time broadcasting';

    public function handle(): int
    {
        $userId = $this->argument('user') ?? $this->ask('User ID');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User #{$userId} not found.");
            return 1;
        }

        $validTypes = ['leave', 'expense', 'advance', 'travel'];
        $type = $this->argument('type') ?? $this->choice('Notification type', $validTypes, 0);

        $notification = match ($type) {
            'leave' => new LeaveRequestNotification(
                leaveRequestId: 1,
                personelName: 'Test Personel',
                leaveTypeName: 'Yıllık İzin',
                totalDays: 5.0,
                startDate: '2026-06-01',
                endDate: '2026-06-05',
                requestedBy: $user->name ?? 'Admin'
            ),
            'expense' => new ExpenseRequestNotification(
                expenseId: 1,
                personelName: 'Test Personel',
                amount: 1500.00,
                currency: 'TRY',
                categoryName: 'Yol Masrafı',
                expenseDate: '2026-06-01'
            ),
            'advance' => new AdvanceRequestNotification(
                advanceId: 1,
                personelName: 'Test Personel',
                amount: 5000.00,
                currency: 'TRY',
                reason: 'Test avans talebi'
            ),
            'travel' => new TravelRequestNotification(
                travelId: 1,
                personelName: 'Test Personel',
                destination: 'İstanbul',
                departure: '2026-06-10',
                returnDate: '2026-06-12'
            ),
        };

        $user->notify($notification);

        $this->info("✅ Test notification sent to User #{$userId}!");
        $this->warn("Check the browser console for Reverb/Echo activity.");
        $this->line("Run 'php artisan reverb:start' if the server is not running.");

        return 0;
    }
}
