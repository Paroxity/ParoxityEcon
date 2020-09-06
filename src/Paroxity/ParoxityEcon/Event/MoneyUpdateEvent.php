<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Event;

use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\Utils\ParoxityEconUtils;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\Player;
use pocketmine\utils\UUID;

class MoneyUpdateEvent extends PluginEvent{

	/** @var ParoxityEcon */
	private $engine;
	/** @var string */
	private $name;
	/** @var Player|null */
	private $player = null;

	public function __construct(ParoxityEcon $engine, string $name){
		$this->engine = $engine;
		$this->name = $name;

		if(ParoxityEconUtils::isValidUUID($name)){
			$player = $engine->getServer()->getPlayerByUUID(UUID::fromString($name));
		}else{
			$player = $engine->getServer()->getPlayerExact($name);
		}

		$this->player = $player;

		parent::__construct($engine);
	}

	public function getEngine(): ParoxityEcon{
		return $this->engine;
	}

	public function getName(): string{
		return $this->name;
	}

	public function getPlayer(): ?Player{
		return $this->player;
	}

	/**
	 * $callable -> function(?float $money): void{}
	 *
	 * Money will be null if player is not found in db and float if he exists.
	 */
	public function getMoney(callable $callable): void{
		$this->engine->getAPI()->getMoney($this->name, $callable);
	}
}