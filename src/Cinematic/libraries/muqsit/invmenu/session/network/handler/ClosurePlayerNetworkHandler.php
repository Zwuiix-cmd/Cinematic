<?php

declare(strict_types=1);

namespace Cinematic\libraries\muqsit\invmenu\session\network\handler;

use Cinematic\libraries\muqsit\invmenu\session\network\NetworkStackLatencyEntry;
use Closure;

final class ClosurePlayerNetworkHandler implements PlayerNetworkHandler{

	/**
	 * @param Closure(Closure) : \Cinematic\libraries\muqsit\invmenu\session\network\NetworkStackLatencyEntry $creator
	 */
	public function __construct(
		readonly private Closure $creator
	){}

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry{
		return ($this->creator)($then);
	}
}