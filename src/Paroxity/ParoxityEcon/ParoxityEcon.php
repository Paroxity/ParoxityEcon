<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon;

use Paroxity\ParoxityEcon\Command\ParoxityEconCommand;
use Paroxity\ParoxityEcon\Database\ParoxityEconDatabase;
use pocketmine\plugin\PluginBase;
use function floatval;

class ParoxityEcon extends PluginBase{

	/** @var self|null */
	private static $instance = null;

	/** @var string */
	public static $MONETARY_UNIT = "$";
	/** @var float */
	public static $MAX_MONEY = 50000000.0;
	/** @var float */
	public static $DEFAULT_MONEY = 1000.0;

	/** @var ParoxityEconDatabase */
	private $database;
	/** @var ParoxityEconAPI */
	private $api;

	public static function getInstance(): ?ParoxityEcon{
		return self::$instance;
	}

	public function onLoad(){
		self::$instance = $this;
	}

	public function onEnable(){
		$this->saveDefaultConfig();

		$this->saveResource("data/dummy.txt");
		$this->saveResource("stmts/sqlite.sql", true);
		$this->saveResource("stmts/mysql.sql", true);

		self::$MONETARY_UNIT = $this->getConfig()->get("unit", "$");
		self::$MAX_MONEY = floatval($this->getConfig()->get("max-money", 50000000.0));
		self::$DEFAULT_MONEY = floatval($this->getConfig()->get("default-money", 1000.0));

		$this->database = new ParoxityEconDatabase($this);
		$this->api = new ParoxityEconAPI($this);

		$this->getServer()->getPluginManager()->registerEvents(new ParoxityEconListener($this), $this);
		$this->getServer()->getCommandMap()->register("ParoxityEcon", new ParoxityEconCommand($this));
	}

	public function onDisable(){
		self::$instance = null;

		$this->database->close();
	}

	public function getDatabase(): ParoxityEconDatabase{
		return $this->database;
	}

	public function getAPI(): ParoxityEconAPI{
		return $this->api;
	}
}