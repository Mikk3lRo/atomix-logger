<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mikk3lRo\atomix\io\Logger;

final class BaseTest extends TestCase
{
    public function testCanWrite() {
        $this->expectOutputRegex('#^\[[^\]]+\]\[[^\]]+\]\sAnd it was written...$#');
        Logger::write('And it was written...');
    }
}