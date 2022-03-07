<?php
namespace MaximoToLiveLocation;


class LiveLocationCategory {

    public string $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }
}