<?php

namespace App\Providers;

use App\Support\Time;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // @time12($timeString) — formats "HH:MM" as Arabic 12-hour ("8:00 ص" / "11:59 م")
        Blade::directive('time12', function (string $expression): string {
            return "<?php echo e(\\App\\Support\\Time::format12({$expression})); ?>";
        });
    }
}
