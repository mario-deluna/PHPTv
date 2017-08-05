<p align="center"><a href="https://github.com/mario-deluna/phptv/" target="_blank">
    <img width="100px" src="https://user-images.githubusercontent.com/956212/28140686-a732b15c-6759-11e7-81ed-5a7968ff1e14.png">
</a></p>

# PHPTv

A Command-Line Remote control for Sony Bravia Android TV written in PHP.

It was one of those evenings where I could not find the fu****g remote for my TV. So instead of doing the work I'm supposed to do. I decided to procrastinate by writing this little program that allows you to control your Sony Android TV over the command line.  

[![license](https://img.shields.io/github/license/mario-deluna/phptv.svg)]()
[![GitHub release](https://img.shields.io/github/release/mario-deluna/phptv.svg)]()

## ❓❓❓

![ezgif-2-b5b7210025](https://user-images.githubusercontent.com/956212/28995030-035ace24-79de-11e7-91bb-44e5a77cc8c1.gif)

## Installation

If you are using composer you can make use of global packages.

```sh
$ composer global require mario-deluna/phptv
```

Make sure that you have exported the vendors bin path:

```sh
$ export PATH="$PATH:$HOME/.composer/vendor/bin"
```

You can also just download and use the prebuild `.phar`.

```sh
$ wget https://github.com/mario-deluna/PHPTv/releases/download/v1.0.0/phptv.phar
```

And if you trust me, add it to your bin dir.

```sh
$ sudo chmod a+x phptv.phar
$ sudo mv phptv.phar /usr/local/bin/phptv
```

### Setting up the Pre-Shared Key (PSK)

 1. Go To "Settings" > "Network" > "Home network setup" > "IP control"
 2. Set "Authentication" to "Normal and Pre-Shared Key" 
 3. Choose a "Pre-Shared Key". (Default is 0000)

Now you should be ready to use the remote.

## Usage 

To start the remote just enter `phptv <the ip of your tv> <psk>`.

```sh
$ phptv 192.168.1.42 0000
```

Remote key controls: 

```
------------------------------------------------------------
| key | action      | description                          |
============================================================
| ←   | Left        | Navigate left                        |
------------------------------------------------------------
| →   | Right       | Navigate Right                       |
------------------------------------------------------------
| ↑   | Up          | Navigate Up                          |
------------------------------------------------------------
| ↓   | Down        | Navigate Down                        |
------------------------------------------------------------
| ↵   | Confirm     | Enter / Confirm                      |
------------------------------------------------------------
| ⌫   | Return      | Go Back / Return                     |
------------------------------------------------------------
| c   | Command     | Opens the command prompt.            |
------------------------------------------------------------
| f   | Forward     | Enter the forward raw commands mode. |
------------------------------------------------------------
| g   | Home        | Go Home.                             |
------------------------------------------------------------
| p   | TogglePower | Turns the TV on / Off                |
------------------------------------------------------------
| m   | Mute        | Mute / Unmute the Tv.                |
------------------------------------------------------------
| b   | VolumeDown  | Turn down for what?                  |
------------------------------------------------------------
| n   | VolumeUp    | Turn up the Volume                   |
------------------------------------------------------------
``

## PS

This is an absolute "just for fun" and "because I can" project. You will be off much faster just using the normal Remote.

## Credits

- [Mario Döring](https://github.com/mario-deluna)
- [All Contributors](https://github.com/mario-deluna/PHPTv/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/mario-deluna/PHPTv/blob/master/LICENSE) for more information.
