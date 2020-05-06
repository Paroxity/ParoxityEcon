<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon;

use Paroxity\ParoxityEcon\API\ParoxityEconAPI;
use Paroxity\ParoxityEcon\Command\ParoxityEconCommand;
use Paroxity\ParoxityEcon\Database\ParoxityEconDatabase;
use Paroxity\ParoxityEcon\Offline\OfflineManager;
use Paroxity\ParoxityEcon\Session\SessionManager;
use pocketmine\plugin\PluginBase;

class ParoxityEcon extends PluginBase{

	/** @var ParoxityEconDatabase */
	private $database;
	/** @var SessionManager */
	private $sessionManager;
	/** @var OfflineManager */
	private $offlineManager;
	/** @var ParoxityEconAPI */
	private $api;

	/** @var string */
	public static $MONETARY_UNIT = "$";
	/** @var int */
	public static $MAX_MONEY = 50000000;
	/** @var int */
	public static $DEFAULT_MONEY = 1000;

	public function onEnable(){
		$this->saveDefaultConfig();
		
		$this->saveResource("data/dummy.txt");
		$this->saveResource("stmts/sqlite.sql", true);
		$this->saveResource("stmts/mysql.sql", true);
		
		$this->database = new ParoxityEconDatabase($this);
		$this->sessionManager = new SessionManager($this);
		$this->offlineManager = new OfflineManager($this);
		$this->api = new ParoxityEconAPI($this);

		self::$MONETARY_UNIT = $this->getConfig()->get("unit", "$");
		self::$MAX_MONEY = $this->getConfig()->get("max-money", 50000000);
		self::$DEFAULT_MONEY = $this->getConfig()->get("default-money", 1000);

		$this->getServer()->getCommandMap()->register("ParoxityEcon", new ParoxityEconCommand($this));
	}

	public function onDisable(){
		$sessionManager = $this->sessionManager;

		$sessionManager->closeOnlineSessions();
		$sessionManager->getEngine()->getDatabase()->close();
	}

	public function getDatabase(): ParoxityEconDatabase{
		return $this->database;
	}

	public function getSessionManager(): SessionManager{
		return $this->sessionManager;
	}

	public function getOfflineManager(): OfflineManager{
		return $this->offlineManager;
	}

	public function getAPI(): ParoxityEconAPI{
		return $this->api;
	}
}