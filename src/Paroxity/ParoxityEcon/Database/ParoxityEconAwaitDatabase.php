<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Database;

use Generator;
use poggit\libasynql\DataConnector;
use SOFe\AwaitGenerator\Await;

class ParoxityEconAwaitDatabase{

	/** @var DataConnector */
	protected $connector;

	public function __construct(DataConnector $connector){
		$this->connector = $connector;
	}

	public function getConnector(): DataConnector{
		return $this->connector;
	}

	public function asyncGeneric(string $queryName, array $args = []): Generator{
		$this->connector->executeGeneric($queryName, $args, yield, yield Await::REJECT);

		return yield Await::ONCE;
	}

	public function asyncRawGeneric(string $queryName, array $args = []): Generator{
		$this->connector->executeGenericRaw($queryName, $args, yield, yield Await::REJECT);

		return yield Await::ONCE;
	}

	public function asyncChange(string $queryName, array $args = []): Generator{
		$this->connector->executeChange($queryName, $args, yield, yield Await::REJECT);

		return yield Await::ONCE;
	}

	public function asyncRawChange(string $queryName, array $args = []): Generator{
		$this->connector->executeChangeRaw($queryName, $args, yield, yield Await::REJECT);

		return yield Await::ONCE;
	}

	public function asyncInsert(string $queryName, array $args = []): Generator{
		$resolve = yield;

		$this->connector->executeInsert($queryName, $args, static function(int $insertId, int $affectedRows) use ($resolve): void{
			$resolve($insertId, $affectedRows);
		},
			yield Await::REJECT
		);

		return yield Await::ONCE;
	}

	public function asyncRawInsert(string $queryName, array $args = []): Generator{
		$resolve = yield;

		$this->connector->executeInsertRaw($queryName, $args, static function(int $insertId, int $affectedRows) use ($resolve): void{
			$resolve($insertId, $affectedRows);
		},
			yield Await::REJECT
		);

		return yield Await::ONCE;
	}

	public function asyncSingleSelect(string $queryName, array $args = []): ?Generator{
		return (yield from $this->asyncSelect($queryName, $args))[0] ?? null;
	}

	public function asyncRawSingleSelect(string $queryName, array $args = []): ?Generator{
		return (yield from $this->asyncRawSelect($queryName, $args))[0] ?? null;
	}

	public function asyncSelect(string $queryName, array $args = []): Generator{
		$this->connector->executeSelect($queryName, $args, yield, yield Await::REJECT);

		return yield Await::ONCE;
	}

	public function asyncRawSelect(string $queryName, array $args = []): ?Generator{
		$this->connector->executeSelectRaw($queryName, $args, yield, yield Await::REJECT);

		return yield Await::ONCE;
	}
}
