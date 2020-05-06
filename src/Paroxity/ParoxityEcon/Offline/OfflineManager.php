<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Offline;

use Paroxity\ParoxityEcon\Database\ParoxityEconDatabase;
use Paroxity\ParoxityEcon\ParoxityEcon;
use function is_null;

class OfflineManager{

	/** @var ParoxityEcon */
	private $engine;
	/** @var ParoxityEconDatabase */
	private $db;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;
		$this->db = $engine->getDatabase();
	}

	/**
	 * @param string        $username
	 * @param int           $money
	 * @param callable|null $onSuccess
	 *
	 * $onSuccess = function(int $money): void{}
	 */
	public function addMoney(string $username, int $money, ?callable $onSuccess = null): void{
		$this->db->getMoney($username, function(array $rows) use ($money, $username, $onSuccess){
			$balance = empty($rows) ? ParoxityEcon::$DEFAULT_MONEY : $rows[0]["money"];

			if(($finalBalance = $balance + $money) > ParoxityEcon::$MAX_MONEY){
				$finalBalance = ParoxityEcon::$MAX_MONEY;
			}

			$this->db->updateMoney($username, $finalBalance, function() use ($onSuccess, $finalBalance){
				if(!is_null($onSuccess)){
					$onSuccess($finalBalance);
				}
			});
		});
	}

	/**
	 * @param string        $username
	 * @param int           $money
	 * @param callable|null $onSuccess
	 *
	 * $onSuccess = function(int $money): void{}
	 */
	public function reduceMoney(string $username, int $money, ?callable $onSuccess = null): void{
		$this->db->getMoney($username, function(array $rows) use ($money, $username, $onSuccess){
			$balance = empty($rows) ? ParoxityEcon::$DEFAULT_MONEY : $rows[0]["money"];

			if(($finalBalance = $balance - $money) < 0){
				$finalBalance = 0;
			}

			$this->db->updateMoney($username, $finalBalance, function() use ($onSuccess, $finalBalance){
				if(!is_null($onSuccess)){
					$onSuccess($finalBalance);
				}
			});
		});
	}

	/**
	 * @param string        $username
	 * @param int           $money
	 * @param callable|null $onSuccess
	 *
	 * $onSuccess = function(int $money): void{}
	 */
	public function setMoney(string $username, int $money, ?callable $onSuccess = null): void{
		if($money > ParoxityEcon::$MAX_MONEY){
			$money = ParoxityEcon::$MAX_MONEY;
		}

		$this->db->updateMoney($username, $money, function() use ($onSuccess, $money){
			if(!is_null($onSuccess)){
				$onSuccess($money);
			}
		});
	}
}