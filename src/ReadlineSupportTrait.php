<?php 

namespace PHPTv;

trait ReadlineSupportTrait 
{
    /**
     * The current readline command history
     * 
     * @var array
     */
    protected $readlineCommandHistory = [];

    /**
     * Adds a command to the history
     * 
     * @param string            $command
     * @return void
     */
    protected function readlineAddHistory(string $command)
    {
        $this->readlineCommandHistory[] = $command;
    }

    /**
     * Readline...
     * 
     * @param string            $prefix
     * @return void
     */
    protected function readlinePromt(string $prefix)
    {
        return readline($prefix);
    }

    /**
     * Will clear the readline history and rebuild it with the 
     * command history of the current class.
     * 
     * @return void
     */
    protected function readlinePrepare()
    {
        readline_clear_history();

        foreach($this->readlineCommandHistory as $command) 
        {
            readline_add_history($command);
        }
    }
}