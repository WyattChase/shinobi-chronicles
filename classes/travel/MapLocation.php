<?php

class MapLocation {

    public int $location_id = 0;
    public string $name;
    public int $map_id;
    public int $x;
    public int $y;
    public string $background_image;
    public string $background_color;
    public int $pvp_allowed;
    public int $ai_allowed;
    public int $regen;
    public function __construct(array $location_data) {
        foreach ($location_data as $key => $value) {
            $this->$key = $value;
        }
    }
}