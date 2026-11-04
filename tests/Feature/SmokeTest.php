<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Membuka setiap halaman GET tanpa parameter sebagai admin dan memastikan tidak ada error 500.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private const SKIP = ['logout', 'splash', 'stock.scan-qr'];

    public static function roles(): array
    {
        return [['admin'], ['pimpinan'], ['staff'], ['purchasing'], ['kasir']];
    }

    /** @dataProvider roles */
    public function test_all_parameterless_pages_render_for_role(string $role): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => $role, 'is_active' => true]);

        $failures = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }
            if (str_contains($route->uri(), '{') || ! in_array('auth', $route->gatherMiddleware(), true)) {
                continue;
            }
            if (in_array($route->getName(), self::SKIP, true)) {
                continue;
            }

            $response = $this->actingAs($admin)->get('/' . ltrim($route->uri(), '/'));

            if ($response->getStatusCode() >= 500) {
                $message = $response->exception?->getMessage() ?? 'HTTP ' . $response->getStatusCode();
                $failures[] = $route->uri() . ' => ' . mb_substr($message, 0, 300);
            }
        }

        $this->assertSame([], $failures, "Halaman error:\n" . implode("\n", $failures));
    }

    public function test_global_search_renders(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get('/search?q=semen')->assertOk();
    }
}
