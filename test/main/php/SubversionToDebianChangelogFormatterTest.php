<?php

require 'SubversionToDebianChangelogFormatter.php';

/*
 * Much thanks to http://stackoverflow.com/questions/9158155/how-to-write-unit-tests-for-interactive-console-app?lq=1
 */

class SubversionToDebianChangelogFormatterTest extends PHPUnit_Framework_TestCase
{

    public function testNoArgumentsPrintsUsageAndExitsWithErrorCode()
    {
        $formatter = new SubversionToDebianChangelogFormatter(array('script-name'));

        $this->expectOutputString("Usage: script-name <package-name> <architecture> </path/to/svn-log.xml> </path/to/optional/scm-alias-file>\n");
        $this->assertEquals(1, $formatter->run());
    }

    public function testMissingInputFilePrintsErrorAndExitsWithErrorCode()
    {
        $formatter = new SubversionToDebianChangelogFormatter(array('script-name', 'package-name', 'arch',
            '/some/unknown/xml/file/path.xml'));

        $this->expectOutputString("Error: Input file (/some/unknown/xml/file/path.xml) not found\n");
        $this->assertEquals(1, $formatter->run());
    }

    public function testMissingSCMAliasFilePrintsErrorAndExitsWithErrorCode()
    {
        $formatter = new SubversionToDebianChangelogFormatter(array('script-name', 'package-name', 'arch',
            'test/main/resources/svn-log.xml', '/some/unknown/path/to/scm/alias/file'));

        $this->expectOutputString("Error: Input file (/some/unknown/path/to/scm/alias/file) not found\n");
        $this->assertEquals(1, $formatter->run());
    }

    public function testParsesSVNLogIntoExpectedDebianFormat()
    {
        $formatter = new SubversionToDebianChangelogFormatter(array('script-name', 'package-name', 'arch',
            'test/main/resources/svn-log.xml', 'test/main/resources/scm-aliases'));

        $this->expectOutputString(file_get_contents('test/main/resources/debian.changelog'));
        $this->assertEquals(0, $formatter->run());
    }

}
?>