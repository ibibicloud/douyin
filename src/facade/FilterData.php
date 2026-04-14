<?php

declare(strict_types=1);

namespace ibibicloud\douyin\facade;

use think\Facade;

class FilterData extends Facade
{
    protected static function getFacadeClass(): string
    {
    	return 'ibibicloud\douyin\FilterData';
    }
}