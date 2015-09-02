<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Steps\Release\CreateBranch;

/**
 * Create a new branch
 *
 * @author dmooyman
 */
class Branch extends Release {

	/**
	 *
	 * @var string
	 */
	protected $name = 'release:branch';

	protected $description = 'Branch all modules';

	protected function fire() {
		// Get arguments
		$version = $this->getInputVersion();
		$branch = $this->getInputBranch($version);
		$directory = $this->getInputDirectory($version);

		// Steps
		$step = new CreateBranch($this, $directory, $branch);
		$step->run($this->input, $this->output);
	}
}
