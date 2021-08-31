<?php
// .phpstorm.meta.php

namespace PHPSTORM_META {

    use Ep\Contract\InjectorInterface;
    use Psr\EventDispatcher\EventDispatcherInterface;

    override(
        InjectorInterface::make(0),
        map([
            '' => '@'
        ])
    );

    override(
        EventDispatcherInterface::dispatch(0),
        type(0)
    );
}
