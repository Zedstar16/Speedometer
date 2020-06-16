# Speedometer

- This plugin can also be used as an API plugin
- By default the plugin shows the speed in m/s of a player when they have elytra equipped, there are configurable options, see Config

## API:
Method:
```php
static function calculateSpeed(Player $p, int $precision = 2): ?float
```
Example Usage:
```php
use Zedstar16/Speedometer/Speedometer;

/** var Player $player */
$speed = Speedometer::calculateSpeed($player, 1);
$player->sendTip("Speed: $speed");
```

### Config
- display-speed - If you do not want player speed to show up so you can use this as an API plugin, set the value to false
- display-only-with-elytra - Should either be true or false, if false then speed will always be displayed
- display-type - Configure how the speed will show to the player with options popup, tip, action-bar
