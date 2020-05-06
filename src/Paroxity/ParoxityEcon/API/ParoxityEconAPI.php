<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\API;

use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\OfflinePlayer;
use function is_null;
use function strtolower;

class ParoxityEconAPI{

	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;
	}

	public function setMoney(string $username, int $money, ?callable $onSuccess = null): bool{
		$engine = $this->engine;

		$username = strtolower($username);
		$player = $engine->getServer()->getPlayerExact($username);
		$money = $money > ParoxityEcon::$MAX_MONEY ? ParoxityEcon::$MAX_MONEY : $money;

		$session = $engine->getSessionManager()->getSession($username);

		if(is_null($session)){
			if($player instanceof OfflinePlayer){
				$engine->getDatabase()->updateMoney($username, $money, function() use ($onSuccess){
					if(!is_null($onSuccess)){
						$onSuccess();
					}
				});

				return true;
			}

			return false;
		}

		$session->setMoney($money);

		return true;
	}

	public function addMoney(string $username, int $money, ?callable $onSuccess = null): bool{
		$engine = $this->engine;

		$username = strtolower($username);
		$player = $engine->getServer()->getPlayerExact($username);
		$money = $money > ParoxityEcon::$MAX_MONEY ? ParoxityEcon::$MAX_MONEY : $money;

		$session = $engine->getSessionManager()->getSession($username);

		if(is_null($session)){
			if($player instanceof OfflinePlayer){
				$engine->getOfflineManager()->addMoney($username, $money, $onSuccess);

				return true;
			}

			return false;
		}

		$session->addMoney($money);

		return true;
	}

	public function reduceMoney(string $username, int $money, ?callable $onSuccess = null): bool{
		$engine = $this->engine;

		$username = strtolower($username);
		$player = $engine->getServer()->getPlayerExact($username);
		$money = $money > ParoxityEcon::$MAX_MONEY ? ParoxityEcon::$MAX_MONEY : $money;

		$session = $engine->getSessionManager()->getSession($username);

		if(is_null($session)){
			if($player instanceof OfflinePlayer){
				$engine->getOfflineManager()->reduceMoney($username, $money, $onSuccess);

				return true;
			}

			return false;
		}

		$session->reduceMoney($money);

		return true;
	}

	/**
	 * Returns true if player is found, false otherwise.
	 *
	 * If player is offline then a callback with the players balance is called.
	 *
	 * callback -> function(int $balance, ?BaseSession $session): void{}
	 *
	 * $session will be null in case of offline player...
	 *
	 * @param string           $username
	 * @param callable         $onSuccess
	 * @return bool
	 */
	public function getMoney(string $username, callable $onSuccess): bool{
		$engine = $this->engine;

		$username = strtolower($username);
		$player = $engine->getServer()->getPlayerExact($username);

		$session = $engine->getSessionManager()->getSession($username);

		if(is_null($session)){
			if($player instanceof OfflinePlayer){
				$engine->getDatabase()->getMoney($username, function(array $rows) use ($username, $onSuccess){
					$balance = empty($rows) ? ParoxityEcon::$DEFAULT_MONEY : $rows[0]["money"];

					$onSuccess((int) $balance, null);
				});

				return true;
			}

			return false;
		}

		$onSuccess($session->getMoney(), $session);

		return true;
	}
}
