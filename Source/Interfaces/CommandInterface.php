<?php


namespace SourceManager\Interfaces;


interface CommandInterface
{

    public function execute($path = null);

}