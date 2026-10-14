<?php

declare(strict_types=1);

namespace CustomWing;

use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use function array_key_exists;

final class WingMenu implements Form{
	/**
	 * @param list<string> $buttons
	 * @param \Closure(Player, int): void $onSelect
	 */
	public function __construct(
		private string $title,
		private string $content,
		private array $buttons,
		private \Closure $onSelect
	){}

	/** @return array{type: string, title: string, content: string, buttons: list<array{text: string}>} */
	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => $this->title,
			"content" => $this->content,
			"buttons" => array_map(static fn(string $text) : array => ["text" => $text], $this->buttons)
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if($data === null){
			return;
		}
		if(!is_int($data) || !array_key_exists($data, $this->buttons)){
			throw new FormValidationException("Invalid wing menu response");
		}

		($this->onSelect)($player, $data);
	}
}
