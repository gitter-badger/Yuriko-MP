<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\Player;

class LitRedstoneTorch extends Flowable{ //implements Redstone

    protected $id = self::LIT_REDSTONE_TORCH;

    public function __construct($meta = 0){
        $this->meta = $meta;
    }

    public function getLightLevel(){
        return 7;
    }

    public function getName(){
        return "Redstone Torch";
    }

    public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
        $below = $this->getSide(0);
        if($target->isTransparent() === false and $face !== 0){
            $faces = [
                1 => 5,
                2 => 4,
                3 => 3,
                4 => 2,
                5 => 1,
            ];
            $this->meta = $faces[$face];
            $this->getLevel()->setBlock($block, $this, true, true);
            return true;
        }elseif($below->isTransparent() === false){
            $this->meta = 0;
            $this->getLevel()->setBlock($block, $this, true, true);
            return true;
        }
        return false;
    }

    public function getDrops(Item $item){
        return [
            [$this->id, 0, 1],
        ];
    }
}