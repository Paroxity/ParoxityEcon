<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Session;

use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\Task\SessionSaveTask;
use function is_null;
use function strtolower;

class SessionManager{

	/** @var ParoxityEcon */
	private $engine;

	/** @var OnlineSession[] */
	private $onlineSessions = [];

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		$engine->getServer()->getPluginManager()->registerEvents(new SessionListener($this), $engine);
		$engine->getScheduler()->scheduleRepeatingTask(new SessionSaveTask($this), (int) $engine->getConfig()->get("session-save-period") * 60 * 20);
	}

	public function getEngine(): ParoxityEcon{
		return $this->engine;
	}

	public function getSession(string $username): ?BaseSession{
		$username = strtolower($username);

		if(isset($this->onlineSessions[$username])){
			return $this->onlineSessions[$username];
		}

		$player = $this->engine->getServer()->getPlayerExact($username);

		if(!is_null($player) && $player->isOnline()){
			$this->openOnlineSession($username);

			return $this->getOnlineSession($username);
		}

		return null;
	}

	/**
	 * Returns online sessions.
	 *
	 * @return OnlineSession[]
	 */
	public function getOnlineSessions(): array{
		return $this->onlineSessions;
	}

	/**
	 * Returns an online session of a player or null if not found.
	 *
	 * @param string $playerName
	 * @return OnlineSession|null
	 */
	private function getOnlineSession(string $playerName): ?OnlineSession{
		return $this->onlineSessions[strtolower($playerName)] ?? null;
	}

	/**
	 * Opens a players online session. Should be called onLogin().
	 *
	 * @param string $playerName
	 */
	public function openOnlineSession(string $playerName): void{
		$username = strtolower($playerName);

		/* Return if the session already exists. */
		if(isset($this->onlineSessions[$username])){
			return;
		}

		$session = $this->onlineSessions[$username] = new OnlineSession($this, $username);

		$this->engine->getDatabase()->getMoney($username, function(array $rows) use ($username, $session){
			// no player found
			if(empty($rows)){
				$defaultMoney = ParoxityEcon::$DEFAULT_MONEY;

				// if player doesnt have any money then update their money to default money
				$this->engine->getDatabase()->updateMoney($username, $defaultMoney);
				$session->setMoney($defaultMoney);

				return;
			}

			// player was already in db then simply update his money in the session
			$session->setMoney((int) $rows[0]["money"]);
		});
	}

	/**
	 * Closes and saves a players online session. Called onQuit() onCoreDisable().
	 *
	 * @param string $playerName
	 */
	public function closeOnlineSession(string $playerName): void{
		$username = strtolower($playerName);

		if(isset($this->onlineSessions[$username])){
			$this->saveSession($this->onlineSessions[$username]);
			unset($this->onlineSessions[$username]);
		}
	}

	/**
	 * Closes every open Online Session.
	 */
	public function closeOnlineSessions(): void{
		foreach($this->onlineSessions as $username => $session){
			$this->closeOnlineSession($username);
		}
	}

	/**
	 * Saves every online and offline session that exists.
	 *
	 * @see SessionSaveTask
	 */
	public function saveSessions(): void{
		foreach($this->onlineSessions as $username => $session){
			$this->saveSession($session);
		}
	}

	/**
	 * Saves the session to the database.
	 *
	 * @param BaseSession $session
	 */
	private function saveSession(BaseSession $session): void{
		$this->engine->getDatabase()->updateMoney($session->getUsername(), $session->getMoney());
	}
}