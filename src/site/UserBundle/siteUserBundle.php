<?php

namespace site\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class siteUserBundle extends Bundle
{
	public function getParent() {
		return 'FOSUserBundle';
	}
}
