<?php

namespace Tcds\Io\Ray;

enum RayEventStatus: string
{
    case pending = 'pending';
    case processed = 'processed';
    case failed = 'failed';
}
