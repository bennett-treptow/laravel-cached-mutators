<?php
namespace CachedMutators;

use Illuminate\Support\Facades\Cache;

trait HasCachedMutators {
    private static $cacheConfig = [];

    public static function bootHasCachedMutators(){
        $conf = static::$cacheAttributes;
        foreach($conf as $key => $value){
            if(is_array($value)){
                static::$cacheConfig[$key] = $value;
            } else {
                static::$cacheConfig[$value] = ['store' => 'array'];
            }
        }
    }

    protected function getAttributeCacheKeyName($key){
        return self::class.'_'.$key;
    }

    public function getAttributeValue($key) {
        if (isset(static::$cacheConfig[$key])) {
            //this key exists in the cache config

            $cacheKey = $this->getAttributeCacheKeyName($key);
            $config = self::$cacheConfig[$key];
            $store = $config['store'] ?? null;
            if (Cache::store($store)->has($cacheKey)) {
                return Cache::store($store)->get($cacheKey);
            }
            $value = parent::getAttributeValue($key);
            $cacheStore = Cache::store($store);
            if (isset($config['ttl'])) {
                return $cacheStore->remember($cacheKey, $config['ttl'], function() use ($value){
                    return $value;
                });
            } else {
                return $cacheStore->rememberForever($cacheKey, function() use ($value){
                    return $value;
                });
            }
        }
        return parent::getAttributeValue($key);
    }

    public function clearCachedMutators($key = null){
        if($key === null){
            foreach(static::$cacheConfig as $key => $config){
                $this->clearCachedMutators($key);
            }
            return $this;
        }
        $config = static::$cacheConfig[$key];
        $store = $config['store'] ?? null;
        Cache::store($store)->forget($this->getAttributeCacheKeyName($key));
        return $this;
    }
}