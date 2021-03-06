<!-- PROJECT SHIELDS -->
<!--
*** I'm using markdown "reference style" links for readability.
*** Reference links are enclosed in brackets [ ] instead of parentheses ( ).
*** See the bottom of this document for the declaration of the reference variables
*** for contributors-url, forks-url, etc. This is an optional, concise syntax you may use.
*** https://www.markdownguide.org/basic-syntax/#reference-style-links
-->
<!-- commenting this out until we have more?
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
-->
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]



<!-- PROJECT LOGO -->
<br />
<p align="center">
  <!-- <a href="https://github.com/lpenap/alassea-bot">
    <img src="images/logo.png" alt="Logo" width="80" height="80">
  </a> -->

  <h3 align="center">AlasseaBot</h3>

  <p align="center">
    Extensible General Purpose Discord Bot
    <br />
    <br />
    <a href="https://github.com/lpenap/alassea-bot/issues">Report Bug</a>
    ·
    <a href="https://github.com/lpenap/alassea-bot/issues">Request Feature</a>
  </p>
</p>



<!-- TABLE OF CONTENTS -->
<details open="open">
  <summary><h2 style="display: inline-block">Table of Contents</h2></summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project
AlasseaBot is an early attempt to provide an extensible general purpose discord bot that could be managed through commands. It can be extensible in the sense that new commands could be added without the need to modify the bot core.

### Current State
The current features already implemented are:
* Easy way to add new commands: Just extend the provided abstract class, place your command in a specific folder and restart the bot. The command loader will detect the command received from discord (using a prefix, i.e. `,mycmd`) and will try to load a class with that name in the custom folder (i.e. `Commands\Custom\MycmdCommand`). Having a Custom command will override a Core and System command of the same name. For an easy example checkout the class `Commands\Custom\EchoCommand` or check `Commands\Core\QodCommand` for a more complex example.
* Support for passing of parameters from discord to the custom commands (i.e. `,mycmd param1 param2`).
* Support for custom commands with a three-stage loading phase: prepare(), run() and cleanup() to allow for more custom implementation of commands.
* Access to the high level discord-php api.
* A native php and light NoSQL-like database facility available for your custom commands with an out of the box persistent cache. For an example on how to use this, check `Commands\Core\QodCommand` that makes use of its own cache context to store the `quote of the day` the first time is requested, or You can also check `Commands\Core\WeatherCommand` for info about how to use cache data with expiration.
* System admin configuration (through discord user ids, see `src/alassea-bot.php` for configuration options).

#### Commands list
* `,restart` : Will restart the bot on-the-fly (it will load new code added to it). It is not necesary to restart the bot after adding new commands, it will load them dinamically but once they are loaded you will need to restart the bot again if the command changed (because there is an internal command cache to save on disk IO).
* `,hello` : Basic hello (world?) command.
* `,echo` : Commad that will reply back with the received parameters.
* `,info` : Prints an embed with some info from the bot (i.e. versions).
* `,qod` : Quote of the day command to retrieve qod using the free `quotes.rest` API. Qods are cached for an entire day because of quotes.rest limits.
* `,help` : Print help information of commands in cache.
* `,roll` : Rolls a dice pool using standard dice notaction. see [DiceBag](https://github.com/AnthonyPorthouse/DiceBag) for documentation and examples.
* `,weather` : Weather info for a given city name using the free `metaweather.com` API. Weather info are cached for 5min for the requested location to comply with metaweather's request to use the API no more than once per minute.
* `,rae` : Fetchs spanish word definitions from the RAE's diccionary.

#### ToDo
* Add configuration for welcoming new guild members.
* Add out of the box support for custom auto reaction roles.
* Restrict help command output depending on user permissions: Sysadmin will see system commands, guild admins will see guildamin commands, etc
* Make a list of required bot permissions for the discord invite link.

<!-- GETTING STARTED -->
## Getting Started

To get a local copy up and running follow these simple steps.

### Prerequisites

* composer: Install composer on your system (if you don't have it already). (Maybe I should add a `composer.phar` to the project?)
* Add/create a configuration for a new App/Bot in Discord: https://discord.com/developers/applications . (you will need a valid bot token to configure AlasseaBot). Remember to check the `bot` scope in oauth2 section, and toggle the presence and server member intents in the bot section.
* Create an environment variable called `ALASSEA_DISCORD_TOKEN` with your discord bot token. AlasseaBot will take the required token from there.
* Optionally create the `ALASSEA_DISCORD_SYSADMINS` environment variable with a comma separated list of Discord IDs  (Ids are **not** the username#xxxx combination but the actual discord id) who will be able to access the Sysadmin commands (like restart)

### Installation

1. Clone the repo
   ```sh
   git clone https://github.com/lpenap/alassea-bot.git
   ```
2. Install composer packages
   ```sh
   composer install
   ```
3. Check additional configuration in `src/alassea-bot.php`

<!-- USAGE EXAMPLES -->
## Usage
Run from the cli and from the project folder (This is important since the restart command will work only if you start the bot with this):
```sh
php src/alassea-bot.php 0
```

<!-- CONTRIBUTING -->
## Contributing

Any contributions you make are **greatly appreciated**. Contribution can be in the form of a new core functionality or new custom commands that could make its way to become core or system commands.

The project is currently using Eclipse PHP Convention (built in) and not the Zend or psr-2 convention (this is because this convention is similar to conventions from other language I regularly use. I can be convinced otherwise with a good argument... maybe.)

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE` for more information.

<!-- CONTACT -->
## Contact

Project Link: [https://github.com/lpenap/alassea-bot](https://github.com/lpenap/alassea-bot)

<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/lpenap/alassea-bot?style=for-the-badge
[contributors-url]: https://github.com/lpenap/alassea-bot/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/lpenap/alassea-bot?style=for-the-badge
[forks-url]: https://github.com/lpenap/alassea-bot/network/members
[stars-shield]: https://img.shields.io/github/stars/lpenap/alassea-bot?style=for-the-badge
[stars-url]: https://github.com/lpenap/alassea-bot/stargazers
[issues-shield]: https://img.shields.io/github/issues/lpenap/alassea-bot?style=for-the-badge
[issues-url]: https://github.com/lpenap/alassea-bot/issues
[license-shield]: https://img.shields.io/github/license/lpenap/alassea-bot?style=for-the-badge
[license-url]: https://github.com/lpenap/alassea-bot/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/luisaugustopena
