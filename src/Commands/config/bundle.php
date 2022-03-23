<?php

return [
//    new \ZnLib\Fixture\Bundle(['container', 'console']),
    new \ZnDatabase\Fixture\Bundle(['container', 'console']),
    new \ZnDatabase\Base\Bundle(['container']),
    new \ZnDatabase\Tool\Bundle(['container', 'console']),
//    new \ZnDatabase\Migration\Bundle(['container', 'console']),
//    new \ZnTool\Package\Bundle(['container', 'console']),
//    new \ZnTool\Phar\Bundle(['container', 'console']),
];