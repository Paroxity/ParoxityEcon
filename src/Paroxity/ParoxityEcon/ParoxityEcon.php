<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon;

use Paroxity\ParoxityEcon\Cache\ParoxityEconCache;
use Paroxity\ParoxityEcon\Command\ParoxityEconCommand;
use Paroxity\ParoxityEcon\Database\ParoxityEconDatabase;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use function floatval;

class ParoxityEcon extends PluginBase{

	use SingletonTrait;

	/** @var string */
	private static $MONETARY_UNIT = "$";
	/** @var float */
	private static $MAX_MONEY = 50000000.0;
	/** @var float */
	private static $DEFAULT_MONEY = 1000.0;

	/** @var ParoxityEconDatabase */
	private $database;
	/** @var ParoxityEconAPI */
	private $api;

	public static function getMonetaryUnit(): string{
		return self::$MONETARY_UNIT;
	}

	public static function getMaxMoney(): float{
		return self::$MAX_MONEY;
	}

	public static function getDefaultMoney(): float{
		return self::$DEFAULT_MONEY;
	}

	public function onLoad(){
		self::setInstance($this);
	}

	public function onEnable(){
		$this->saveDefaultConfig();

		$this->saveResource("data/dummy.txt");

		self::$MONETARY_UNIT = $this->getConfig()->get("unit", "$");
		self::$MAX_MONEY = floatval($this->getConfig()->get("max-money", 50000000.0));
		self::$DEFAULT_MONEY = floatval($this->getConfig()->get("default-money", 1000.0));

		$this->database = new ParoxityEconDatabase($this);
		$this->api = new ParoxityEconAPI($this, $this->database);

		ParoxityEconCache::init($this);

		$this->getServer()->getPluginManager()->registerEvents(new ParoxityEconListener($this, $this->database), $this);
		$this->getServer()->getCommandMap()->register("ParoxityEcon", new ParoxityEconCommand($this));
	}

	public function getDatabase(): ParoxityEconDatabase{
		return $this->database;
	}

	public function getAPI(): ParoxityEconAPI{
		return $this->api;
	}

	public function onDisable(){
		$this->database->close();
		self::reset();
	}
}