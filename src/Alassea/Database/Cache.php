<?php

namespace Alassea\Database;

use SleekDB\Store;

class Cache implements CacheInterface {
	protected $dataDir;
	protected $store;
	public const DEFAULT_CONTEXT = "cache";
	public function __construct($store, $basedir = ROOT_PATH) {
		$this->dataDir = $basedir . "/alassea-db";
		$this->store = new Store ( $store ?? self::DEFAULT_CONTEXT, $this->dataDir );
	}
	protected function findByKey($key): array {
		$cacheObjs = $this->store->findBy ( [ 
				'key',
				"=",
				$key
		], null, 1 );
		return isset ( $cacheObjs [0] ) ? $cacheObjs [0] : array (
				'data' => null
		);
	}
	public function delete($key): void {
		$this->store->deleteBy ( [ 
				'key',
				"=",
				$key
		] );
	}
	public function getWithCallback(string $key, $callback) {
		return call_user_func ( $callback, $this->get ( $key ) );
	}
	public function get(string $key): ?array {
		$cacheObj = $this->findByKey ( $key );
		if ($cacheObj ['data'] != null) {
			$isExpired = false;
			if ((isset ( $cacheObj ['ttl'] ) && $cacheObj ['ttl'] > 0) && isset ( $cacheObj ['timestamp'] )) {
				$isExpired = time () - $cacheObj ['timestamp'] > $cacheObj ['ttl'];
			}
			if ($isExpired) {
				$this->delete ( $key );
				$cacheObj ['data'] = null;
			}
		}
		return $cacheObj ['data'];
	}
	public function insert($key, $arrayValue): ?array {
		return $this->insertWithTtl ( $key, $arrayValue, 0 );
	}
	public function insertWithTtl($key, $arrayValue, $ttl): ?array {
		$cacheObj = array (
				'key' => $key,
				'timestamp' => time (),
				'ttl' => $ttl,
				'data' => $arrayValue
		);
		return ($this->store->insert ( $cacheObj )) ['data'];
	}
	public function setContext(string $contextName): void {
		$this->store = new Store ( $contextName ?? self::DEFAULT_CONTEXT, $this->dataDir );
	}
}