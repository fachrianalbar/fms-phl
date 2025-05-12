<?php

namespace App\Helpers;

class DecodedDirectionPolyline
{
    public static function decoded($encoded)
    {
        $length = strlen($encoded);
        $index = 0;
        $points = [];
        $lat = 0;
        $lng = 0;

        while ($index < $length) {
            $shift = $result = 0x00;
            do {
                $byte = ord(substr($encoded, $index++)) - 63;
                $result |= ($byte & 0x1F) << $shift;
                $shift += 5;
            } while ($byte >= 0x20);
            $lat += ($result & 1 ? ~($result >> 1) : ($result >> 1));

            $shift = $result = 0x00;
            do {
                $byte = ord(substr($encoded, $index++)) - 63;
                $result |= ($byte & 0x1F) << $shift;
                $shift += 5;
            } while ($byte >= 0x20);
            $lng += ($result & 1 ? ~($result >> 1) : ($result >> 1));

            $points[] = [$lat * 1e-5, $lng * 1e-5];
        }

        return $points;
    }
}
