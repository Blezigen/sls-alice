<?php

namespace common;

use api\helpers\UrlRule;
use Yii;
use yii\base\BootstrapInterface;
use yii\console\Application;

abstract class AbstractModule extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @param $moduleID
     *
     * @return string[]|mixed
     */
    abstract public function routes($moduleID);

    public function bootstrap($app)
    {
        $urlManager = Yii::$app->getUrlManager();

        if (Yii::$app instanceof Application){
            $urlManager->scriptUrl = env("API_URL");
        }

        $moduleID = $this->id;

        foreach ($this->routes($moduleID) as $pattern => $route) {
            if (is_array($route)) {
                $urlManager->addRules([
                    $route,
                ]);
            } elseif (preg_match('/(?<verb>(?:(?:GET|PUT|POST|PATCH|DELETE|OPTIONS|HEAD)[,]?)+ )?(?<url>.*)/',
                    $pattern, $match) !== false
            ) {
                $rule = [
                    'class' => UrlRule::class,
                    'pattern' => $pattern,
                    'route' => $route,
                ];

                if (isset($match['verb'])) {
                    $rule['verb'] = explode(',', trim($match['verb']));
                }
                if (isset($match['url'])) {
                    $rule['pattern'] = trim($match['url']);
                }

                $urlManager->addRules([
                    $rule,
                ]);
            }
        }
    }
}
