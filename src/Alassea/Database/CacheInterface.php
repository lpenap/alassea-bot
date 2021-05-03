<?php

namespace Alassea\Database;

interface CacheInterface {
	public function setContext(string $contextName): void;
	public function delete(string $key): void;
	public function get(string $key): ?array;
	public function getWithCallback(string $key, $callback);
	public function insert($key, $arrayValue): ?array;
	public function insertWithTtl($key, $arrayValue, $ttl): ?array;
}
