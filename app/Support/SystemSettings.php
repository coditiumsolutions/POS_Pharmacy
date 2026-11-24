<?php

namespace App\Support;

use App\Models\Configuration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SystemSettings
{
    public const TOP_NAVBAR_TITLE = 'system.top_navbar_title';
    public const PRINT_SLIP_HEADING = 'system.print_slip_heading';
    public const PRINT_SLIP_PHONE = 'system.print_slip_phone';
    public const PRINT_SLIP_IMAGE = 'system.print_slip_image';
    public const NAVBAR_PRIMARY_COLOR = 'system.navbar_primary_color';
    public const NAVBAR_SECONDARY_COLOR = 'system.navbar_secondary_color';

    public static function defaults(): array
    {
        return [
            self::TOP_NAVBAR_TITLE => 'LipSip POS',
            self::PRINT_SLIP_HEADING => 'LipSip Mart',
            self::PRINT_SLIP_PHONE => '0339-5470747',
            self::PRINT_SLIP_IMAGE => '',
            self::NAVBAR_PRIMARY_COLOR => '#0f172a',
            self::NAVBAR_SECONDARY_COLOR => '#d4a017',
        ];
    }

    /**
     * Preset pairs: top/side navbar (primary) + second top navbar (secondary).
     *
     * @return list<array{id:string,primary:string,secondary:string,label:string}>
     */
    public static function colorCombinations(): array
    {
        return [
            [
                'id' => 'default',
                'primary' => '#0f172a',
                'secondary' => '#d4a017',
                'label' => 'Default (Slate / Gold)',
            ],
            [
                'id' => 'navy-amber',
                'primary' => '#102944',
                'secondary' => '#ffb20b',
                'label' => 'Navy / Amber',
            ],
            [
                'id' => 'teal-sky',
                'primary' => '#00537A',
                'secondary' => '#A8E8F9',
                'label' => 'Teal / Sky',
            ],
            [
                'id' => 'ocean-orange',
                'primary' => '#013C58',
                'secondary' => '#F5A201',
                'label' => 'Ocean / Orange',
            ],
            [
                'id' => 'indigo-gold',
                'primary' => '#0A174E',
                'secondary' => '#F5D042',
                'label' => 'Indigo / Gold',
            ],
            [
                'id' => 'charcoal-yellow',
                'primary' => '#101820',
                'secondary' => '#FEE715',
                'label' => 'Charcoal / Yellow',
            ],
            [
                'id' => 'deepnavy-mint',
                'primary' => '#00203F',
                'secondary' => '#ADEFD1',
                'label' => 'Deep Navy / Mint',
            ],
            [
                'id' => 'black-neon',
                'primary' => '#0A0A0A',
                'secondary' => '#39FF14',
                'label' => 'Black / Neon Green',
            ],
            [
                'id' => 'forest-sage',
                'primary' => '#2C5F2D',
                'secondary' => '#97BC62',
                'label' => 'Forest / Sage',
            ],
            [
                'id' => 'espresso-coral',
                'primary' => '#2D2926',
                'secondary' => '#ED6F63',
                'label' => 'Espresso / Coral',
            ],
        ];
    }

    public static function normalizeHexColor(?string $value, string $fallback): string
    {
        $value = strtoupper(trim((string) $value));
        if (preg_match('/^#[0-9A-F]{6}$/', $value)) {
            return $value;
        }

        return strtoupper($fallback);
    }

    public static function findColorCombination(?string $primary, ?string $secondary): ?array
    {
        $primary = self::normalizeHexColor($primary, '');
        $secondary = self::normalizeHexColor($secondary, '');

        foreach (self::colorCombinations() as $combo) {
            if (
                strcasecmp($combo['primary'], $primary) === 0
                && strcasecmp($combo['secondary'], $secondary) === 0
            ) {
                return $combo;
            }
        }

        return null;
    }

    public static function resolveColorCombination(?string $id): array
    {
        $id = trim((string) $id);
        foreach (self::colorCombinations() as $combo) {
            if ($combo['id'] === $id) {
                return $combo;
            }
        }

        return self::colorCombinations()[0];
    }

    public static function get(string $key, ?string $default = null): string
    {
        $defaults = self::defaults();
        $fallback = $default ?? ($defaults[$key] ?? '');

        try {
            if (!Schema::hasTable('configurations')) {
                return $fallback;
            }
        } catch (\Throwable $e) {
            return $fallback;
        }

        return Cache::remember('syscfg.' . $key, 60, function () use ($key, $fallback) {
            $value = Configuration::query()
                ->where('config_key', $key)
                ->value('config_value');

            if ($value === null || trim((string) $value) === '') {
                return $fallback;
            }

            return (string) $value;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        Configuration::updateOrCreate(
            ['config_key' => $key],
            ['config_value' => $value ?? '']
        );
        Cache::forget('syscfg.' . $key);
    }

    public static function topNavbarTitle(): string
    {
        return self::get(self::TOP_NAVBAR_TITLE);
    }

    public static function printSlipHeading(): string
    {
        return self::get(self::PRINT_SLIP_HEADING);
    }

    public static function printSlipPhone(): string
    {
        return self::get(self::PRINT_SLIP_PHONE);
    }

    public static function printSlipImage(): string
    {
        return self::get(self::PRINT_SLIP_IMAGE, '');
    }

    public static function printSlipImageUrl(): ?string
    {
        $path = self::printSlipImage();
        if ($path === '') {
            return null;
        }

        return asset($path);
    }

    public static function navbarPrimaryColor(): string
    {
        $defaults = self::defaults();

        return self::normalizeHexColor(
            self::get(self::NAVBAR_PRIMARY_COLOR),
            $defaults[self::NAVBAR_PRIMARY_COLOR]
        );
    }

    public static function navbarSecondaryColor(): string
    {
        $defaults = self::defaults();

        return self::normalizeHexColor(
            self::get(self::NAVBAR_SECONDARY_COLOR),
            $defaults[self::NAVBAR_SECONDARY_COLOR]
        );
    }

    public static function activeColorCombination(): array
    {
        $match = self::findColorCombination(
            self::navbarPrimaryColor(),
            self::navbarSecondaryColor()
        );

        return $match ?? self::colorCombinations()[0];
    }
}
