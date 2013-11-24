<?php
namespace Page\Builder;

use Page\Block\BlockInterface;

interface BlockBuilderInterface
{
    public function __construct(\Page\Service $service);
    public function build(BlockInterface $block);
}