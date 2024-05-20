<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Web\{APPNAME}
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

?>
<header>
    <nav>
       <ul>
          <li><a href="<?= UriFactory::build('{/base}/{/app}'); ?>">Website</a>
          <li><a href="<?= UriFactory::build('{/base}/{/app}/components'); ?>">Profile</a>
       </ul>
    </nav>
    <div id="search">
        <input type="text" name="search">
    </div>
</header>