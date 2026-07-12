<?php

namespace Tests\Feature;

use App\Services\UniqueCodeService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UniqueCodeServiceTest extends TestCase
{
    protected UniqueCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('unique_code_test_records', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->string('code')->unique();
            $table->softDeletes();
            $table->timestamps();
        });

        $this->service = app(UniqueCodeService::class);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('unique_code_test_records');

        parent::tearDown();
    }

    public function test_manual_code_that_has_never_been_used_is_kept(): void
    {
        $result = $this->service->resolve(UniqueCodeTestRecord::class, 'code', 'BRG-0010');

        $this->assertFalse($result->wasChanged);
        $this->assertSame('BRG-0010', $result->resolvedCode);
    }

    public function test_active_and_soft_deleted_codes_are_skipped(): void
    {
        UniqueCodeTestRecord::create(['code' => 'BRG-0010']);
        UniqueCodeTestRecord::create(['code' => 'BRG-0011']);
        UniqueCodeTestRecord::create(['code' => 'BRG-0012'])->delete();
        UniqueCodeTestRecord::create(['code' => 'BRG-0013']);

        $result = $this->service->resolve(UniqueCodeTestRecord::class, 'code', 'BRG-0010');

        $this->assertTrue($result->wasChanged);
        $this->assertSame('BRG-0014', $result->resolvedCode);
    }

    public function test_update_can_keep_current_code_but_changes_to_used_code_are_resolved(): void
    {
        $current = UniqueCodeTestRecord::create(['code' => 'BRG-0099']);
        UniqueCodeTestRecord::create(['code' => 'BRG-0100']);

        $same = $this->service->resolve(UniqueCodeTestRecord::class, 'code', 'BRG-0099', ignoreId: (string) $current->id);
        $changed = $this->service->resolve(UniqueCodeTestRecord::class, 'code', 'BRG-0100', ignoreId: (string) $current->id);

        $this->assertFalse($same->wasChanged);
        $this->assertSame('BRG-0099', $same->resolvedCode);
        $this->assertTrue($changed->wasChanged);
        $this->assertSame('BRG-0101', $changed->resolvedCode);
    }

    public function test_code_without_trailing_number_uses_configurable_fallback(): void
    {
        UniqueCodeTestRecord::create(['code' => 'CUSTOM']);
        UniqueCodeTestRecord::create(['code' => 'CUSTOM-001']);

        $result = $this->service->resolve(UniqueCodeTestRecord::class, 'code', 'CUSTOM');

        $this->assertSame('CUSTOM-002', $result->resolvedCode);
    }

    public function test_scope_limits_uniqueness_lookup(): void
    {
        UniqueCodeTestRecord::create(['company_id' => 'A', 'code' => 'CP-001']);
        UniqueCodeTestRecord::create(['company_id' => 'A', 'code' => 'CP-002']);
        UniqueCodeTestRecord::create(['company_id' => 'B', 'code' => 'CP-010']);

        $result = $this->service->resolve(
            model: UniqueCodeTestRecord::class,
            field: 'code',
            requestedCode: 'CP-001',
            scope: fn ($query) => $query->where('company_id', 'A'),
        );

        $this->assertSame('CP-003', $result->resolvedCode);
    }

    public function test_duplicate_key_retry_only_retries_duplicate_errors(): void
    {
        UniqueCodeTestRecord::create(['code' => 'DUP-001']);
        $attempts = 0;

        $result = $this->service->runWithDuplicateRetry(function () use (&$attempts) {
            $attempts++;

            if ($attempts === 1) {
                UniqueCodeTestRecord::create(['code' => 'DUP-001']);
            }

            return 'retried';
        });

        $this->assertSame('retried', $result);
        $this->assertSame(2, $attempts);
    }

    public function test_database_unique_index_still_rejects_direct_duplicate_insert(): void
    {
        UniqueCodeTestRecord::create(['code' => 'DB-001']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        UniqueCodeTestRecord::create(['code' => 'DB-001']);
    }
}

class UniqueCodeTestRecord extends Model
{
    use SoftDeletes;

    protected $table = 'unique_code_test_records';

    protected $guarded = [];
}
