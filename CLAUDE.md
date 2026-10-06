# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

PHPTv is a CLI PHP application that controls Sony Bravia Android TVs over the network using the Sony REST API and IRCC (Infrared Compatible Control over IP) protocol.

Usage: `php bin/phptv <tv-ip> <psk>`

## Commands

```bash
# Install dependencies
composer install

# Run static analysis
./vendor/bin/phpstan analyse src

# Build the phar (requires box.phar)
php box.phar build

# Run directly without installing
php bin/phptv 192.168.1.42 0000
```

Tests (PHPUnit, `tests/`) cover `IRCC` using `MockClient` from strictphp/http-clients. Run them with `composer test`.

## Architecture

### Entry Point & DI Container

`bin/phptv` bootstraps the application using `ClanCats\Container` for dependency injection. It binds:
- `client` — the HTTP client (`Client`)
- `repo.*` — API repositories (`Guide`, `System`, `Audio`, `IRCC`), each receiving `@client`
- `cmd.*` — commands (`PHPTv`, `ExecuteCommand`, `RawForward`, `TogglePower`, `VolumeControl`, `InputMenu`, `Help\RemoteHelpCommand`, `Help\CommandHelpCommand`), each receiving its specific dependencies plus the `CLImate` instance

### Two Layers

**Repositories** (`src/*.php` extending `Repository`) wrap the Sony TV REST API. Each repository sets `$tvApiEndpoint` (e.g. `'audio'`, `'system'`) and calls `$this->request('methodName', $params)`, which maps to `POST /sony/<endpoint>` with a JSON-RPC-style body. `IRCC` is separate — it uses `Client::sendIRCC()` to send SOAP requests to `/sony/IRCC`.

**Commands** (`src/Commands/`) extend `BaseCommand` (a `readonly` class, so all subclasses must be `readonly` too), which provides `$this->cli` (CLImate); other dependencies are injected via each command's constructor. All commands implement `execute(array $args = [])`.

### Main Loop

`PHPTv::startCMDLoop()` puts the terminal in raw mode (`stty -echo -icanon`), reads single bytes from STDIN, and maps key codes to IRCC sends or sub-command dispatches. When a sub-command is invoked, the terminal is restored (`stty sane`), the command runs, then raw mode is re-entered.

### Adding a New Command

1. Create `src/Commands/MyCommand.php` as a `final readonly class` extending `BaseCommand`, implement `execute(array $args = [])`.
2. Register it in `bin/phptv` under `cmd.mycommand`.
3. Optionally add a key binding in `PHPTv::startCMDLoop()` and register it in `CommandHelpCommand::KEY_MAPPING`.
