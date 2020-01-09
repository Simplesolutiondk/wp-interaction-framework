<?php

namespace SimpleSolution\WpInteractionFramework;

use Exception;

abstract class Init {

	// TODO: Implement check for if already initialized.
	static function load() {
        static::setup();
	}

	static function setup() {
		throw new Exception('No setup function specified');
	}

}
