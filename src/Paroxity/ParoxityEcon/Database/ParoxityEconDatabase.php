<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Database;

use Paroxity\ParoxityEcon\ParoxityEcon;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlError;
use function is_null;
use function strtolower;

final class ParoxityEconDatabase extends ParoxityEconAwaitDatabase implements ParoxityEconQueryIds{

	/** @var ParoxityEcon */
	private $engine;
	/** @var DataConnector */
	protected $connector;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		$this->connector = libasynql::create(
			$this->engine,
			$this->engine->getConfig()->get("database"),
			[
				"sqlite" => "stmts/sqlite.sql",
				"mysql"  => "stmts/mysql.sql"
			]
		);

		$this->connector->executeGeneric(self::INIT);
		$this->connector->waitAll(); // just to be sure, also waiting on startup isn't a big deal

		$this->engine->getLogger()->debug("Database Initialized.");

		parent::__construct($this->connector);
	}

	public function close(): void{
		$this->connector->close();
	}

	/**
	 * @internal External plugins shouldn't be using this!
	 *
	 * @param string $uuid
	 * @param string $username
	 */
	public function register(string $uuid, string $username): void{
		$username = strtolower($username);

		$this->connector->executeInsert(self::REGISTER,
			[
				"uuid"     => $uuid,
				"username" => $username,
				"money"    => ParoxityEcon::getDefaultMoney(),
			],

			function() use ($uuid, $username): void{
				$this->engine->getLogger()->debug("Player: $username with UUID: $uuid successfully registered.");
			},

			// not sure if this ever get thrown but you never know :/
			function(SqlError $error) use ($uuid, $username): void{
				$engine = $this->engine;
				$player = $engine->getServer()->getPlayerExact($username);

				// display warning ig..
				$engine->getLogger()->warning("Player: $username with UUID: $uuid was kicked since he was found online at two places.");

				if(is_null($player) || !$player->isOnline()){
					return;
				}

				$player->kick("You can't be online at two places at once.", false);
			}
		);
	}

	/*
	 * Note:
	 *
	 * $string is either the username or the uuid of the user.
	 * Use uuid when player is online and username when player is offline.
	 */

	public function addMoney(string $string, float $money, bool $isUUID, ?callable $callable = null): void{
		if($isUUID){
			$this->connector->executeChange(self::ADD_BY_UUID,
				[
					"uuid"  => $string,
					"money" => $money,
					"max"   => ParoxityEcon::getMaxMoney()
				],

				$callable,

				function() use ($callable){
					$callable(-1);
				}
			);
		}else{
			$this->connector->executeChange(self::ADD_BY_USERNAME,
				[
					"username" => $string,
					"money"    => $money,
					"max"      => ParoxityEcon::getMaxMoney()
				],

				$callable,

				function() use ($callable){
					$callable(-1);
				}
			);
		}
	}

	public function deductMoney(string $string, float $money, bool $isUUID, ?callable $callable = null): void{
		if($isUUID){
			$this->connector->executeChange(self::DEDUCT_BY_UUID,
				[
					"uuid"  => $string,
					"money" => $money
				],

				$callable,

				function() use ($callable){
					$callable(-1);
				}
			);
		}else{
			$this->connector->executeChange(self::DEDUCT_BY_USERNAME,
				[
					"username" => $string,
					"money"    => $money
				],

				$callable,

				function() use ($callable){
					$callable(-1);
				}
			);
		}
	}

	public function setMoney(string $string, float $money, bool $isUUID, ?callable $callable = null): void{
		if($money < 0){
			$money = 0;
		}

		if($isUUID){
			$this->connector->executeChange(self::SET_BY_UUID,
				[
					"uuid"  => $string,
					"money" => $money,
					"max"   => ParoxityEcon::getMaxMoney()
				],

				$callable,

				function() use ($callable){
					$callable(-1);
				}
			);
		}else{
			$this->connector->executeChange(self::SET_BY_USERNAME,
				[
					"username" => $string,
					"money"    => $money,
					"max"      => ParoxityEcon::getMaxMoney()
				],

				$callable,

				function() use ($callable){
					$callable(-1);
				}
			);
		}
	}

	public function getMoney(string $string, bool $isUUID, callable $callable): void{
		if($isUUID){
			$this->connector->executeSelect(self::GET_BY_UUID, ["uuid" => $string], $callable, function() use ($callable){
				$callable([]);
			});
		}else{
			$this->connector->executeSelect(self::GET_BY_USERNAME, ["username" => $string], $callable, function() use ($callable){
				$callable([]);
			});
		}
	}

	/**
	 * @see self::GET_TOP_PLAYERS
	 * @see self::GET_TOP_10_PLAYERS
	 *
	 * @param string   $query
	 * @param callable $callable
	 */
	public function getTopPlayers(string $query, callable $callable): void{
		$this->connector->executeSelect($query, [], $callable, function() use ($callable){
			$callable([]);
		});
	}
}