<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Utils;

use InvalidArgumentException;
use pocketmine\utils\UUID;

class ParoxityEconUtils{

	public static function isValidUUID(string $string): bool{
		try{
			UUID::fromString($string);
		}catch(InvalidArgumentException $ex){
			return false;
		}

		return true;
	}
}