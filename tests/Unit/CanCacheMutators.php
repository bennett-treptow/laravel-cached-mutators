<?php
namespace Tests\Unit;

use Tests\Models\TestModel;

class CanCacheMutators extends \Orchestra\Testbench\TestCase {
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

        $model->clearMutationCache();
        $this->assertNotEquals($firstCall, $model->random);
    }

    public function test_timed_attribute(){
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
}