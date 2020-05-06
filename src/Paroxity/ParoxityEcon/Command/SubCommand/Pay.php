<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\BaseSubCommand;
use Paroxity\ParoxityEcon\Command\Argument\ParoxityEconPlayerArgument;
use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\Session\BaseSession;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use function is_null;

class Pay extends BaseSubCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"pay",
			"Give money to a player."
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.pay");

		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new ParoxityEconPlayerArgument());
		$this->registerArgument(1, new IntegerArgument("money"));
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$engine = $this->engine;

		$senderSession = $engine->getSessionManager()->getSession($sender->getName());

		if(is_null($senderSession)){
			throw new \RuntimeException("Player {$sender->getName()} session is null in Economy");
		}

		$username = $args["player"];
		$money = $args["money"];

		if($money > $senderSession->getMoney()){
			$sender->sendMessage("§cYou do not have enough money to perform this transaction.");

			return;
		}

		$found = $engine->getAPI()->getMoney($username, function(int $balance, ?BaseSession $session) use ($sender, $username, $money, $senderSession){
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
				$session->getPlayer()->sendMessage("§aPlayer §2{$sender->getName()} §agave you §6$unit" . "$money.");
			}

			$senderSession->reduceMoney($money);

			$sender->sendMessage("§aSuccessfully paid §6$unit" . "$money §ato §2$username. §aYour balance now is §6$unit" . $senderSession->getMoney());

		});

		if(!$found){
			$sender->sendMessage("§cPlayer:§4 $username §ccould not be found.");
		}
	}
}