<?php

namespace App\Console\Commands;

use App\Mail\NotificationDigest;
use App\Models\Farm;
use App\Support\CurrentFarm;
use App\Support\NotificationGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GenerateNotifications extends Command
{
    protected $signature = 'notifications:generate';

    protected $description = 'Generate farm alerts (low stock, farrowings, rappels, weanings, heat returns)';

    public function handle(): int
    {
        $total = 0;

        foreach (Farm::all() as $farm) {
            app(CurrentFarm::class)->set($farm->id);

            $new = NotificationGenerator::forCurrentFarm();
            $total += $new->count();

            if ($new->isNotEmpty() && ($farm->notification_prefs['email'] ?? true) !== false) {
                $owners = $farm->users()->wherePivot('role', 'owner')->pluck('email')->all();
                if ($owners) {
                    Mail::to($owners)->send(new NotificationDigest($farm, $new));
                }
            }
        }

        $this->info("Generated {$total} notification(s).");

        return self::SUCCESS;
    }
}
