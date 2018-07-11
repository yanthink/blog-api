# 模拟浏览器

## 安装

1) 打开终端执行下面命令:
```php
composer require yanthink/selenium="@dev"
```

2) 使用
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Yanthink\selenium\Browser;
use Yanthink\selenium\Selenium;

Selenium::useChromeDriver();
Selenium::enableAutoStartChromeDriver();

class Test extends Command
{
    public function handle(Selenium $selenium)
    {
        $selenium->browse(function (Browser $browser) {
            $browser->visit('http://localhost/foo')
                ->waitFor('#pass')
                ->type('pass', 'password')
                ->click('#sub')
                ->dump();
        });
    }
}
```