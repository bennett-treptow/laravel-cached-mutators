<?php
namespace CacheMutators\Tests\Unit;

use CachedMutators\Tests\Models\TestModel;
use Orchestra\Testbench\TestCase;

class CanClearCacheMutatorsTest extends TestCase {
    public function test_can_clear_cache_mutators_by_key(){
        $testModel = new TestModel(['id' => 1]);
        $randomValue = $testModel->random;
        $this->assertEquals($randomValue, $testModel->random);

        $testModel->clearCachedMutators('random');
        $this->assertNotEquals($randomValue, $testModel->random);
    }
    public function test_can_clear_all_cache_mutators(){
        $testModel = new TestModel(['id' => 1]);
        $randomValue = $testModel->random;
        $anotherRandomValue = $testModel->another_random;
        $this->assertEquals($randomValue, $testModel->random);
        $this->assertEquals($anotherRandomValue, $testModel->another_random);

        $testModel->clearCachedMutators();
        $this->assertNotEquals($randomValue, $testModel->random);
        $this->assertNotEquals($anotherRandomValue, $testModel->another_random);
    }
}