<?php
namespace CachedMutators\Tests\Unit;

use CachedMutators\Tests\Models\TestModelWithDefaults;
use Orchestra\Testbench\TestCase;

class CanUseDefaultConfigTest extends TestCase {
    public function test_default_store(){
        $model = new TestModelWithDefaults([
            'id' => 1
        ]);
        $this->assertEquals('array', $model::defaultCacheStore());
    }
    public function test_default_ttl(){
        $model = new TestModelWithDefaults([
            'id' => 1
        ]);
        $this->assertEquals(60, $model::defaultCacheTTL());
    }
    public function test_override(){
        $model = new TestModelWithDefaults([
            'id' => 1
        ]);
        $this->assertEquals(600, $model->getCacheConfig()['slow_value']['ttl']);
    }
}