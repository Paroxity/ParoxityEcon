<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon;

use Paroxity\ParoxityEcon\Database\ParoxityEconDatabase;

class ParoxityEconAPI{

	/** @var self|null */
	private static $instance = null;

	/** @var ParoxityEcon */
	private $engine;
	/** @var ParoxityEconDatabase */
	private $db;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;
		$this->db = $engine->getDatabase();

		self::$instance = $this;
	}

	/**
	 * @return ParoxityEconAPI|null
	 */
	public static function getInstance(): ?ParoxityEconAPI{
		return self::$instance;
	}

	public function getEngine(): ParoxityEcon{
		return $this->engine;
	}

	/*
	 * Note:
	 *
	 * $string is either the username or the uuid of the user.
	 * Use uuid when player is online and username when player is offline.
	 */

	/**
	 * $callable -> function(bool $success): void{}
	 *
	 * If money was set successfully then callable would contain true else false.
	 */
	public function setMoney(string $string, float $money, bool $isUUID, ?callable $callable = null): void{
		$this->db->setMoney($string, $money, $isUUID, function(int $affectedRows) use ($callable): void{
			$affectedRows > 0 ? $callable(true) : $callable(false);
		});
	}

	/**
	 * $callable -> function(bool $success): void{}
	 *
	 * If money was added successfully then callable would contain true else false.
	 */
	public function addMoney(string $string, float $money, bool $isUUID, ?callable $callable = null): void{
		$this->db->addMoney($string, $money, $isUUID, function(int $affectedRows) use ($callable): void{
			$affectedRows > 0 ? $callable(true) : $callable(false);
		});
	}

	/**
	 * $callable -> function(bool $success): void{}
	 *
	 * If money was deducted successfully then callable would contain true else false.
	 */
	public function deductMoney(string $string, float $money, bool $isUUID, ?callable $callable = null): void{
		$this->db->deductMoney($string, $money, $isUUID, function(int $affectedRows) use ($callable): void{
			$affectedRows > 0 ? $callable(true) : $callable(false);
		});
	}

	/**
	 * $callable -> function(?float $money): void{}
	 *
	 * Money will be null if player is not found in db and float if he exists.
	 */
	public function getMoney(string $string, bool $isUUID, callable $callable): void{
		$this->db->getMoney($string, $isUUID, function(array $rows) use ($callable): void{
			if(empty($rows)){
				$callable(null);

				return;
			}

			$callable((float) $rows[0]["money"]);
		});
	}

	/**
	 * @see ParoxityEconQuery::GET_TOP_PLAYERS
	 * @see ParoxityEconQuery::GET_TOP_10_PLAYERS
	 *
	 * callable -> function(array $rows): void{}
	 */
	public function getTopPlayers(string $query, callable $callable){
		$this->db->getTopPlayers($query, $callable);
	}
	public function getMonetaryUnit(): string{
		return \Paroxity\ParoxityEcon\ParoxityEcon::$MONETARY_UNIT;
	}
}
