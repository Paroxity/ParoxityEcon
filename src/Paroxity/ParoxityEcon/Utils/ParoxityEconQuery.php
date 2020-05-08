<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Utils;

class ParoxityEconQuery{
	
	public const INIT = "paroxityecon.init";
	public const REGISTER = "paroxityecon.register";

	public const ADD_BY_USERNAME = "paroxityecon.add.by-username";
	public const ADD_BY_UUID = "paroxityecon.add.by-uuid";

	public const DEDUCT_BY_USERNAME = "paroxityecon.deduct.by-username";
	public const DEDUCT_BY_UUID = "paroxityecon.deduct.by-uuid";

	public const SET_BY_USERNAME = "paroxityecon.set.by-username";
	public const SET_BY_UUID = "paroxityecon.set.by-uuid";

	public const GET_BY_USERNAME = "paroxityecon.get.by-username";
	public const GET_BY_UUID = "paroxityecon.get.by-uuid";

	public const GET_TOP_PLAYERS = "paroxityecon.get.top";
	public const GET_TOP_10_PLAYERS = "paroxityecon.get.top10";
}