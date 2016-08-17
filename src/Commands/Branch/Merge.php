<?php

namespace SilverStripe\Cow\Commands\Branch;

use SilverStripe\Cow\Commands\Module\Module;
use SilverStripe\Cow\Steps\Branch\MergeBranch;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Description of Create
 *
 * @author dmooyman
 */
class Merge extends Module
{
    /**
     * @var string
     */
    protected $name = 'branch:merge';

    protected $description = 'Merge branches';

    protected function configureOptions()
    {
        $this->addArgument('from', InputArgument::REQUIRED, 'Branch name to merge from');
        $this->addArgument('to', InputArgument::REQUIRED, 'Branch name to merge to');
        $this->addOption('interactive', 'i', InputOption::VALUE_NONE, 'Use interactive mode');

        parent::configureOptions(); // TODO: Change the autogenerated stub
    }

    protected function fire()
    {
        $directory = $this->getInputDirectory();
        $modules = $this->getInputModules();
        $listIsExclusive = $this->getInputExclude();
        $push = $this->getInputPush();
        $from = $this->getInputFrom();
        $to = $this->getInputTo();
        $interactive = $this->getInputInteractive();

        // Bit of a sanity check on version
        if (!$this->canMerge($from, $to)) {
            throw new \InvalidArgumentException(
                "{$to} seems like an older version that {$from}. Are you sure that's correct?"
            );
        }

        $merge = new MergeBranch($this, $directory, $modules, $listIsExclusive, $from, $to, $push, $interactive);
        $merge->setVersionConstraint(null); // branch:merge doesn't filter by self.version
        $merge->run($this->input, $this->output);
    }

    /**
     * Using SS workflow, can you merge $from into $to?
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    protected function canMerge($from, $to)
    {
        if ($from === 'master') {
            return false;
        }
        if ($to === 'master') {
            return true;
        }

        // Allow if either $from or $to are non-numeric
        if (!preg_match('/^(\d+)(.\d+)*$/', $from) || !preg_match('/^(\d+)(.\d+)*$/', $to)) {
            return true;
        }

        // Apply minor vs major rule (3.3 > 3 but not 3 > 3.3)
        if (stripos($from, $to) === 0) {
            return true;
        }
        if (stripos($to, $from) === 0) {
            return false;
        }

        // Otherwise, just make sure the to version is a higher value
        return version_compare($to, $from, '>');
    }

    /**
     * Get branch to merge from
     *
     * @return string
     */
    protected function getInputFrom()
    {
        return $this->input->getArgument('from');
    }

    /**
     * Get branch to merge to
     *
     * @return string
     */
    protected function getInputTo()
    {
        return $this->input->getArgument('to');
    }

    /**
     * Check if running in interactive mode
     *
     * @return bool
     */
    protected function getInputInteractive()
    {
        return $this->input->getOption('interactive');
    }
}
