<?php

namespace Database\Seeders;

use App\Models\ProductSpecificationValue;
use App\Models\Specification;
use App\Models\SpecificationValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecificationValuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $values = [
            'cpu' => 'Intel Core i3, Intel Core i5, Intel Core i7, Intel Core i9, AMD Ryzen 3, AMD Ryzen 5, AMD Ryzen 7, AMD Ryzen 9, Apple M1, Apple M2',
            'cpu_speed' => '1.1 GHz, 1.6 GHz, 2.0 GHz, 2.4 GHz, 2.8 GHz, 3.0 GHz, 3.4 GHz',
            'cpu_generation' => '2nd Gen, 3rd Gen, 4th Gen, 6th Gen, 7th Gen, 8th Gen, 10th Gen, 11th Gen, 12th Gen, 13th Gen, 14th Gen',
            'cpu_series' => 'U-Series, H-Series, G-Series, K-Series, X-Series, P-Series',

            'gpu_type' => 'Integrated, Dedicated, Hybrid',
            'gpu_brand' => 'NVIDIA, AMD, Intel',
            'gpu_model' => 'NVIDIA GTX 1650, NVIDIA RTX 3050, NVIDIA RTX 3060, NVIDIA RTX 4060, AMD Radeon RX 5600M, Intel Iris Xe',
            'gpu_memory' => '2 GB, 4 GB, 6 GB, 8 GB, 12 GB, 16 GB',

            'ram_type' => 'DDR3, DDR4, DDR5, LPDDR4, LPDDR5',
            'ram_capacity' => '4 GB, 8 GB, 12 GB, 16 GB, 24 GB, 32 GB, 64 GB',
            'ram_speed' => '2133 MHz, 2400 MHz, 2666 MHz, 2933 MHz, 3200 MHz, 3600 MHz, 4266 MHz',
            'ram_slots' => '1, 2, 4',

            'storage_type' => 'HDD, SSD, Hybrid, eMMC, NVMe SSD',
            'storage_capacity' => '128 GB, 256 GB, 512 GB, 1 TB, 2 TB',
            'storage_speed' => '5400 RPM, 7200 RPM, Read 3500 MB/s, Write 3000 MB/s',

            'display_size' => '11.6", 13.3", 14.0", 15.6", 16.0", 17.3"',
            'display_resolution' => 'HD (1366x768), Full HD (1920x1080), 2K (2560x1440), 4K UHD (3840x2160)',
            'display_refresh_rate' => '60 Hz, 75 Hz, 120 Hz, 144 Hz, 165 Hz, 240 Hz',

            'battery_capacity' => '35 Wh, 45 Wh, 50 Wh, 56 Wh, 65 Wh, 70 Wh, 99 Wh',
            'battery_life' => '4 hours, 6 hours, 8 hours, 10 hours, 12+ hours',

            'material' => 'Plastic, Aluminum, Magnesium Alloy, Carbon Fiber',
            'dimensions' => '12.5 x 8.5 x 0.7 in, 14.1 x 9.7 x 0.8 in, 15.0 x 10.2 x 0.9 in',

            'ports' => 'USB-A, USB-C, HDMI, Ethernet, SD Card Reader, Audio Jack, Thunderbolt 3, Thunderbolt 4',
            'connectivity' => 'Wi-Fi 5, Wi-Fi 6, Wi-Fi 6E, Bluetooth 4.2, Bluetooth 5.0, Bluetooth 5.2',

            'audio' => 'Stereo Speakers, Dolby Atmos, Bang & Olufsen, Harman Kardon',
            'keyboard' => 'Standard, Backlit, RGB Backlit, Mechanical',
            'touchpad' => 'Standard, Precision Touchpad, Glass Surface, Multi-Gesture Support',

            'camera' => '720p HD, 1080p FHD, Infrared (IR), No Camera',
            'microphone' => 'Single Mic, Dual Mic, Noise Cancelling Mic',
            'speakers' => 'Stereo, Quad Speakers, Dolby Audio, Bang & Olufsen',

            'os' => 'Windows 10, Windows 11, Ubuntu, macOS, FreeDOS, No OS',
            'weight' => '1.1 kg, 1.5 kg, 1.8 kg, 2.0 kg, 2.3 kg, 2.5 kg',
            'color' => 'Black, Silver, Gray, White, Blue, Red',
            'warranty' => '3 Months, 6 Months, 1 Year, 2 Years, 3 Years',

        ];

        foreach ($values as $specification => $valueList) {

            $specs = Specification::where('name', $specification)->first();
            if (!$specs) {
                $this->command->warn("Specification '{$specification}' not found. Skipping.");
                continue;
            }

            $valueArray = explode(', ', $valueList);
            foreach ($valueArray as $value) {
                SpecificationValue::create([
                    'specification_id' => $specs->id,
                    'value' => trim($value),
                ]);
            }
        }
    }
}
