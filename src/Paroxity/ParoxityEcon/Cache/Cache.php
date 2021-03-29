<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Cache;

use pocketmine\Player;

class Cache{

	private Player $player;
	private float $money;

	public function __construct(Player $player, float $money){
		$this->player = $player;
		$this->money = $money;
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function getMoney(): float{
		return $this->money;
	}

	/**
	 * @internal
	 *
	 * @param float $money
	 */
	public function setMoney(float $money): void{
		$this->money = $money;
	}
}