<?php
/**
 * @see       https://github.com/zendframework/zend-db for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-db/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Db\TableGateway\Feature;

/**
 * EventFeature event constants.
 *
 * This moves the constants introduced in {@link https://github.com/zendframework/zf2/pull/7066}
 * into a separate interface that EventFeature implements; the change keeps
 * backwards compatibility, while simultaneously removing the need to add
 * another hard dependency to the component.
 */
interface EventFeatureEventsInterface
{
    const EVENT_PRE_INITIALIZE  = 'preInitialize';
    const EVENT_POST_INITIALIZE = 'postInitialize';

    const EVENT_PRE_SELECT      = 'preSelect';
    const EVENT_POST_SELECT     = 'postSelect';

    const EVENT_PRE_INSERT      = 'preInsert';
    const EVENT_POST_INSERT     = 'postInsert';

    const EVENT_PRE_DELETE      = 'preDelete';
    const EVENT_POST_DELETE     = 'postDelete';

    const EVENT_PRE_UPDATE      = 'preUpdate';
    const EVENT_POST_UPDATE     = 'postUpdate';
}
