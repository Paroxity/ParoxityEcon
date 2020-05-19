<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon;

use Paroxity\ParoxityEcon\Cache\Cache;
use Paroxity\ParoxityEcon\Cache\ParoxityEconCache;
use Paroxity\ParoxityEcon\Database\ParoxityEconDatabase;
use Paroxity\ParoxityEcon\Event\MoneyUpdateEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use function is_null;

class ParoxityEconListener implements Listener{

	/** @var ParoxityEcon */
	private $engine;
	/** @var ParoxityEconDatabase */
	private $database;

	public function __construct(ParoxityEcon $engine, ParoxityEconDatabase $database){
		$this->engine = $engine;
		$this->database = $database;
	}

	/**
	 * Priority lowest gets called first.
	 *
	 * @param PlayerJoinEvent $event
	 * @priority LOWEST
	 */
	public function onJoin(PlayerJoinEvent $event): void{
		$player = $event->getPlayer();

		/* Note:
		 *
		 * Add the player to cache with default money then update later on
		 * Further eliminating the need of adding player to cache while registering in db
		 */
		ParoxityEconCache::add(new Cache($player, ParoxityEcon::getDefaultMoney()));

		$this->engine->getAPI()->getMoney($player->getUniqueId()->toString(), true, function(?float $money) use ($player): void{
			// player exists in db
			if(!is_null($money)){
				// player was found so update his balance safely now
				ParoxityEconCache::update($player, $money);

				return;
			}

			// register the player in the db
			$this->database->register($player->getUniqueId()->toString(), $player->getName());
		});
	}

	public function onQuit(PlayerQuitEvent $event): void{
		ParoxityEconCache::remove($event->getPlayer());
	}

	public function onMoneyUpdate(MoneyUpdateEvent $event): void{
		$player = $event->getPlayer();

		if(is_null($player) || !$player->isOnline()){
			return;
		}

		$event->getMoney(function(?float $money) use ($player): void{
			if(is_null($money)){
				return;
			}

			ParoxityEconCache::update($player, $money);
		});
	}
}