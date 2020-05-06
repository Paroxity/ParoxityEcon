<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use Paroxity\ParoxityEcon\Command\Argument\ParoxityEconPlayerArgument;
use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\Session\BaseSession;
use Core\Virion\Commando\TargetPlayerArgument;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use function is_null;

class Set extends BaseSubCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"set",
			"Set money of a player.",
			["setmoney"]
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.setmoney");
		
		$this->registerArgument(0, new ParoxityEconPlayerArgument());
		$this->registerArgument(1, new IntegerArgument("money"));
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$engine = $this->engine;

		$username = $args["player"];
		$money = (int) $args["money"];

		if($money > ParoxityEcon::$MAX_MONEY){
			$money = ParoxityEcon::$MAX_MONEY;
		}

		$found = $engine->getAPI()->getMoney($username, function(int $balance, ?BaseSession $session) use ($sender, $username, $money){
			if(is_null($session)){
				$this->engine->getDatabase()->updateMoney($username, $money);
			}else{
				$session->setMoney($money);
				$session->getPlayer()->sendMessage("§cYour money was set to§6 " . ParoxityEcon::$MONETARY_UNIT . $money);
			}

			$sender->sendMessage("§aSuccessfully set §2$username's§a money to §6" . ParoxityEcon::$MONETARY_UNIT . $money);
		});

		if(!$found){
			$sender->sendMessage("§cPlayer:§4 $username §ccould not be found.");
		}
	}
}