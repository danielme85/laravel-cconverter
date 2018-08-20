<?php

namespace danielme85\CConverter;

class CurrencyProviderCache
{
    protected $instance;

    public function setInstance($apiSource, $instance) {
        $this->instance[$apiSource] = $instance;
    }

    public function getInstance($apiSource) {
        return $this->instance[$apiSource];
    }

}