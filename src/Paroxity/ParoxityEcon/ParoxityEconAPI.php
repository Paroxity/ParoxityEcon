<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon;

use Paroxity\ParoxityEcon\Database\ParoxityEconDatabase;
use Paroxity\ParoxityEcon\Database\ParoxityEconQueryIds;
use Paroxity\ParoxityEcon\Event\MoneyUpdateEvent;
use pocketmine\utils\Utils;
use SOFe\AwaitGenerator\Await;
use function is_array;
use function is_null;
use function strtolower;

class ParoxityEconAPI{

	public const TRANSACTION_SUCCESSFUL = 0;

	public const ERROR_CAUSE_UNKNOWN         = 1;
	public const ERROR_SENDER_NOT_FOUND      = 2;
	public const ERROR_SENDER_LOW_ON_BALANCE = 3;
	public const ERROR_RECEIVER_NOT_FOUND    = 4;

	/** @var self|null */
	private static $instance = null;

	/** @var ParoxityEcon */
	private $engine;
	/** @var ParoxityEconDatabase */
	private $database;

	public function __construct(ParoxityEcon $engine, ParoxityEconDatabase $database){
		$this->engine = $engine;
		$this->database = $database;

		self::$instance = $this;
	}

	/**
	 * @return ParoxityEconAPI|null
	 */
	public static function getInstance(): ?ParoxityEconAPI{
		return self::$instance;
	}

	public function getEngine(): ParoxityEcon{
		return $this->engine;
	}

	/*
	 * Note:
	 *
	 * $string is either the username or the uuid of the user.
	 * Use uuid when player is online and username when player is offline.
	 */

	/**
	 * $callable -> function(bool $success): void{}
	 *
	 * If money was set successfully then callable would contain true else false.
	 */
	public function setMoney(string $string, float $money, bool $isUUID, ?callable $callable = null): void{
		$this->database->setMoney($string, $money, $isUUID, function(int $affectedRows) use ($callable, $string, $isUUID): void{
			if($affectedRows > 0){
				(new MoneyUpdateEvent($this->engine, $string, $isUUID))->call();

				if(!is_null($callable)){
					$callable(true);
				}

				return;
			}

			if(!is_null($callable)){
				$callable(false);
			}
		});
	}

	/**
	 * $callable -> function(bool $success): void{}
	 *
	 * If money was added successfully then callable would contain true else false.
	 */
	public function addMoney(string $string, float $money, bool $isUUID, ?callable $callable = null): void{
		$this->database->addMoney($string, $money, $isUUID, function(int $affectedRows) use ($callable, $string, $isUUID): void{
			if($affectedRows > 0){
				(new MoneyUpdateEvent($this->engine, $string, $isUUID))->call();

				if(!is_null($callable)){
					$callable(true);
				}

				return;
			}

			if(!is_null($callable)){
				$callable(false);
			}
		});
	}

	/**
	 * $callable -> function(bool $success): void{}
	 *
	 * If money was deducted successfully then callable would contain true else false.
	 */
	public function deductMoney(string $string, float $money, bool $isUUID, ?callable $callable = null): void{
		$this->database->deductMoney($string, $money, $isUUID, function(int $affectedRows) use ($callable, $string, $isUUID): void{
			if($affectedRows > 0){
				(new MoneyUpdateEvent($this->engine, $string, $isUUID))->call();

				if(!is_null($callable)){
					$callable(true);
				}

				return;
			}

			if(!is_null($callable)){
				$callable(false);
			}
		});
	}

	/**
	 * $callable -> function(?float $money): void{}
	 *
	 * Money will be null if player is not found in db and float if he exists.
	 */
	public function getMoney(string $string, bool $isUUID, callable $callable): void{
		$this->database->getMoney($string, $isUUID, function(array $rows) use ($callable): void{
			empty($rows) ? $callable(null) : $callable((float) $rows[0]["money"]);
		});
	}

	/**
	 * @see ParoxityEconQueryIds::GET_TOP_PLAYERS
	 * @see ParoxityEconQueryIds::GET_TOP_10_PLAYERS
	 *
	 * callable -> function(array $rows): void{}
	 */
	public function getTopPlayers(string $query, callable $callable): void{
		$this->database->getTopPlayers($query, $callable);
	}

	/**
	 * $callable -> function(bool $success, ?float $sendersBalance, ?float $receiversBalance, int $errorCode): void{}
	 *
	 * Error Codes:
	 *
	 * @see ParoxityEconAPI::ERROR_CAUSE_UNKNOWN
	 * @see ParoxityEconAPI::ERROR_SENDER_NOT_FOUND
	 * @see ParoxityEconAPI::ERROR_SENDER_LOW_ON_BALANCE
	 * @see ParoxityEconAPI::ERROR_RECEIVER_NOT_FOUND
	 *
	 * The error code will be equal to ParoxityEconAPI::TRANSACTION_SUCCESSFUL if
	 * everything was executed successfully.
	 *
	 * @see ParoxityEconAPI::TRANSACTION_SUCCESSFUL
	 */
	public function pay(string $sendersName, string $receiversName, float $money, ?callable $callback = null): void{
		$sendersName = strtolower($sendersName);
		$receiversName = strtolower($receiversName);

		Await::f2c(
			function() use ($sendersName, $receiversName, $money){
				$return = [
					"error_code" => -1,
					"data"       => []
				];

				$engine = $this->engine;
				$database = $engine->getDatabase();

				// gets senders money and check if he has enough money
				$sendersData = yield $database->asyncSelect(ParoxityEconQueryIds::GET_BY_USERNAME, ["username" => $sendersName]);

				if(empty($sendersData)){
					$return["error_code"] = self::ERROR_SENDER_NOT_FOUND;

					return $return;
				}

				$sendersBalance = $sendersData[0]["money"];

				if($money > $sendersBalance){
					$return["error_code"] = self::ERROR_SENDER_LOW_ON_BALANCE;

					return $return;
				}

				$lookup = [
					"username" => $receiversName,
					"money"    => $money,
					"max"      => ParoxityEcon::getMaxMoney()
				];

				// add the money to the targets balance
				$result = yield $database->asyncChange(ParoxityEconQueryIds::ADD_BY_USERNAME, $lookup);

				if($result === 0){
					$return["error_code"] = self::ERROR_RECEIVER_NOT_FOUND;

					return $return;
				}

				// before adding to targets balance, deduct from senders first
				$result = yield $database->asyncChange(ParoxityEconQueryIds::DEDUCT_BY_USERNAME, ["username" => $sendersName, "money" => $money]);

				if($result === 0){
					// remove the money that was added to targets balance
					yield $database->asyncChange(ParoxityEconQueryIds::DEDUCT_BY_USERNAME, $lookup);
					$return["error_code"] = self::ERROR_CAUSE_UNKNOWN;

					return $return;
				}

				// target balance was added and senders money was deducted. proceed...
				// get targets updated balance
				$receiversData = yield $database->asyncSelect(ParoxityEconQueryIds::GET_BY_USERNAME, $lookup);
				$receiversBalance = $receiversData[0]["money"];
				$sendersBalance = $sendersBalance - $money;

				// call the events for both the players, serves to update the cache as well
				(new MoneyUpdateEvent($this->engine, $sendersName, false))->call();
				(new MoneyUpdateEvent($this->engine, $receiversName, false))->call();

				$return = [
					"data" => [
						"senders_balance"   => $sendersBalance,
						"receivers_balance" => $receiversBalance
					]
				];

				return $return;
			},

			function($data) use ($callback): void{
				if(is_null($callback)){
					return;
				}

				Utils::validateCallableSignature(
					function(bool $success, ?float $sendersBalance, ?float $receiversBalance, int $errorCode): void{},
					$callback
				);

				// callback function signature
				// bool success, ?float sender bal, ?float receivers bal, int error code

				if(!is_array($data)){
					$callback(false, null, null, self::ERROR_CAUSE_UNKNOWN);

					return;
				}

				if(isset($data["error_code"])){
					$callback(false, null, null, (int) $data["error_code"]);

					return;
				}

				$data = $data["data"];

				$callback(true, (float) $data["senders_balance"], (float) $data["receivers_balance"], self::TRANSACTION_SUCCESSFUL);
			}
		);
	}
}