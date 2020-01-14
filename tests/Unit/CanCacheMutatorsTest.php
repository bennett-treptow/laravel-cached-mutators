<?php
namespace CachedMutators\Tests\Unit;

use CachedMutators\Tests\Models\TestModel;

class CanCacheMutatorsTest extends \Orchestra\Testbench\TestCase {
    public function test_registers_mutators(){
        $model = new TestModel([
            'attribute' => 'test'
        ]);
        $this->assertEquals('test', $model->attribute);
    }
    public function test_random_attribute(){
        $model = new TestModel();
        $firstCall = $model->random;
        $secondCall = $model->random;

        $this->assertEquals($firstCall, $secondCall);

        $model->clearCachedMutators();
        $this->assertNotEquals($firstCall, $model->random);
    }

    public function test_timed_attribute()
    {
        $model = new TestModel();
        $startTime = microtime(true);
        $value = $model->timed;
        $endTime = microtime(true);
        $this->assertEquals(1.0, round($endTime - $startTime));
        $startTime = microtime(true);
        $value = $model->timed;
        $endTime = microtime(true);
        $this->assertEquals(0.0, round($endTime - $startTime));
    }

    public function test_different_keys_result_in_different_values(){
        $model = new TestModel(['id' => 1, 'test' => 'test1']);
        $model2 = new TestModel(['id' => 2, 'test' => 'test2']);

        $this->assertNotSame($model->getAttributeCacheKeyName('random'), $model2->getAttributeCacheKeyName('random'));
        $this->assertNotSame($model->test, $model2->test);
    }
}