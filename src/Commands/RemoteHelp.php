<?php 

namespace PHPTv\Commands;

use PHPTv\BaseCommand;
use PHPTv\Exception;

class RemoteHelp extends BaseCommand
{
	/** 
     * Show the user the current key mapping as a help
	 * This is not automatically generated so you will have to update this method when
	 * changes to the mapping are made.
     * 
     * @return void
     */
	public function execute()
	{	
		$data = [
		    [
		  		'key' => '←',
		  		'action' => 'Left',
		  		'description' => 'Navigate left',
		    ],
		    [
		  		'key' => '→',
		  		'action' => 'Right',
		  		'description' => 'Navigate Right',
		    ],
		    [
		  		'key' => '↑',
		  		'action' => 'Up',
		  		'description' => 'Navigate Up',
		    ],
		    [
		  		'key' => '↓',
		  		'action' => 'Down',
		  		'description' => 'Navigate Down',
		    ],
		    [
		  		'key' => '↵',
		  		'action' => 'Confirm',
		  		'description' => 'Enter / Confirm',
		    ],
		    [
		  		'key' => '⌫',
		  		'action' => 'Return',
		  		'description' => 'Go Back / Return',
		    ],
		    [
		  		'key' => 'f',
		  		'action' => 'Forward',
		  		'description' => 'Enter the forward raw commands mode.',
		    ],
		    [
		  		'key' => 'h',
		  		'action' => 'Home',
		  		'description' => 'Go Home.',
		    ],
		];

		$this->cli->table($data);
	}
}