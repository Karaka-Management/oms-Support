<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Support\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Support\Models;

use phpOMS\Localization\ISO639x1Enum;

/**
 * Ticket Attribute Type class.
 *
 * @package Modules\Support\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
class TicketAttributeType implements \JsonSerializable
{
    /**
     * Id
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Name/string identifier by which it can be found/categorized
     *
     * @var string
     * @since 1.0.0
     */
    public string $name = ''; // @todo: currently not filled, should be used as identifier or if not required removed (at the moment it seems like it is useless?!)

    /**
     * Which field data type is required (string, int, ...) in the value
     *
     * @var int
     * @since 1.0.0
     */
    public int $fields = 0;

    /**
     * Is a custom value allowed (e.g. custom string)
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $custom = false;

    public string $validationPattern = '';

    public bool $isRequired = false;

    /**
     * Localization
     *
     * @var string | TicketAttributeTypeL11n
     */
    protected string | TicketAttributeTypeL11n $l11n;

    /**
     * Possible default attribute values
     *
     * @var array
     */
    protected array $defaults = [];

    /**
     * Constructor.
     *
     * @param string $name Name/identifier of the attribute type
     *
     * @since 1.0.0
     */
    public function __construct(string $name = '')
    {
        $this->setL11n($name);
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
     * Set l11n
     *
     * @param string|TicketAttributeTypeL11n $l11n Tag article l11n
     * @param string                         $lang Language
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setL11n(string | TicketAttributeTypeL11n $l11n, string $lang = ISO639x1Enum::_EN) : void
    {
        if ($l11n instanceof TicketAttributeTypeL11n) {
            $this->l11n = $l11n;
        } elseif (isset($this->l11n) && $this->l11n instanceof TicketAttributeTypeL11n) {
            $this->l11n->title = $l11n;
        } else {
            $this->l11n        = new TicketAttributeTypeL11n();
            $this->l11n->title = $l11n;
            $this->l11n->setLanguage($lang);
        }
    }

    /**
     * @return string
     *
     * @since 1.0.0
     */
    public function getL11n() : string
    {
        return $this->l11n instanceof TicketAttributeTypeL11n ? $this->l11n->title : $this->l11n;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'fields'            => $this->fields,
            'custom'            => $this->custom,
            'validationPattern' => $this->validationPattern,
            'isRequired'        => $this->isRequired,
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
