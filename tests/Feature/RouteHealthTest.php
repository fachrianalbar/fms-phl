<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RouteHealthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('nama')->nullable();
            $table->string('parentCode')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('menu');

        parent::tearDown();
    }

    public function test_all_controller_routes_reference_existing_methods_and_instantiate_without_auth(): void
    {
        $brokenRoutes = [];
        $controllerClasses = [];

        foreach (Route::getRoutes() as $route) {
            $uses = $route->getAction('uses');
            if ($uses instanceof \Closure) {
                continue;
            }

            $controllerAction = $route->getAction('controller');
            if (! is_string($controllerAction)) {
                continue;
            }

            [$class, $method] = str_contains($controllerAction, '@')
                ? explode('@', $controllerAction, 2)
                : [$controllerAction, '__invoke'];

            if (! class_exists($class) || ! method_exists($class, $method)) {
                $brokenRoutes[] = sprintf(
                    '%s /%s -> %s',
                    implode('|', $route->methods()),
                    ltrim($route->uri(), '/'),
                    $controllerAction
                );

                continue;
            }

            if (str_starts_with($class, 'App\\Http\\Controllers\\')) {
                $controllerClasses[$class] = true;
            }
        }

        $this->assertSame([], $brokenRoutes, 'Broken routes found: '.implode('; ', $brokenRoutes));
        $this->assertNotEmpty($controllerClasses);

        Auth::logout();
        $this->assertNull(Auth::user());

        foreach (array_keys($controllerClasses) as $controllerClass) {
            $instance = app()->make($controllerClass);
            $this->assertInstanceOf($controllerClass, $instance);
        }
    }

    public function test_no_non_whitelisted_routes_lack_auth_or_guest_middleware(): void
    {
        $whitelist = [
            '/',
            '/authenticate',
            '/logout',
            '/maintenance',
            '/up',
        ];

        $unprotectedRoutes = [];

        foreach (Route::getRoutes() as $route) {
            $uri = '/'.ltrim($route->uri(), '/');

            if (str_starts_with($uri, '/_debugbar')) {
                continue;
            }

            $middlewares = $route->gatherMiddleware();
            $hasAuthOrGuest = false;

            foreach ($middlewares as $middleware) {
                if (! is_string($middleware)) {
                    continue;
                }

                if (
                    $middleware === 'auth'
                    || str_starts_with($middleware, 'auth:')
                    || $middleware === 'guest'
                    || str_starts_with($middleware, 'guest:')
                    || $middleware === \Illuminate\Auth\Middleware\Authenticate::class
                    || $middleware === \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class
                ) {
                    $hasAuthOrGuest = true;
                    break;
                }
            }

            if (! $hasAuthOrGuest && ! in_array($uri, $whitelist, true)) {
                $unprotectedRoutes[] = sprintf(
                    '%s %s [%s]',
                    implode('|', $route->methods()),
                    $uri,
                    implode(', ', array_filter($middlewares, 'is_string'))
                );
            }
        }

        $this->assertSame(
            [],
            $unprotectedRoutes,
            'Non-whitelisted routes missing auth/guest middleware: '.implode('; ', $unprotectedRoutes)
        );
    }
}
