<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use Paroxity\ParoxityEcon\Command\Argument\ParoxityEconPlayerArgument;
use Paroxity\ParoxityEcon\Session\BaseSession;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\command\CommandSender;
use function is_null;

class ReduceMoney extends BaseSubCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"reduce",
			"Take money from players balance.",
			["take"]
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.reduce");
		
		$this->registerArgument(0, new ParoxityEconPlayerArgument());
		$this->registerArgument(1, new IntegerArgument("money"));
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
        $engine = $this->engine;

		$username = $args["player"];
		$money = $args["money"];

		if($money <= 0){
			$sender->sendMessage("§cPlease enter valid money.");

			return;
		}

		$found = $engine->getAPI()->getMoney($username, function(int $balance, ?BaseSession $session) use ($sender, $username, $money){
			$finalBalance = $balance - $money;

			if($finalBalance < 0){
				$finalBalance = 0;
			}

			$unit = ParoxityEcon::$MONETARY_UNIT;

			if(is_null($session)){
				$this->engine->getDatabase()->updateMoney($username, $finalBalance);
			}else{
				$session->reduceMoney($money);
				$session->getPlayer()->sendMessage("§6$unit" . "$money §was taken away from your account. Your new balance is §6$unit" . $session->getMoney());
			}

			$sender->sendMessage("§aSuccessfully removed §4$unit" . "$money §afrom §2$username's §aaccount. His new balance is§6 $unit" . $finalBalance);
		});

		if(!$found){
			$sender->sendMessage("§cPlayer:§4 $username §ccould not be found.");
		}
	}
}