#!/usr/bin/php
<?php

class SubversionToDebianChangelogFormatter {

    public function __construct($argv)
    {
        $this->argc = count($argv);
        $this->argv = $argv;
    }

    public function run()
    {
        $result = $this->validateArgs();
        if($result == 0) {

            if($this->aliasFile) {
                $result = $this->loadSCMAliasesFromFile($this->aliasFile);
            }

            if($result == 0) {
                $this->convertSVNXmlLogToDebianFormat($this->xmlfile);
            }

        }

        return $result;
    }

    private function convertSVNXmlLogToDebianFormat($xmlfile)
    {
        $xml = new SimpleXMLElement(file_get_contents($xmlfile));

        foreach ($xml->logentry as $xmlentry) {
            print $this->convertSVNXmlLogEntryToDebianFormat($xmlentry);
        }
    }

    private function loadSCMAliasesFromFile($aliasFile)
    {
        $this->aliases = array();

        $result = $this->validateInputFile($aliasFile);
        if($result == 0) {

            $data = file_get_contents($aliasFile);
            $rows = explode("\n", $data);

            foreach($rows as $row)
            {
                $row_data = explode('|', $row);
                if(count($row_data) >= 3) {
                    $aliasKey = trim($row_data[0]);
                    $fullName = trim($row_data[1]);
                    $email    = trim($row_data[2]);
                    $this->aliases[$aliasKey] = "{$fullName} <{$email}>";
                }
            }

        }

        return $result;
    }

    private function convertSVNXmlLogEntryToDebianFormat($xmlentry)
    {
        $revision = $xmlentry['revision'];
        $message  = $xmlentry->msg;
        $author   = $this->getAuthor((string)$xmlentry->author);
        $date     = $this->convertSVNDateToRFC2822($xmlentry->date);

        return "$this->package ($revision) all; urgency=low

  * $message

 -- $author  $date

";

    }

    private function getAuthor($scmName)
    {
        $result = $scmName;

        if($this->aliases)
        {
            if(array_key_exists($scmName, $this->aliases)) {
                $alias = $this->aliases[$scmName];
                if($alias)
                {
                    $result = $alias;
                }
            }
        }

        return $result;
    }

    private function convertSVNDateToRFC2822($svnDate)
    {
        $timezone = new DateTimeZone('UTC');
        $date = date_create_from_format('Y-m-d*H:i:s.u*', $svnDate, $timezone);

        return $date->format('D, jS F Y H:i:s O');
    }

    private function validateArgs()
    {
        $result = $this->validateArgCount();
        if($result == 0) {
            $this->package = $this->argv[1];
            $this->arch    = $this->argv[2];
            $this->xmlfile = $this->argv[3];

            if($this->argc > 4) {
                $this->aliasFile = $this->argv[4];
            }

            $result = $this->validateInputFile($this->xmlfile);
        }

        return $result;
    }

    private function validateArgCount()
    {
        if ($this->argc < 3) {
            $this->printUsage();
            return 1;
        }
    }

    private function validateInputFile($inputFile)
    {
        if(!file_exists($inputFile)) {
            print "Error: Input file ($inputFile) not found\n";
            return 1;
        }
    }

    private function printUsage()
    {
        print "Usage: {$this->argv[0]} <package-name> <architecture> </path/to/svn-log.xml> </path/to/optional/scm-alias-file>\n";
    }

}

if (isset($argv))
{
    $formatter = new SubversionToDebianChangelogFormatter($argv);
    exit($formatter->run());
}

?>
