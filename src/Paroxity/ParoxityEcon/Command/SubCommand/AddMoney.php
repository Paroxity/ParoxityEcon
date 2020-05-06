<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use Paroxity\ParoxityEcon\Command\Argument\ParoxityEconPlayerArgument;
use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\Session\BaseSession;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use function is_null;

class AddMoney extends BaseSubCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"add",
			"Add money to a players balance",
			["give"]
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.addmoney");
		
		$this->registerArgument(0, new ParoxityEconPlayerArgument());
		$this->registerArgument(1, new IntegerArgument("money"));
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$engine = $this->engine;

		$username = $args["player"];
		$money = $args["money"];

		if($money >= ParoxityEcon::$MAX_MONEY){
			$sender->sendMessage("§cMoney exceeds the max-money limit.");

			return;
		}

		$found = $engine->getAPI()->getMoney($username, function(int $balance, ?BaseSession $session) use ($sender, $username, $money){
			$finalBalance = $balance + $money;

			if($finalBalance >= ParoxityEcon::$MAX_MONEY){
				$sender->sendMessage("§cUser balance plus the money added exceeds the max-money limit.");

				return;
			}

			$unit = ParoxityEcon::$MONETARY_UNIT;

			if(is_null($session)){
				$this->engine->getDatabase()->updateMoney($username, $finalBalance);
			}else{
				$session->addMoney($money);
				$session->getPlayer()->sendMessage("§aYour were given §6$unit" . "$money.");
			}

			$sender->sendMessage("§aSuccessfully added §6$unit" . "$money §ato §2$username's §aaccount. His new balance is §6$unit" . $finalBalance);

		});

		if(!$found){
			$sender->sendMessage("§cPlayer:§4 $username §ccould not be found.");
		}
	}
}