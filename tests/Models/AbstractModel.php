<?php

namespace CachedMutators\Tests\Models;

use CachedMutators\HasCachedMutators;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractModel extends Model {
    use HasCachedMutators;
    protected $cacheAttributes = [
        'random'
    ];
    public function getRandomAttribute(){
        return mt_rand(0, 10000);
    }
}