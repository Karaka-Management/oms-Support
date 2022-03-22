<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Support\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Support\Models;

use phpOMS\Contract\ArrayableInterface;
use phpOMS\Localization\ISO3166TwoEnum;
use phpOMS\Localization\ISO639x1Enum;

/**
 * Ticket attribute value class.
 *
 * The relation with the type/supplier is defined in the TicketAttribute class.
 *
 * @package Modules\Support\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
class TicketAttributeValue implements \JsonSerializable, ArrayableInterface
{
    /**
     * Id
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Type of the attribute
     *
     * @var int
     * @since 1.0.0
     */
    public int $type = 0;

    /**
     * Int value
     *
     * @var null|int
     * @since 1.0.0
     */
    public ?int $valueInt = null;

    /**
     * String value
     *
     * @var null|string
     * @since 1.0.0
     */
    public ?string $valueStr = null;

    /**
     * Decimal value
     *
     * @var null|float
     * @since 1.0.0
     */
    public ?float $valueDec = null;

    /**
     * DateTime value
     *
     * @var null|\DateTimeInterface
     * @since 1.0.0
     */
    public ?\DateTimeInterface $valueDat = null;

    /**
     * Is a default value which can be selected
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $isDefault = false;

    /**
     * Language
     *
     * @var string
     * @since 1.0.0
     */
    protected string $language = ISO639x1Enum::_EN;

    /**
     * Country
     *
     * @var string
     * @since 1.0.0
     */
    protected string $country = ISO3166TwoEnum::_USA;

    /**
     * Constructor.
     *
     * @param int    $type     Type
     * @param mixed  $value    Value
     * @param string $language Language
     *
     * @since 1.0.0
     */
    public function __construct(int $type = 0, $value = '', string $language = ISO639x1Enum::_EN)
    {
        $this->type     = $type;
        $this->language = $language;

        $this->setValue($value);
    }

    /**
     * Get id
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param int|string|float|\DateTimeInterface $value Value
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setValue($value) : void
    {
        if (\is_string($value)) {
            $this->valueStr = $value;
        } elseif (\is_int($value)) {
            $this->valueInt = $value;
        } elseif (\is_float($value)) {
            $this->valueDec = $value;
        } elseif ($value instanceof \DateTimeInterface) {
            $this->valueDat = $value;
        }
    }

    /**
     * Get value
     *
     * @return null|int|string|float|\DateTimeInterface
     *
     * @since 1.0.0
     */
    public function getValue() : mixed
    {
        if (!empty($this->valueStr)) {
            return $this->valueStr;
        } elseif (!empty($this->valueInt)) {
            return $this->valueInt;
        } elseif (!empty($this->valueDec)) {
            return $this->valueDec;
        } elseif ($this->valueDat instanceof \DateTimeInterface) {
            return $this->valueDat;
        }

        return null;
    }

    /**
     * Set language
     *
     * @param string $language Language
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setLanguage(string $language) : void
    {
        $this->language = $language;
    }

    /**
     * Get language
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getLanguage() : string
    {
        return $this->language;
    }

    /**
     * Set country
     *
     * @param string $country Country
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setCountry(string $country) : void
    {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getCountry() : string
    {
        return $this->country;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'        => $this->id,
            'type'      => $this->type,
            'valueInt'  => $this->valueInt,
            'valueStr'  => $this->valueStr,
            'valueDec'  => $this->valueDec,
            'valueDat'  => $this->valueDat,
            'isDefault' => $this->isDefault,
            'language'  => $this->language,
            'country'   => $this->country,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
