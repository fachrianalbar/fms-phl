<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndAuthTest extends TestCase
{
    protected User $adminUser;

    protected User $nonAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createMinimalSchema();
        $this->seedUsersAndRoles();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('company_setting');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('role_menu');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('role');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_login_on_protected_routes(): void
    {
        $protectedGetRoutes = [
            '/home',
            '/change-password',
            '/logs',
            '/administrator/user',
        ];

        foreach ($protectedGetRoutes as $uri) {
            $response = $this->get($uri);
            $response->assertStatus(302);
            $response->assertRedirect('/');
        }
    }

    public function test_non_admin_user_is_forbidden_from_logs_and_admin_routes(): void
    {
        $this->actingAs($this->nonAdminUser);

        $this->get('/logs')->assertForbidden();
        $this->get('/administrator/user')->assertForbidden();
        $this->get('/administrator/company-setting')->assertForbidden();
        $this->post('/administrator/company-setting', ['name' => 'Unauthorized'])->assertForbidden();
        $this->get('/bank/transfer-fund')->assertForbidden();
        $this->get('/report/profit-loss')->assertForbidden();
    }

    public function test_super_admin_can_access_logs_and_admin_routes(): void
    {
        $this->actingAs($this->adminUser);

        $logsResponse = $this->get('/logs');
        $this->assertNotSame(403, $logsResponse->getStatusCode());
        $this->assertNotSame(500, $logsResponse->getStatusCode());
        $logsResponse->assertOk();

        $userIndexResponse = $this->get('/administrator/user');
        $this->assertNotSame(403, $userIndexResponse->getStatusCode());
        $this->assertNotSame(500, $userIndexResponse->getStatusCode());
        $userIndexResponse->assertOk();

        $this->get('/change-password')->assertOk();
    }

    public function test_dangerous_file_uploads_are_rejected_in_company_setting(): void
    {
        Storage::fake('local');
        $this->actingAs($this->adminUser);

        $existing = CompanySetting::create([
            'code' => 'FMSCS-TEST-1',
            'name' => 'PT Existing',
        ]);

        $dangerousFiles = [
            ['shell.php', 'application/x-php'],
            ['xss.svg', 'image/svg+xml'],
            ['payload.exe', 'application/x-msdownload'],
        ];

        foreach ($dangerousFiles as [$filename, $mime]) {
            $storeResponse = $this->post(route('administrator.company-setting.store'), [
                'name' => 'PT Test Upload',
                'logo' => UploadedFile::fake()->create($filename, 10, $mime),
            ]);

            $storeResponse->assertRedirect(route('administrator.company-setting.index'));
            $storeResponse->assertSessionHas('fail');

            $updateResponse = $this->put(route('administrator.company-setting.update', $existing->id), [
                'name' => 'PT Existing',
                'logo' => UploadedFile::fake()->create($filename, 10, $mime),
            ]);

            $updateResponse->assertRedirect(route('administrator.company-setting.index'));
            $updateResponse->assertSessionHas('fail');
        }

        $this->assertSame(1, CompanySetting::count());
        $this->assertSame([], Storage::disk('local')->allFiles('public/company_setting/logo'));
    }

    public function test_valid_image_upload_succeeds_in_company_setting(): void
    {
        Storage::fake('local');
        $this->actingAs($this->adminUser);

        $response = $this->post(route('administrator.company-setting.store'), [
            'name' => 'PT Aman Sentosa',
            'logo' => UploadedFile::fake()->image('logo.png', 100, 100),
        ]);

        $response->assertRedirect(route('administrator.company-setting.index'));
        $response->assertSessionHas('success');

        $setting = CompanySetting::first();
        $this->assertNotNull($setting);
        $this->assertNotNull($setting->logo);
        $this->assertStringEndsWith('.png', $setting->logo);
        Storage::disk('local')->assertExists('public/company_setting/logo/'.$setting->logo);
    }

    public function test_dangerous_file_uploads_are_rejected_in_fleet_controller(): void
    {
        Storage::fake('local');
        $this->actingAs($this->adminUser);

        $dangerousFiles = [
            ['shell.php', 'application/x-php'],
            ['xss.svg', 'image/svg+xml'],
            ['payload.exe', 'application/x-msdownload'],
        ];

        foreach ($dangerousFiles as [$filename, $mime]) {
            // 1. barcode
            $this->post(route('master.fleets.store'), [
                'plateNumber' => 'B 1234 TST',
                'barcode' => UploadedFile::fake()->create($filename, 10, $mime),
            ])->assertRedirect(route('master.fleets.index'))
                ->assertSessionHas('fail');

            // 2. vehicleRegistrationNumber
            $this->post(route('master.fleets.store'), [
                'plateNumber' => 'B 1234 TST',
                'vehicleRegistrationNumber' => UploadedFile::fake()->create($filename, 10, $mime),
            ])->assertRedirect(route('master.fleets.index'))
                ->assertSessionHas('fail');

            // 3. fleetPicture (array upload on store)
            $this->post(route('master.fleets.store'), [
                'plateNumber' => 'B 1234 TST',
                'fleetPicture' => [
                    UploadedFile::fake()->create($filename, 10, $mime),
                ],
            ])->assertRedirect(route('master.fleets.index'))
                ->assertSessionHas('fail');

            // 4. newFleetPicture (array upload on update)
            $this->put(route('master.fleets.update', 'dummy-fleet-id'), [
                'plateNumber' => 'B 1234 TST',
                'newFleetPicture' => [
                    UploadedFile::fake()->create($filename, 10, $mime),
                ],
            ])->assertRedirect(route('master.fleets.index'))
                ->assertSessionHas('fail');
        }

        $this->assertSame([], Storage::disk('local')->allFiles('public/fleet'));
    }

    private function createMinimalSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('roleCode')->nullable();
            $table->string('languange')->default('id');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('role', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

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

        Schema::create('role_menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('roleCode');
            $table->string('menuCode');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('object');
            $table->string('value')->nullable();
            $table->timestamps();
        });

        Schema::create('company_setting', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('owner')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('event')->nullable();
            $table->string('causer_type')->nullable();
            $table->string('causer_id')->nullable();
            $table->text('properties')->nullable();
            $table->string('batch_uuid')->nullable();
            $table->timestamps();
        });
    }

    private function seedUsersAndRoles(): void
    {
        Role::create([
            'code' => 'SPRADMIN',
            'name' => 'System Administrator',
        ]);

        Role::create([
            'code' => 'DO',
            'name' => 'Operational',
        ]);

        $this->adminUser = User::create([
            'code' => 'USR-SPRADMIN',
            'name' => 'Super Admin',
            'username' => 'superadmin_test',
            'password' => bcrypt('password'),
            'roleCode' => 'SPRADMIN',
            'languange' => 'id',
        ]);

        $this->nonAdminUser = User::create([
            'code' => 'USR-DO',
            'name' => 'Operator DO',
            'username' => 'operator_test',
            'password' => bcrypt('password'),
            'roleCode' => 'DO',
            'languange' => 'id',
        ]);
    }
}
