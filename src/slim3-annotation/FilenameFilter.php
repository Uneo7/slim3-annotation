<?php

namespace Slim3\Annotation;


class FilenameFilter extends FilesystemRegexFilter
{
    // Filter files against the regex
    public function accept() {
        return ( ! $this->isFile() || preg_match($this->regex, $this->getFilename()));
    }
}