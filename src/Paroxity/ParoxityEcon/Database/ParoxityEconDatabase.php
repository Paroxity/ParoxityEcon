<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Database;

use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\Utils\ParoxityEconQuery;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use function strtolower;

class ParoxityEconDatabase{
	
	/** @var ParoxityEcon */
	private $engine;
	/** @var libasynql|DataConnector */
	private $connector;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		$this->initDatabase();
	}

	/**
	 * Prepare the database.
	 */
	private function initDatabase(): void{
		$this->connector = libasynql::create(
			$this->engine,
			$this->engine->getConfig()->get("database"),
			[
				"sqlite" => "stmts/sqlite.sql",
				"mysql" => "stmts/mysql.sql"
			]
		);

		$this->connector->executeGeneric(ParoxityEconQuery::INIT_TABLE);

		$this->engine->getLogger()->debug("Database Initialized.");
	}

	/**
	 * @param string        $playerName
	 * @param int           $money
	 * @param callable|null $callable
	 */
	public function updateMoney(string $playerName, int $money, ?callable $callable = null): void{
		$playerName = strtolower($playerName);

		$this->connector->executeChange(ParoxityEconQuery::UPDATE,
			[
				"player" => $playerName,
				"money" => $money
			],

			$callable
		);
	}

	public function getMoney(string $playerName, callable $callable){
		$playerName = strtolower($playerName);

		$this->connector->executeSelect(ParoxityEconQuery::GET_PLAYER_MONEY,
			[
				"player" => $playerName,
			],

			$callable
		);
	}

	/**
	 * @param string   $query
	 * @param callable $callable
	 */
	public function getTopPlayers(string $query, callable $callable){
		$this->connector->executeSelect($query, [], $callable);
	}

	/**
	 * @return DataConnector|libasynql
	 */
	public function getConnector(): DataConnector{
		return $this->connector;
	}

	/**
	 * Close the database.
	 */
	public function close(): void{
		$this->connector->close();
	}
}