<?php

namespace App\Services;

use Illuminate\Support\Str;

class CustomTripDuration
{

    public $days;
    public $nights;
    public $hours;

    public function __construct(?int $days = null, ?int $nights = null, ?int $hours = null)
    {
        $this->days = $days;
        $this->nights = $nights;
        $this->hours = $hours;
    }

    public function toArray(): array
    {
        return [
            'days' => $this->days,
            'nights' => $this->nights,
            'hours' => $this->hours,
        ];
    }

    public function toString(): string
    {
        $parts = [];
        if ($this->days > 0) {
            $parts[] = $this->days . ' ' . Str::plural('Day', $this->days);
        }
        if ($this->nights > 0) {
            $parts[] = $this->nights . ' ' . Str::plural('Night', $this->nights);
        }
        if ($this->hours > 0) {
            $parts[] = $this->hours . ' ' . Str::plural('Hour', $this->hours);
        }
        return implode(' - ', $parts);
    }

    public static function fromString(string $durationString): self
    {
        $parts = preg_split('/\s+-\s+/', $durationString);
        $days = null;
        $nights = null;
        $hours = null;
        foreach ($parts as $part) {
            $matches = [];
            if (preg_match('/(\d+)\s+(Day|Night|Hour)/', $part, $matches)) {
                $value = (int) $matches[1];
                switch ($matches[2]) {
                    case 'Day':
                        $days = $value;
                        break;
                    case 'Night':
                        $nights = $value;
                        break;
                    case 'Hour':
                        $hours = $value;
                        break;
                }
            }
        }
        return new self($days, $nights, $hours);
    }
}
