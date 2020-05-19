<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Cache;

use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\Player;

class ParoxityEconCache{

	/** @var ParoxityEcon */
	private static $engine;
	/** @var Cache[] */
	private static $cache = [];

	public static function init(ParoxityEcon $engine){
		self::$engine = $engine;
	}

	/*
	 * USE OF THIS CLASS IMPLIES THAT THE PLAYER IS ADDED ON JOIN AND REMOVED ON QUIT
	 *
	 * ADDITIONALLY THIS MAY **ONLY** BE USED FOR DISPLAY PURPOSES
	 */

	/**
	 * @internal
	 *
	 * @param Cache $cache
	 */
	public static function add(Cache $cache): void{
		$player = $cache->getPlayer();
		$uuid = $player->getUniqueId()->toString();

		if(isset(self::$cache[$uuid])){
			return;
		}

		self::$cache[$uuid] = $cache;
		self::$engine->getLogger()->debug("Player: {$player->getName()} with UUID: $uuid added to cache.");
	}

	/**
	 * @internal
	 *
	 * @param Player $player
	 */
	public static function remove(Player $player): void{
		$uuid = $player->getUniqueId()->toString();

		unset(self::$cache[$uuid]);
		self::$engine->getLogger()->debug("Player: {$player->getName()} with UUID: $uuid removed from cache.");
	}

	/**
	 * Will return the Cache object if player is found.
	 *
	 * @param Player $player
	 * @return Cache|null
	 */
	public static function get(Player $player): ?Cache{
		$uuid = $player->getUniqueId()->toString();

		return isset(self::$cache[$uuid]) ? self::$cache[$uuid] : null;
	}

	/**
	 * Will return the money of the player if he exists in cache.
	 *
	 * @param Player $player
	 * @return float|null
	 */
	public static function getMoney(Player $player): ?float{
		$uuid = $player->getUniqueId()->toString();

		return isset(self::$cache[$uuid]) ? self::$cache[$uuid]->getMoney() : null;
	}

	/**
	 * @internal
	 *
	 * @param Player $player
	 * @param float  $money
	 */
	public static function update(Player $player, float $money): void{
		$uuid = $player->getUniqueId()->toString();

		if(!isset(self::$cache[$uuid])){
			return;
		}

		self::$cache[$uuid]->setMoney($money);
	}
}