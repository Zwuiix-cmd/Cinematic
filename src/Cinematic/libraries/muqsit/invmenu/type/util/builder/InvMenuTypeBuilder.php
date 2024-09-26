<?php

declare(strict_types=1);

namespace Cinematic\libraries\muqsit\invmenu\type\util\builder;

use Cinematic\libraries\muqsit\invmenu\type\InvMenuType;

interface InvMenuTypeBuilder{

	public function build() : InvMenuType;
}