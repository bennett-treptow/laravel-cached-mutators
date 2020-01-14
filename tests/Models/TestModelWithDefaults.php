<?php
namespace CachedMutators\Tests\Models;

use CachedMutators\HasCachedMutators;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestModelWithDefaults extends Model {
    use HasCachedMutators;
    protected $guarded = [];

    public static function defaultCacheStore(){
        return 'array';
    }
    public static function defaultCacheTTL(){
        return 60;
    }

    protected static $cacheAttributes = [
        'expensive_value',
        'slow_value' => ['ttl' => 600]
    ];

    public function getExpensiveValueAttribute(){
        return bcrypt(Str::random(60));
    }
    public function getSlowValueAttribute(){
        return Hash::make(Str::random(50));
    }
}