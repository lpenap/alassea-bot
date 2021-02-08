<?php

namespace Alassea\Database;

use SleekDB\Store;

class Cache {
	protected $dataDir;
	protected $store;
	public const DEFAULT_CONTEXT = "cache";
	public const KEY = "__cache_key";
	public function __construct($store = "cache", $basedir = __DIR__) {
		$this->dataDir = $basedir . "/alassea-db";
		$this->store = new Store ( $store, $this->dataDir );
	}
	public function get($key, $callback, $context = Cache::DEFAULT_CONTEXT) {
		$store = $context == Cache::DEFAULT_CONTEXT ? $this->store : new Store ( $context, $this->dataDir );
		$cacheObjs = $store->findBy ( [ 
				Cache::KEY,
				"=",
				$key
		], null, 1 );
		$obj = isset ( $cacheObjs [0] ) ? $cacheObjs [0] : null;
		return call_user_func ( $callback, $obj );
	}
	public function insert($key, $arrayValue, $context = Cache::DEFAULT_CONTEXT) {
		$store = $context == Cache::DEFAULT_CONTEXT ? $this->store : new Store ( $context, $this->dataDir );
		$arrayValue [Cache::KEY] = $key;
		return $store->insert ( $arrayValue );
	}
}