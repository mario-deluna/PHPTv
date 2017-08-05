<?php 

namespace PHPTv;

use ClanCats\Container\Container;
use League\Climate\Climate;

abstract class BaseCommand 
{
    /**
     * The container
     */
    protected $container;

    /**
     * The command line interface
     */
    protected $cli;

    /**
     * Construct every command with the container and command line interface
     * 
     * @param Container             $container
     * @param Climate               $cli
     */
    public function __construct(Container $container, Climate $cli)
    {
        $this->container = $container;
        $this->cli = $cli;
    }

    /**
     * Shortcut to laod a repo
     * 
     * @return Repository
     */
    protected function repo(string $name)
    {
        return $this->container->get('repo.' . $name);
    }

    /** 
     * Execute the command
     * 
     * @return void
     */
    abstract public function execute(array $args = []);
}