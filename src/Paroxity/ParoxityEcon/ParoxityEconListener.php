<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon;

use Paroxity\ParoxityEcon\Database\ParoxityEconDatabase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

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

		$this->engine->getAPI()->getMoney($player->getUniqueId()->toString(), true, function(array $rows) use ($player): void{
			// player exists in db
			if(!empty($rows)){
				return;
			}

			// register the player in the db
			$this->database->register($player->getUniqueId()->toString(), $player->getName());
		});
	}
}