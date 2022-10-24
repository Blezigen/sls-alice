<?php

namespace console\modules\install\actions;

use common\contracts\IBootstrapSetting;
use console\modules\install\Module;
use Redbox\PersonalSettings\models\Section;
use Redbox\PersonalSettings\models\Type;
use yii\base\Action;

class InstallSettingAction extends Action
{
    public $resources = [];

    public function initElement($collectionArray)
    {
        foreach ($collectionArray as $collectionKey => $collectionElements) {
            $section = Section::make($collectionKey);
            $section->title = $collectionElements['title'];
            $section->description = $collectionElements['description'] ?? '';
            \Yii::$app->settings->initSection($section);


            $settings = $collectionElements['settings'] ?? [];
            foreach ($settings as $settingKey => $settingValue) {
                $default = $settingValue['default'] ?? null;

                $type = Type::make($settingKey);
                $type->title = $settingValue['title'];
                $type->description = $settingValue['description'] ?? '';

                \Yii::$app->settings->initSetting($section->slug, $type);
                \Yii::$app->settings->setDefaultValue($section->slug, $type->slug, $default);

                $values = $settingValue['values'] ?? [];
                foreach ($values as $value) {
                    \Yii::$app->settings->setValue($section->slug, $type->slug, $value['value'], $value['summary']);
                }
            }
        }
    }

    public function run()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var Module $installModule */
            $installModule = \Yii::$app->getModule('install');

            $modules = \Yii::$app->getModules();
            foreach ($modules as $module) {
                if ($module instanceof IBootstrapSetting) {
                    $installModule->setSettings($module->getSettings());
                }
            }

            foreach ($this->resources as $collectionFile) {
                $collections = include realpath(\Yii::getAlias($collectionFile));
                $installModule->setSettings($collections);
            }

            if (!empty($installModule->settings)) {
                $this->initElement($installModule->settings);
                unset($collections);
            }

            \Yii::$app->settings->save();

            $transaction->commit();
        } catch (\Throwable $ex) {
            $transaction->rollBack();
            $this->controller->consoleLog("<error>{$ex->getMessage()}</error>\n<error>".json_encode([
                "message" => $ex->getMessage(),
                "code" => $ex->getCode(),
                "file" => $ex->getFile().":".$ex->getLine(),
                "trace" => $ex->getTrace()
            ], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), true)."</error>";
        }
        $this->controller->consoleLog('<value>Настройки установлены<value>', true);
    }
}
