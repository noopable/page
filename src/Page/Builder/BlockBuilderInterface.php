<?php
/**
 *
 * @copyright Copyright (c) 2013-2014 KipsProduction (http://www.kips.gr.jp)
 * @license   http://www.kips.gr.jp/newbsd/LICENSE.txt New BSD License
 */

namespace Page\Builder;

use Page\Block\BlockInterface;
use Page\Service;

/**
 * ブロック
 */
interface BlockBuilderInterface
{
    public function __construct(Service $service);

    public function build(BlockInterface $block);

    /**
     *
     * @return BlockInterface
     */
    public function thisBlock();
}