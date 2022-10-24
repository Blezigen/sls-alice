<?php

namespace common\contracts;

interface IModelSoftDelete
{
    public const WITH_TRASHED = 0;
    public const WITHOUT_TRASHED = 1;
    public const ONLY_TRASHED = 2;

    public const EVENT_BEFORE_SOFT_DELETE = 'beforeSoftDelete';
    public const EVENT_AFTER_SOFT_DELETE = 'afterSoftDelete';
    public const EVENT_BEFORE_FORCE_DELETE = 'beforeForceDelete';
    public const EVENT_AFTER_FORCE_DELETE = 'beforeForceDelete';
    public const EVENT_BEFORE_RESTORE = 'beforeRestore';
    public const EVENT_AFTER_RESTORE = 'afterRestore';
}
