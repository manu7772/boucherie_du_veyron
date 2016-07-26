<?php

namespace site\adminsiteBundle;

// use Symfony\Component\HttpKernel\Bundle\Bundle;
use site\adminBundle\siteadminBundle;

class siteadminsiteBundle extends siteadminBundle {

	public function getParent() {
		return "siteadminBundle";
	}

}
