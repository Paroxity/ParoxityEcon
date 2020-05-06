<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Session;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class SessionListener implements Listener{

	/** @var SessionManager */
	private $sessionManager;

	public function __construct(SessionManager $sessionManager) {
		$this->sessionManager = $sessionManager;
	}

	/**
	 * Note: Open session first and then register player.
	 *
	 * Priority lowest gets called first.
	 *
	 * @param PlayerJoinEvent $event
	 * @priority LOWEST
	 */
	public function onJoin(PlayerJoinEvent $event): void {
		$this->sessionManager->openOnlineSession($event->getPlayer()->getName());
	}

	/**
	 * Note: Unregister the player first and then close session.
	 *
	 * Priority monitor gets called last.
	 *
	 * @param PlayerQuitEvent $event
	 * @priority MONITOR
	 */
	public function onQuit(PlayerQuitEvent $event): void{
		$this->sessionManager->closeOnlineSession($event->getPlayer()->getName());
	}
}