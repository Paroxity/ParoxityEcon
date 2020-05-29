<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\args\FloatArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use Paroxity\ParoxityEcon\Command\Argument\ParoxityEconPlayerArgument;
use Paroxity\ParoxityEcon\Database\ParoxityEconQueryIds;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use SOFe\AwaitGenerator\Await;
use function floatval;

class Pay extends BaseSubCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			$engine,
			"pay",
			"Give money to a player."
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.pay");

		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new ParoxityEconPlayerArgument());
		$this->registerArgument(1, new FloatArgument("money"));
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		// shut up phpstan and phpstorm
		if(!$sender instanceof Player){
			return;
		}

		$username = $args["player"];
		$money = floatval($args["money"]);

		Await::f2c(function() use ($sender, $username, $money){
			$engine = $this->engine;
			$database = $engine->getDatabase();
			$sendersUUID = $sender->getUniqueId()->toString();

			// gets senders money and check if he has enough money
			$sendersData = yield $database->asyncSelect(ParoxityEconQueryIds::GET_BY_UUID, ["uuid" => $sendersUUID]);

			if(empty($sendersData)){
				$sender->sendMessage("§cSomething went wrong. Unable to get your money.");

				return;
			}

			$sendersBalance = $sendersData[0]["money"];

			if($money > $sendersBalance){
				$sender->sendMessage("§cYou do not have enough money to perform this transaction.");

				return;
			}

			$online = false;
			$lookup = ["username" => $username];

			$player = $this->engine->getServer()->getPlayerExact($username);

			if($player instanceof Player && $player->isOnline()){
				$online = true;
				$lookup = ["uuid" => $player->getUniqueId()->toString()];
			}

			$addLookup = $lookup;
			$addLookup["money"] = $money;
			$addLookup["max"] = ParoxityEcon::getMaxMoney();

			// add the money to the targets balance
			$result = yield $database->asyncChange($online ? ParoxityEconQueryIds::ADD_BY_UUID : ParoxityEconQueryIds::ADD_BY_USERNAME, $addLookup);

			if($result === 0){
				$sender->sendMessage("§cPlayer:§4 $username §ccould not be found.");

				return;
			}

			// before adding to targets balance, deduct from senders first
			$result = yield $database->asyncChange(ParoxityEconQueryIds::DEDUCT_BY_UUID, ["uuid" => $sendersUUID, "money" => $money]);

			if($result === 0){
				$deductLookup = $lookup;
				$deductLookup["money"] = $money;

				// remove the money that was added to targets balance
				yield $database->asyncChange($online ? ParoxityEconQueryIds::DEDUCT_BY_UUID : ParoxityEconQueryIds::DEDUCT_BY_USERNAME, $deductLookup);

				$sender->sendMessage("§cUnable to perform the transaction. Something went wrong.");

				return;
			}

			// target balance was added and senders money was deducted. proceed...
			// get targets updated balance
			$playersData = yield $database->asyncSelect($online ? ParoxityEconQueryIds::GET_BY_UUID : ParoxityEconQueryIds::GET_BY_USERNAME, $lookup);
			$playersBalance = $playersData[0]["money"];

			$unit = ParoxityEcon::getMonetaryUnit();

			if($online){
				$player->sendMessage("§aPlayer: §2{$sender->getName()} §agave you §6$unit" . $money . "§a. Your new balance is §6$unit" . $playersBalance);
			}

			$sender->sendMessage("§aSuccessfully paid §6$unit" . "$money §ato §2$username. §aYour balance now is §6$unit" . ($sendersBalance - $money));
		});
	}
}