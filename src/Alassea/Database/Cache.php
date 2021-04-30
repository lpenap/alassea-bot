<?php

namespace Alassea\Database;

use SleekDB\Store;

class Cache {
	protected $dataDir;
	protected $store;
	public const DEFAULT_CONTEXT = "cache";
	public function __construct($store = "cache", $basedir = __DIR__) {
		$this->dataDir = $basedir . "/alassea-db";
		$this->store = new Store ( $store, $this->dataDir );
	}
	protected function findByKey($key, $context = Cache::DEFAULT_CONTEXT) {
		$store = $context == Cache::DEFAULT_CONTEXT ? $this->store : new Store ( $context, $this->dataDir );
		$cacheObjs = $store->findBy ( [ 
				'key',
				"=",
				$key
		], null, 1 );
		return isset ( $cacheObjs [0] ) ? $cacheObjs [0] : array (
				'data' => null
		);
	}
	public function delete($key, $context = Cache::DEFAULT_CONTEXT) {
		$store = $context == Cache::DEFAULT_CONTEXT ? $this->store : new Store ( $context, $this->dataDir );
		$store->deleteBy ( [ 
				'key',
				"=",
				$key
		] );
	}
	public function get($key, $callback, $context = Cache::DEFAULT_CONTEXT) {
		return call_user_func ( $callback, ($this->findByKey ( $key, $context )) ['data'] );
	}
	public function getForToday($key, $callback, bool $deleteOld, $context = Cache::DEFAULT_CONTEXT) {
		$cacheObj = $this->findByKey ( $key, $context );
		$result = array (
				'data' => null
		);
		if ($cacheObj ['data'] != null) {
			$currentDate = date ( 'Y-m-d' );
			$cacheDate = $cacheObj ['timestamp'] == null ? $currentDate : date ( 'Y-m-d', $cacheObj ['timestamp'] );
			$isFromToday = $currentDate == $cacheDate;
			if (isset ( $cacheObj ['timestamp'] ) && $isFromToday) {
				// obj is newer than the specified TTL, return it
				$result ['data'] = $cacheObj ['data'];
			} else {
				if ($deleteOld) {
					$this->delete ( $key, $context );
				}
			}
		}

		return call_user_func ( $callback, $result ['data'] );
	}
	public function insert($key, $arrayValue, $context = Cache::DEFAULT_CONTEXT) {
		$store = $context == Cache::DEFAULT_CONTEXT ? $this->store : new Store ( $context, $this->dataDir );
		$cacheObj = array (
				'key' => $key,
				'timestamp' => time (),
				'data' => $arrayValue
		);
		return ($store->insert ( $cacheObj )) ['data'];
	}
}