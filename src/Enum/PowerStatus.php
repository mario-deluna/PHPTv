<?php declare(strict_types = 1);

namespace PHPTv\Enum;

enum PowerStatus: string
{

	case Inactive = 'inactive';
	case Active = 'active';
	case Standby = 'standby';

}
