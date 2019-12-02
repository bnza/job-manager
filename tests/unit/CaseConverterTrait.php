<?php

namespace Bnza\JobManagerBundle\Tests;

use Jawira\CaseConverter\CaseConverter;

trait CaseConverterTrait
{
    /**
     * @var CaseConverter
     */
    private static $caseConverter;
    
    /**
     * @return void
     */
    private static function setUpCaseConverter()
    {
        self::$caseConverter = new CaseConverter();
    }
}
