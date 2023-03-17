<?php

declare(strict_types=1);

namespace margusk\Accessors\Attr;

/**
 * @author https://vk.com/ddosnik
 */

use Attribute;
use margusk\Accessors\Attr;
 
/** @api */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
class NotSerializable extends Attr
{}