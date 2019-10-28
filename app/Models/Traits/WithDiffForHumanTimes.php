<?php

namespace App\Models\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait WithDiffForHumanTimes
{
    public function __call($method, $arguments)
    {
        if (Str::endsWith($method, 'TimeagoAttribute')) {
            $date = $this->{Str::snake(substr($method, 3, -16))};

            if ($date instanceof Carbon) {
                $now = now();

                if ($date->diffInDays($now) <= 15) {
                    return $date->diffForHumans();
                }

                return $date->year == $now->year ? $date->format('m-d H:i') : $date->format('Y-m-d H:i');
            }

            return $date;
        }

        return parent::__call($method, $arguments);
    }
}
