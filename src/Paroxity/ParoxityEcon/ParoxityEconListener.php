<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class ParoxityEconListener implements Listener{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;
	}

	/**
	 * Priority lowest gets called first.
	 *
	 * @param PlayerJoinEvent $event
	 * @priority LOWEST
	 */
	public function onJoin(PlayerJoinEvent $event): void{
		$player = $event->getPlayer();

		$this->engine->getDatabase()->getMoney($player->getUniqueId()->toString(), true, function(array $rows) use ($player): void{
			// player exists in db
			if(!empty($rows)){
				return;
			}

			// register the player in the db
			$this->engine->getDatabase()->register($player->getUniqueId()->toString(), $player->getName());
		});
	}
}