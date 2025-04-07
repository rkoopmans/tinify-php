<?php

if (!getenv("TINIFY_KEY")) {
    exit("Set the TINIFY_KEY environment variable.\n");
}

if (!class_exists('PHPUnit\Runner\Version')) {
    class_alias('PHPUnit_Runner_Version', 'PHPUnit\Runner\Version');
}
if (version_compare(PHPUnit\Runner\Version::id(), '8') >= 0) {
    require_once("base" . DIRECTORY_SEPARATOR . "integration_2.php");
    class_alias('IntegrationTestCase_2', 'test_case');
} else {
    require_once("base" . DIRECTORY_SEPARATOR . "integration_1.php");
    class_alias('IntegrationTestCase_1', 'test_case');
}


class Integration extends test_case {
    static protected $optimized;

    static public function initialize() {
        \Tinify\setKey(getenv("TINIFY_KEY"));
        \Tinify\setProxy(getenv("TINIFY_PROXY"));
        \Tinify\validate();

        $unoptimizedPath = __DIR__ . "/examples/voormedia.png";
        self::$optimized = \Tinify\fromFile($unoptimizedPath);
    }

    public function testShouldCompressFromFile() {
        $path = tempnam(sys_get_temp_dir(), "tinify-php");
        self::$optimized->toFile($path);

        $size = filesize($path);
        $contents = fread(fopen($path, "rb"), $size);

        $this->assertGreaterThan(1000, $size);
        $this->assertLessThan(1500, $size);

        /* width == 137 */
        $this->assertStringContainsString("\0\0\0\x89", $contents);
        $this->assertStringNotContainsString("Copyright Voormedia", $contents);
    }

    public function testShouldCompressFromUrl() {
        $path = tempnam(sys_get_temp_dir(), "tinify-php");
        $source = \Tinify\fromUrl("https://raw.githubusercontent.com/tinify/tinify-php/master/test/examples/voormedia.png");
        $source->toFile($path);

        $size = filesize($path);
        $contents = fread(fopen($path, "rb"), $size);

        $this->assertGreaterThan(1000, $size);
        $this->assertLessThan(1500, $size);

        /* width == 137 */
        $this->assertStringContainsString("\0\0\0\x89", $contents);
        $this->assertStringNotContainsString("Copyright Voormedia", $contents);
    }

    public function testShouldResize() {
        $path = tempnam(sys_get_temp_dir(), "tinify-php");
        self::$optimized->resize(array("method" => "fit", "width" => 50, "height" => 20))->toFile($path);

        $size = filesize($path);
        $contents = fread(fopen($path, "rb"), $size);

        $this->assertGreaterThan(500, $size);
        $this->assertLessThan(1000, $size);

        /* width == 50 */
        $this->assertStringContainsString("\0\0\0\x32", $contents);
        $this->assertStringNotContainsString("Copyright Voormedia", $contents);
    }

    public function testShouldPreserveMetadata() {
        $path = tempnam(sys_get_temp_dir(), "tinify-php");
        self::$optimized->preserve("copyright", "creation")->toFile($path);

        $size = filesize($path);
        $contents = fread(fopen($path, "rb"), $size);

        $this->assertGreaterThan(1000, $size);
        $this->assertLessThan(2000, $size);

        /* width == 137 */
        $this->assertStringContainsString("\0\0\0\x89", $contents);
        $this->assertStringContainsString("Copyright Voormedia", $contents);
    }

    public function testShouldConvert() {
        $path = tempnam(sys_get_temp_dir(), "tinify-php");
        self::$optimized->convert(array("type" => ["image/webp"]))->toFile($path);

        $size = filesize($path);
        $contents = fread(fopen($path, "rb"), $size);

        $this->assertEquals(substr($contents, 0, 4), "RIFF");
        $this->assertEquals(substr($contents, 8, 4), "WEBP");
    }
}
