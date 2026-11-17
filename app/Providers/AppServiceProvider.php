<?php

namespace App\Providers;

use App\Models\ClassSection;
use App\Models\KrsSubmission;
use App\Models\StudentInvoice;
use App\Models\Thesis;
use App\Policies\GradeSheetPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\KrsPolicy;
use App\Policies\ThesisPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider
 *
 * Configures enterprise database integrity, strict N+1 query prevention,
 * authorization policies, and fine-grained production rate limiters.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // 1. Strict N+1 Query Elimination in Development/Local
        Model::preventLazyLoading(!$this->app->isProduction());

        // 2. Prevent Silently Discarding Fillable Attributes
        Model::preventSilentlyDiscardingAttributes(!$this->app->isProduction());

        // 3. Prohibit Destructive Migrations in Production
        if ($this->app->isProduction()) {
            DB::prohibitDestructiveCommands();
        }

        // 4. Configure strict pagination defaults
        \Illuminate\Pagination\Paginator::useTailwind();

        // 5. Explicit Policy Bindings & Dynamic Granular Gate Definitions
        Gate::policy(KrsSubmission::class, KrsPolicy::class);
        Gate::policy(StudentInvoice::class, InvoicePolicy::class);
        Gate::policy(ClassSection::class, GradeSheetPolicy::class);
        Gate::policy(Thesis::class, ThesisPolicy::class);

        Gate::before(function ($user, $ability) {
            if ($user && $user->role === 'superadmin') {
                return true;
            }
            if ($user && method_exists($user, 'hasPrivilege') && $user->hasPrivilege($ability)) {
                return true;
            }
            return null;
        });

        // 6. Strict Enterprise Rate Limiters (Stage 10 Requirement 1.C)
        // Login: 5 attempts per minute per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // CBT Exam Submission: 10 per minute per user or IP
        RateLimiter::for('cbt_submit', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // QR Attendance Scan: 6 per minute to prevent token brute-forcing
        RateLimiter::for('attendance_scan', function (Request $request) {
            return Limit::perMinute(6)->by($request->user()?->id ?: $request->ip());
        });

        // KRS War Cart Submission & Operations: 20 per minute
        RateLimiter::for('krs_cart', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
