<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Currency;

use Paroxity\ParoxityEcon\Database\ParoxityEconQueryIds as Query;
use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityVault\ParoxityVault;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use SOFe\AwaitGenerator\Await;
use function var_dump;

class CurrencyManager{

	private ParoxityEcon $engine;
	private DataConnector $connector;

	/** @var Currency[] */
	private $currencies = [];

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;
		$this->connector = ParoxityVault::getInstance()->getDatabase()->getConnector();
	}

	public function init(): void{
		Await::f2c(function(){
			$db = ParoxityVault::getInstance()->getDatabase();
			$config = new Config($this->engine->getDataFolder() . "init_currency.yml");
			$localCurrencies = (array) $config->get("currencies");

			$currencies = yield $db->asyncSelect(Query::CURRENCY_GET_ALL);

			// if there is no currency in the db then sync the config entirely
			if(empty($currencies)){
				foreach($localCurrencies as $data){
					$i = yield $db->asyncInsert(Query::CURRENCY_ADD, $data);
					var_dump($i);
 				}
			}
		});

		$this->connector->waitAll();
	}
}