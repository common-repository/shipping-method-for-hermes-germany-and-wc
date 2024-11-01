<?php
/**
 * Box packing (3D bin packing, knapsack problem).
 *
 * @author Doug Wright
 */

namespace WPRuby\Hermes\DVDoug\BoxPacker\Test;

use WPRuby\Hermes\DVDoug\BoxPacker\Box;
use WPRuby\Hermes\DVDoug\BoxPacker\ConstrainedItem;
use WPRuby\Hermes\DVDoug\BoxPacker\ItemList;

class TestConstrainedTestItem extends TestItem implements ConstrainedItem
{
    /**
     * @var int
     */
    public static $limit = 3;

    /**
     * @param ItemList $alreadyPackedItems
     * @param TestBox  $box
     *
     * @return bool
     */
    public function canBePackedInBox(ItemList $alreadyPackedItems, Box $box)
    {
        $alreadyPackedType = array_filter(
            $alreadyPackedItems->asArray(),
            function (TestItem $item) {
                return $item->getDescription() === $this->getDescription();
            }
        );

        return count($alreadyPackedType) + 1 <= static::$limit;
    }
}
