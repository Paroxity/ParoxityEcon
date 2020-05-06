<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\BaseSubCommand;
use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\Utils\ParoxityEconQuery;
use pocketmine\command\CommandSender;

class TopMoney extends BaseSubCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"top",
			"See top 10 players with most money."
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.topmoney");
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$this->engine->getDatabase()->getTopPlayers(ParoxityEconQuery::GET_TOP_10_PLAYERS, function(array $rows) use ($sender){
			$text = "§aTop 10 players with most balance\n\n";

			$i = 1;

			foreach($rows as $row){
				$playerName = $row["player"];
				$money = $row["money"];

				$text .= "§e$i §l§c»§r §2$playerName §l§b»§r §fwith: §6" . ParoxityEcon::$MONETARY_UNIT . $money . "\n";

				$i++;
			}

			$sender->sendMessage($text);
		});
	}
}