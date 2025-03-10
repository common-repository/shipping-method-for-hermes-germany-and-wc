<?php
/**
 * Box packing (3D bin packing, knapsack problem).
 *
 * @author Doug Wright
 */
namespace WPRuby\Hermes\DVDoug\BoxPacker\Test;

use WPRuby\Hermes\DVDoug\BoxPacker\Box;
use WPRuby\Hermes\DVDoug\BoxPacker\ConstrainedPlacementItem;
use WPRuby\Hermes\DVDoug\BoxPacker\PackedItem;
use WPRuby\Hermes\DVDoug\BoxPacker\PackedItemList;

class ConstrainedPlacementByCountTestItem extends TestItem implements ConstrainedPlacementItem
{
    /**
     * @var int
     */
    public static $limit = 3;

    /**
     * Hook for user implementation of item-specific constraints, e.g. max <x> batteries per box.
     *
     * @param  Box            $box
     * @param  PackedItemList $alreadyPackedItems
     * @param  int            $proposedX
     * @param  int            $proposedY
     * @param  int            $proposedZ
     * @param  int            $width
     * @param  int            $length
     * @param  int            $depth
     * @return bool
     */
    public function canBePacked(
        Box $box,
        PackedItemList $alreadyPackedItems,
        $proposedX,
        $proposedY,
        $proposedZ,
        $width,
        $length,
        $depth
    ) {
        $alreadyPackedType = array_filter(
            iterator_to_array($alreadyPackedItems, false),
            function (PackedItem $item) {
                return $item->getItem()->getDescription() === $this->getDescription();
            }
        );

        return count($alreadyPackedType) + 1 <= static::$limit;
    }
}
