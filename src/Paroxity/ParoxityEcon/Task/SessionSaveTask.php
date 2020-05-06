<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Task;

use Paroxity\ParoxityEcon\Session\SessionManager;
use pocketmine\scheduler\Task;

class SessionSaveTask extends Task{

	/** @var SessionManager */
	private $sessionManager;

	public function __construct(SessionManager $sessionManager){
		$this->sessionManager = $sessionManager;
	}

	public function onRun(int $currentTick){
		$this->sessionManager->saveSessions();
	}
}