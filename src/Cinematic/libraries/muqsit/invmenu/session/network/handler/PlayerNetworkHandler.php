<?php

declare(strict_types=1);

namespace Cinematic\libraries\muqsit\invmenu\session\network\handler;

use Cinematic\libraries\muqsit\invmenu\session\network\NetworkStackLatencyEntry;
use Closure;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}