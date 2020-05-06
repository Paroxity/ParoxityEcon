<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Session;

use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\Player;
use function strtolower;

class BaseSession{

	/** @var SessionManager */
	private $sessionManager;

	/** @var string */
	private $username;
	/** @var Player */
	private $player;
	/** @var int */
	private $money;

	public function __construct(SessionManager $sessionManager, string $username){
		$this->sessionManager = $sessionManager;
		$this->username = strtolower($username);

		$this->player = $sessionManager->getEngine()->getServer()->getPlayerExact($username);
	}

	public function getSessionManager(): SessionManager{
		return $this->sessionManager;
	}

	public function getUsername(): string{
		return $this->username;
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function setUsername(string $username): void{
		$this->username = strtolower($username);
	}

	public function getMoney(): int{
		return $this->money;
	}

	public function setMoney(int $money): void{
		$this->money = $money;
	}

	public function addMoney(int $money): void{
		if($this->money + $money >= ParoxityEcon::$MAX_MONEY){
			$this->money = ParoxityEcon::$MAX_MONEY;

			return;
		}

		$this->money += $money;
	}

	public function reduceMoney(int $money): void{
		if($this->money - $money <= 0){
			$this->money = 0;

			return;
		}

		$this->money -= $money;
	}

	public function __toArray(): array{
		return [
			"username" => $this->username,
			"money"    => $this->money
		];
	}
}