<?php

namespace Model;

use \System\Emerald\Emerald_enum;

class Transaction_type extends Emerald_enum
{
    const REFILL='refill';
    const BUY='buy';
}