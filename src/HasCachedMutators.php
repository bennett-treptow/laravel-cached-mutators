<?php
namespace CachedMutators;

use Illuminate\Support\Facades\Cache;

trait HasCachedMutators {
    private static $cacheConfig = [];
    public function getCacheConfig(){
        return self::$cacheConfig;
    }

    public static function defaultCacheTTL(){
        return null;
    }
    public static function defaultCacheStore(){
        return null;
    }

    private function getCacheStoreForKey($key){
        if(isset(static::$cacheConfig[$key])){
            return Cache::store(static::$cacheConfig[$key]['store']);
        } else {
            return null;
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private static function fillCacheConfig($data){
        $defaultObj = [
            'store' => static::defaultCacheStore(),
            'ttl' => static::defaultCacheTTL()
        ];
        foreach($data as $key => $val){
            $defaultObj[$key] = $val;
        }
        return $defaultObj;
    }

    public static function bootHasCachedMutators(){
        $conf = static::$cacheAttributes;
        foreach($conf as $key => $value){
            if(is_array($value)){
                static::$cacheConfig[$key] = static::fillCacheConfig($value);
            } else {
                static::$cacheConfig[$value] = static::fillCacheConfig([]);
            }
        }
    }

    /**
     * @param $key
     * @return string
     */
    public function getAttributeCacheKeyName($key){
        return implode('_', [self::class, $this->getKey(), $key]);
    }

    /***
     * @param $key
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getAttributeValue($key) {
        if (isset(static::$cacheConfig[$key])) {
            //this key exists in the cache config
            $cacheKey = $this->getAttributeCacheKeyName($key);
            $config = self::$cacheConfig[$key];
            $cacheStore = $this->getCacheStoreForKey($key);
            if ($cacheStore->has($cacheKey)) {
                return $cacheStore->get($cacheKey);
            }
            $value = parent::getAttributeValue($key);

            if (isset($config['ttl']) && !empty($config['ttl']) && $config['ttl'] !== null) {
                return $cacheStore->remember($cacheKey, $config['ttl'], function() use ($value){
                    return $value;
                });
            } else {
                return $cacheStore->rememberForever($cacheKey, function() use ($value){
                    return $value;
                });
            }
        }
        //or just pass thru
        return parent::getAttributeValue($key);
    }

    /**
     * @param string|null $key
     * @return $this
     */
    public function clearCachedMutators($key = null){
        if($key === null){
            foreach(static::$cacheConfig as $key => $config){
                $this->clearCachedMutators($key);
            }
            return $this;
        }
        $this->getCacheStoreForKey($key)
            ->forget($this->getAttributeCacheKeyName($key));
        return $this;
    }
}