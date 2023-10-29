<?php

declare(strict_types=1);
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */
namespace MsPro\Translatable\Contracts;

use ArrayAccess;
use Hyperf\Contract\Arrayable;

interface LocalesInterface extends Arrayable, ArrayAccess
{
    public function add(string $locale): void;

    public function all(): array;

    public function current(): string;

    public function forget(string $locale): void;

    public function get(string $locale): ?string;

    public function getCountryLocale(string $locale, string $country): string;

    public function getLanguageFromCountryBasedLocale(string $locale): string;

    public function getLocaleSeparator(): string;

    public function has(string $locale): bool;

    public function isLocaleCountryBased(string $locale): bool;
}
