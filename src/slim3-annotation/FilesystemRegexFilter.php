<?php

namespace Slim3\Annotation;

abstract class FilesystemRegexFilter extends \RecursiveRegexIterator
{

    protected $regex;

    public function __construct(\RecursiveIterator $it, $regex)
    {
        $this->regex = $regex;
        parent::__construct($it, $regex);
    }
}