<?php
namespace CachedMutators;

use Illuminate\Support\Facades\Cache;

trait HasCachedMutators {
    private $cacheConfig = [];

    /**
     * @return array
     */
    public function getCacheConfig(){
        return $this->cacheConfig;
    }

    /**
     * @return mixed
     */
    public static function defaultCacheTTL(){
        return null;
    }

    /**
     * @return mixed
     */
    public static function defaultCacheStore(){
        return null;
    }

    /**
     * @param $key
     * @return \Illuminate\Contracts\Cache\Repository|null
     */
    private function getCacheStoreForKey($key){
        if(isset($this->cacheConfig[$key])){
            return Cache::store($this->cacheConfig[$key]['store']);
        } else {
            return null;
        }
    }

    public function initializeHasCachedMutators(){
        if(isset($this::$cacheAttributes)){
            //look for static cacheAttributes
            $attrs = $this::$cacheAttributes;
        } else {
            if(isset($this->cacheAttributes)){
                $attrs = $this->cacheAttributes;
            } else {
                return;
            }
        }
        foreach ($attrs as $key => $value) {
            if (is_array($value)) {
                $this->cacheConfig[$key] = static::fillCacheConfig($value);
            } else {
                $this->cacheConfig[$value] = static::fillCacheConfig([]);
            }
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

    /**
     * @param $key
     * @return string
     */
    public function getAttributeCacheKeyName($key){
        return implode('_', [static::class, $this->getKey(), $key]);
    }

    /***
     * @param $key
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getAttributeValue($key) {
        if (isset($this->cacheConfig[$key])) {
            //this key exists in the cache config
            $cacheKey = $this->getAttributeCacheKeyName($key);
            $config = $this->cacheConfig[$key];
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
            foreach($this->cacheConfig as $key => $config){
                $this->clearCachedMutators($key);
            }
            return $this;
        }
        $this->getCacheStoreForKey($key)
            ->forget($this->getAttributeCacheKeyName($key));
        return $this;
    }
}