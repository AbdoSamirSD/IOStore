<?php
namespace App\Services;

use Illuminate\Support\Str;

class CustomAddress
{
    public $city;
    public $region;
    public $street;

    public function __construct(?string $city = null, ?string $region = null, ?string $street = null)
    {
        $this->city = $city;
        $this->region = $region;
        $this->street = $street;
    }

    public function toArray(): array
    {
        return [
            'city' => $this->city,
            'region' => $this->region,
            'street' => $this->street,
        ];
    }

    public function toString(): string
    {
        $parts = [];
        if ($this->city) {
            $parts[] = $this->city;
        }
        if ($this->region) {
            $parts[] = $this->region;
        }
        if ($this->street) {
            $parts[] = $this->street;
        }
        return implode(', ', $parts);
    }

    public static function fromString(string $addressString): self
    {
        $parts = explode(', ', $addressString);
        $city = $parts[0] ?? null;
        $region = $parts[1] ?? null;
        $street = $parts[2] ?? null;

        return new self($city, $region, $street);
    }

    public static function fromJson(string $addressJson): self
    {
        $address = json_decode($addressJson, true);
        return new self(
            $address['city'] ?? null,
            $address['region'] ?? null,
            $address['street'] ?? null
        );
    }
}
