<?php

declare(strict_types=1);

namespace ibibicloud\douyin\facade;

use think\Facade;

class Analysis extends Facade
{
    protected static function getFacadeClass(): string
    {
    	return 'ibibicloud\douyin\Analysis';
    }
}