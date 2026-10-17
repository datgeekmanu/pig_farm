<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Concerns\BelongsToFarm;
use App\Models\Farm;
use App\Models\Scopes\FarmScope;
use App\Support\CurrentFarm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

// Minimal tenant-owned fixture model, backed by a test-only table.
class Widget extends Model
{
    use BelongsToFarm;

    protected $guarded = [];
}

class FarmScopeTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farmA;

    private Farm $farmB;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id');
            $table->string('name');
            $table->timestamps();
        });

        $account = Account::create(['name' => 'Acme']);
        $this->farmA = Farm::create(['account_id' => $account->id, 'name' => 'Farm A']);
        $this->farmB = Farm::create(['account_id' => $account->id, 'name' => 'Farm B']);
    }

    private function useFarm(?int $id): void
    {
        app(CurrentFarm::class)->set($id);
    }

    public function test_create_autostamps_current_farm_id(): void
    {
        $this->useFarm($this->farmA->id);
        $w = Widget::create(['name' => 'w1']);

        $this->assertSame($this->farmA->id, $w->farm_id);
    }

    public function test_scope_isolates_by_current_farm_and_switches(): void
    {
        $this->useFarm($this->farmA->id);
        Widget::create(['name' => 'a']);

        $this->useFarm($this->farmB->id);
        Widget::create(['name' => 'b']);

        $this->useFarm($this->farmA->id);
        $this->assertSame(1, Widget::count());
        $this->assertSame('a', Widget::first()->name);

        $this->useFarm($this->farmB->id);
        $this->assertSame(1, Widget::count());
        $this->assertSame('b', Widget::first()->name);
    }

    public function test_query_without_active_farm_throws(): void
    {
        $this->useFarm(null);

        $this->expectException(RuntimeException::class);
        Widget::count();
    }

    public function test_without_farm_scope_sees_all_farms(): void
    {
        $this->useFarm($this->farmA->id);
        Widget::create(['name' => 'a']);
        $this->useFarm($this->farmB->id);
        Widget::create(['name' => 'b']);

        $this->useFarm(null);
        $this->assertSame(2, Widget::withoutGlobalScope(FarmScope::class)->count());
    }
}
