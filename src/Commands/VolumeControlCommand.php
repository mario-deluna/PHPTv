<?php declare(strict_types = 1);

namespace PHPTv\Commands;

use League\CLImate\CLImate;
use PHPTv\Exceptions\HttpBadResponseException;
use PHPTv\Exceptions\UnexpectedTvResponseException;
use PHPTv\RemoteControl\Audio;

final readonly class VolumeControlCommand extends BaseCommand
{

	public function __construct(
		private Audio $audio,
		CLImate $cli,
	)
	{
		parent::__construct($cli);
	}

	/**
	 * @param array<string> $args
	 */
	public function execute(array $args = []): void
	{
		try {
			if (!isset($args[0])) {
				$this->printVolumeInformation();
				return;
			}

			$targetVolume = $args[0];
			$target = isset($args[1]) ? 'headphone' : 'speaker';

			$this->cli->out('Setting volume to: ' . $targetVolume . ' on ' . $target);
			$this->audio->setAudioVolume($targetVolume, $target);
			$this->cli->out('Done.');
		} catch (HttpBadResponseException | UnexpectedTvResponseException $e) {
			$this->cli->error($e->getMessage());
		}
	}

	/**
	 * @throws HttpBadResponseException
	 * @throws UnexpectedTvResponseException
	 */
	protected function printVolumeInformation(): void
	{
		$this->cli->table($this->audio->getVolumeInformation());
		$this->cli->out("You can set the volume between 0 and 100.\n - volume 40 # Sets the volume to 40\n - volume 40 -a # Sets the volume on the speakers and headphones.");
	}

}
