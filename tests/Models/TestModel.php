<?php
namespace CachedMutators\Tests\Models;

use CachedMutators\HasCachedMutators;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Cache;

class TestModel extends Model {
    use HasCachedMutators;

    protected $guarded = [];

    protected static $cacheAttributes = [
        'test',
        'random' => ['store' => 'array', 'ttl' => 1],
        'another_random',
        'timed'
    ];

    public function getTestAttribute(){
        //some expensive operation
        return $this->attributes['test'];
    }

    public function getRandomAttribute(){
        return mt_rand(0, 1000);
    }

    public function getAnotherRandomAttribute(){
        return mt_rand(1001, 5000);
    }

    public function getTimedAttribute(){
        sleep(1);
        return 'test';
    }
}