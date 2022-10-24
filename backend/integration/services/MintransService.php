<?php

namespace integration\services;

use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\HttpException;
use yii\console\Exception;
use integration\jobs\MintransJob;
use integration\models\mintrans\MintransShip;
use common\models\City;
use common\models\Tour;
use common\models\ShipNavigation;
use common\models\Order;
use common\models\ShipTeam;
use integration\helpers\Array2Xml;
use integration\helpers\Csv2Array;

/*
  Документация: 
    https://www.z-it.ru/projects/egis-otb/connect-egis-otb.php
    https://www.z-it.ru/upload/connect-egis-otb-docs/Requirements_ACDPDP_(SHIP)_rus.pdf

  Адрес тестовой площадки web-интерфейса АЦБПДП: http://test.portal-p.egis-otb.ru:9080/TransSecurityOperatorApp/ 

  Регламент информационного взаимодействия и другую справочную информацию можно получить по адресу http://www.z-it.ru/projects/egis-otb/regulations-more. 

  Пользователь тестовой зоны (Доступ к FTP серверу и к ПЗП-А. Для тестирования достаточно одного пользователя)

  Название перевозчика/СТИ:	СК "Волга" ООО
  ИД ЕГИС ОТБ перевозчика/СТИ:	31038
  Страна:	Россия
  Сегмент транспорта:	Море
  Размещение ПДП на FTP:	Да
  Размещение расписаний на FTP:	Да
  Размещение справочников на FTP:	Да
  Формат данных ПДП:	Csv
  Доступ к ПЗП-А:	Да
  Язык квитанций:	Русский

  195.60.221.253
  МоРе - 31003
  Сервер квитанций - 31008
*/

class MintransService extends Model
{
  public array $data = [];
  public int $routeType = 0;

  private string $fileId = "31038";
  private string $fileType = "PD";
  private string $fileSegment = "SHIP";
  private bool $fileComporess = false;

  private string $ftp_server;
  private string $ftp_port;
  private string $ftp_port_ack;

  private string $ftp_login;
  private string $ftp_pass;
  private string $ftp_dir = "";

  public function init()
  {
    parent::init();

    $params = \Yii::$app->params['mintrans'];

    $this->ftp_server = $params['ftp_server'];
    $this->ftp_port = $params['ftp_port'];
    $this->ftp_port_ack = $params['ftp_port_ack'];
    $this->ftp_login = $params['ftp_login'];
    $this->ftp_pass = $params['ftp_pass'];

    $this->fileId = $params['operatorId'];
  }

  public function send($data = null)
  {
    if ($data) {
      $this->data = $data;
    }

    if ($this->data && $this->prepareData() && ($filename = $this->saveToCsv())) {
      if ($zipfile = $this->compress($filename)) {
        if ($this->uploadFTP($zipfile)) {
          return basename($zipfile);
        }
      }
    }

    return false;
  }

  public function sendTours()
  {
    $tours = Tour::find()
      // ->byVersion()
      ->withoutTrashed()
      ->where([">=", "departure_dt", date("Y-m-d 00:00:00", strtotime("+1 day"))])
      ->andWhere(["<=", "departure_dt", date("Y-m-d 23:59:59", strtotime("+1 day"))])
      ->all();

    if (!$tours) {
      // throw new HttpException(404, 'Туры не найдены');
      self::addLog("Туры не найдены");
      throw new Exception('Туры не найдены');
    }

    return $this->send($tours);
  }

  public function sendTimetable()
  {
    $xml = $this->prepareDataTimetable();

    $this->ftp_dir = "FULL";
    $this->ftp_port = \Yii::$app->params['mintrans']['ftp_port_route'];

    if ($xml && ($filename = $this->saveXml($xml))) {
      if ($zipfile = $this->compress($filename)) {
        if ($this->uploadFTP($zipfile)) {
          return basename($zipfile);
        }
      }
    }

    return false;
  }

  public function sendStation()
  {
    $xml = $this->prepareDataStation();

    $this->ftp_dir = "Destination/FULL";
    $this->ftp_port = \Yii::$app->params['mintrans']['ftp_port_dictionary'];

    if ($xml && ($filename = $this->saveXml($xml))) {
      if ($zipfile = $this->compress($filename)) {
        if ($this->uploadFTP($zipfile)) {
          return basename($zipfile);
        }
      }
    }

    return false;
  }

  public function getResult($filename, $fileType = null)
  {
    if ($fileType) {
      $this->fileType = $fileType;
    }

    return $this->ackFTP($filename);
  }

  private function saveXml($xml)
  {
    $this->clearDir();
    $filename = $this->__getFileName("xml");
    $dir = $this->getDir();

    file_put_contents("{$dir}/{$filename}", "\xEF\xBB\xBF" . $xml);

    return $filename;
  }

  /*
        Данные по пассажирским перевозкам должны экспортироваться из ИС ПС в
        обменный файл формата CSV (Comma Separated Values) в соответствии с RFC 4180. В качестве
        разделительного символа должна использоваться точка с запятой. Первой строкой файла
        указывается порядок следования полей. Имена полей в заголовке файла являются
        регистрозависимыми.
    */
  private function saveToCsv()
  {
    $separator = ';';
    $this->clearDir();

    $filename = $this->__getFileName();
    $dir = $this->getDir();

    $buffer = fopen("{$dir}/{$filename}", 'w');
    fputs($buffer, chr(0xEF) . chr(0xBB) . chr(0xBF));

    foreach ($this->data as $val) {
      fputcsv($buffer, $val, $separator);
    }

    fclose($buffer);
    return $filename;
  }

  /*
        До начала передачи в АЦБПДП каждый отдельный CSV-файл должен быть
        подвергнут компрессии по алгоритму ZIP, формат «.ZIP File Format Specification, Version: 6.3.3».
        Именование передаваемых обменных файлов, подвергнутых компрессии, должно
        удовлетворять шаблону:
        csv_name.zip,
        где: csv_name — указанное выше имя CSV-файла, вместе с его расширением.
    */
  private function compress($filename)
  {
    $dir = $this->getDir();

    if ($this->fileComporess) {
      $zipfile = "{$dir}/{$filename}.zip";

      $zip = new \ZipArchive;
      $zip->open($zipfile, \ZipArchive::CREATE);
      $zip->addFile("{$dir}/{$filename}", $filename);
      $zip->close();

      return $zipfile;
    }

    return "{$dir}/{$filename}";
  }

  /*
        Именование CSV-файлов должно удовлетворять следующему шаблону: ID_YYYY_MM_DD_HH_mm_ss_mss.csv
        Дата и время, указанные в имени файла, должны соответствовать дате и времени его формирования
        в UTC часовом поясе.

        Пример 00000_2016_12_14_10_30_00_000
    */
  public function __getFileName($ext = "csv")
  {
    $date = date("Y_m_d_H_i_s_000");
    // $date = "0000_00_00_00";

    return "{$this->fileId}_{$date}.{$ext}";
  }

  /*
        Передача обменных файлов на прикладном уровне осуществляется в ходе
        регулярно проводимых сеансов передачи с FTP-серверами входного шлюза АЦБПДП. Сеанс
        передачи заключается в последовательном выполнении следующих операций:
        а) инициализация сеанса связи по инициативе ИС ПС с FTP-сервером входного шлюза
        АЦБПДП;
        б) загрузка обменных файлов средствами ИС ПС в установленную директорию
        FTP-сервера шлюза АЦБПДП;
        в) завершение сеанса связи по инициативе ИС ПС с передачей команды завершения
        сеанса связи по протоколу FTP.
    */
  private function uploadFTP($file)
  {
    $result = false;
    $delay = 60;
    $filename = basename($file);

    $conn_id = ftp_connect($this->ftp_server, $this->ftp_port);

    ftp_login($conn_id, $this->ftp_login, $this->ftp_pass);
    ftp_pasv($conn_id, true);

    if (ftp_put($conn_id, "{$this->ftp_dir}/{$filename}", $file, FTP_ASCII)) {
      $result = true;

      \Yii::$app->queue->delay($delay)->push(new MintransJob([
        'filename' => $filename,
      ]));
    }

    ftp_close($conn_id);

    return $result;
  }

  /*
        Приём обменных файлов на прикладном уровне осуществляется средствами шлюза
        АЦБПДП после завершения сеансов передачи данных из ИС ПС. Приём заключается в
        последовательном выполнении следующих операций:
        а) контроль загруженных обменных файлов средствами шлюза АЦБПДП;
        б) обработка полученных данных и формирование ответной квитанции с результатами
        обработки;
        в) размещение файла-квитанции на шлюзе АЦБПДП и удаление исходного файла.
        Имя файла квитанции формируется на основе шаблона:
        <type>_<segment>_<name>.ack
        где:
        <type> — тип данных (PD – персональные данные; TT - расписания; RD - справочники)
        <segment> — вид транспорта (AUTO — автомобильный)
        <name> — имя исходного файла (на который поступила квитанция).
        Пример имени файла квитанции:
        PD_AUTO_20000_2019_03_24_11_59_05_749.csv.zip.ack
        Файл квитанции содержит результаты обработки информации из обменного файла.
        Формат содержимого файла квитанции — XML. Язык передаваемых данных — русский.
        Используется кодировка UTF-8 согласно RFC 3629 и ISO/IEC 10646 Annex D без указания метки
        порядка байтов. Примеры файлов-квитанций приведены в Приложении 3. Схемы XML для
        формирования XML данных квитанции приводятся в файлах схем.
    */
  private function ackFTP($filename)
  {
    $conn_id = ftp_connect($this->ftp_server, $this->ftp_port_ack);
    ftp_login($conn_id, $this->ftp_login, $this->ftp_pass);
    ftp_pasv($conn_id, true);

    // Прочитать файл в переменную
    $file = "{$this->fileType}_{$this->fileSegment}_{$filename}.ack";
    $handle = fopen('php://temp', 'r+');
    $result = false;

    try {
      ftp_fget($conn_id, $handle, "{$this->ftp_dir}/{$file}", FTP_BINARY, 0);
      fseek($handle, 0);

      $fstats = fstat($handle);
      $contents = fread($handle, $fstats['size']);

      $xml = simplexml_load_string($contents, "SimpleXMLElement", LIBXML_NOCDATA);
      $json = json_encode($xml);
      $result = json_decode($json, TRUE);
    } catch (\Exception $e) {
      throw new \Exception("Error Processing Request");
    }

    fclose($handle);
    ftp_close($conn_id);

    return $result;
  }

  /*
      Получение городов и преобразование в формат минтранса
    */
  public function prepareDataStation()
  {
    $model = City::find()
      // ->byVersion()
      ->all();

    $result = [];

    foreach ($model as $data) {
      $city = Csv2Array::findCity($data->title);

      if (!$city)
        continue;

      $result[] = [
        "@tag" => "entry",
        "@attr" => [
          "sourceId" => $data->id,
          "xsi:type" => "imp:ImportedEntry",
        ],
        "@items" => [
          [
            "@tag" => "data",
            "@attr" => [
              "name" => $data->title,
              "latitude" => str_replace(".", ",", $city["geo_lat"]),
              "longitude" => str_replace(".", ",", $city["geo_lon"]),
              // "UTC" => "Europe/Moscow",
              // "UTC" => $city["timezone"],
              "nearestTown" => $data->title,
              // "shortLatName" => "Spb",
              // "shortName" => "Спб",
              // "otiCode" => "1234567890",
              "xsi:type" => "onsi-stat:ShipStation"
            ],
            "@items" => [
              [
                "@tag" => "actualPeriod",
                "@attr" => [
                  "from" => date('c'),
                  "to" => date('c', strtotime("+1 year")),
                  "xsi:type" => "dt:ImportDateTimePeriod"
                ]
              ],
              [
                "@tag" => "countryCode",
                "@attr" => [
                  "value" => "Российская Федерация",
                  "xsi:type" => "dt:SimpleDictionaryValue"
                ]
              ],
              // [
              //   "@tag" => "federalSubject",
              //   "@attr" => [
              //     "value" => $data->region ? $data->region->title : $city["region"],
              //     "xsi:type" => "dt:SimpleDictionaryValue"
              //   ]
              // ],
              [
                "@tag" => "okato",
                "@attr" => [
                  "value" => $city["okato"],
                  "xsi:type" => "dt:SimpleDictionaryValue"
                ]
              ],
              [
                "@tag" => "portType",
                "@text" => $data->has_station ? "true" : "false"
              ],
            ]
          ]
        ]
      ];
    }

    $rootArray = [
      "@tag" => "Import",
      "@attr" => [
        "xsi:type" => "imp:FullImport",
        "createdAt" => date("c"),
        "dataType" => "DESTINATION",
        "recordCount" => count($result),
        "transportSegment" => $this->fileSegment,
        "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
        "xmlns:dt" => "http://www.egis-otb.ru/datatypes/",
        "xmlns:imp" => "http://www.egis-otb.ru/gtimport/",
        "xmlns:onsi-stat" => "http://www.egis-otb.ru/data/onsi/stations/",
        "xsi:schemaLocation" => "http://www.egis-otb.ru/gtimport/ ru.egisotb.import.xsd http://www.egis-otb.ru/data/onsi/stations/ ru.egisotb.data.onsi.stations.xsd http://www.egis-otb.ru/datatypes/ ru.egisotb.datatypes.xsd"
      ]
    ];

    $xml = Array2Xml::createXml($result, $rootArray);

    return $xml;
  }
  /*
      Получение расписания и преобразование в формат минтранса
    */
  public function prepareDataTimetable()
  {
    $model = Tour::find()
      ->withoutTrashed()
      ->all();

    $result = [];

    foreach ($model as $data) {
      if (!$data->departure_dt || !$data->arrival_dt) {
        continue;
      }

      $navigation = $data->navigations;
      $routeName = [];

      if (!$navigation) {
        $navigation = ShipNavigation::find()
          // ->byVersion()
          ->where(["tour_id" => $data->id])
          ->all();
      }

      if (!$navigation)
        continue;

      $routePoints = [];

      foreach ($navigation as $key => $point) {
        $routeName[] = $point->city->title;

        $routePoints[] = [
          "@tag" => "routePoint",
          "@attr" => [
            "arriveTime" => date("c", strtotime($point->arrival_dt)), // время прибытия на остановку по расписанию (необязательный атрибут)
            "departTime" => date("c", strtotime($point->departure_dt)), // время отправления по расписанию (обязательный атрибут для всех остановок, кроме конечного пункта маршрута)
            // "stopTimeInterval" => "0", // Время стоянки в минутах. Необязательный атрибут. Обязательно использовать, если время стоянки больше суток.
            // "startDistance", // расстояние от начальной точки маршрута в километрах. Необязательный атрибут
            "pathIndex" => $key, // порядковый номер остановки на маршруте 
            // "timeFromStart" => "8490", // время следования до остановки из начального пункта маршрута в минутах
            "xsi:type" => "tt:ImportRoutePoint"
          ],
          "@items" => [
            [
              "@tag" => "station",
              "@attr" => [
                "value" => $point->city->title,
                "xsi:type" => "dt:SimpleDictionaryValue"
              ]
            ]
          ]
        ];
      }

      $routeStart = $routeName[0];
      $lastIndex = count($routeName) - 1;
      $routeEnd = $routeName[$lastIndex];

      $n = intval(date("N", strtotime($data->departure_dt)));
      $weekCalendar = substr_replace("0000000", '1', $n - 1, $n);

      $result[] = [
        "@tag" => "entry",
        "@attr" => [
          "sourceId" => $data->id,
          "xsi:type" => "imp:ImportedEntry",
        ],
        "@items" => [
          [
            "@tag" => "data",
            "@attr" => [
              "xsi:type" => "tt:CalendarTimetable"
            ],
            "@items" => [
              [
                "@tag" => "actualPeriod", // Период действия расписания маршрута
                "@attr" => [
                  "from" => date("c", strtotime($data->departure_dt)),
                  "to" => date("c", strtotime($data->arrival_dt)),
                  "xsi:type" => "dt:ImportDateTimePeriod"
                ]
              ],
              [
                "@tag" => "operator",
                "@attr" => [
                  "value" => $this->fileId // Элемент перевозчика.
                ]
              ],
              [
                "@tag" => "route", // Элемент маршрута
                "@attr" => [
                  "routeName" => implode(" - ", $routeName),
                  "xsi:type" => "tt:RouteHead"
                ],
                "@items" =>  array_merge($routePoints, [
                  [
                    "@tag" => "routeEnd",
                    "@attr" => [
                      "value" => $routeEnd,
                      "xsi:type" => "dt:SimpleDictionaryValue"
                    ]
                  ],
                  [
                    "@tag" => "routeStart",
                    "@attr" => [
                      "value" => $routeStart,
                      "xsi:type" => "dt:SimpleDictionaryValue"
                    ]
                  ]
                ])
              ],
              [
                "@tag" => "calendar",
                "@attr" => [
                  "xsi:type" => "tt:WeekCalendar",
                  "daymask" => $weekCalendar // Недельная маска календаря для указания дней недели, когда выполняется рейс.
                ]
              ]
            ]
          ]
        ]
      ];
    }

    $rootArray = [
      "@tag" => "Import",
      "@attr" => [
        "xsi:type" => "imp:FullImport",
        "createdAt" => date("c"),
        "dataType" => "TIMETABLE_PLAN",
        "recordCount" => count($result),
        "transportSegment" => $this->fileSegment,
        "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
        "xmlns:dt" => "http://www.egis-otb.ru/datatypes/",
        "xmlns:imp" => "http://www.egis-otb.ru/gtimport/",
        "xmlns:tt" => "http://www.egis-otb.ru/data/timetable/",
        "xmlns:onsi-stat" => "http://www.egis-otb.ru/data/onsi/stations/",
        "xsi:schemaLocation" => "http://www.egis-otb.ru/gtimport/ ru.egisotb.import.xsd http://www.egis-otb.ru/data/timetable/ ru.egisotb.data.timetable.xsd"
      ]
    ];

    $xml = Array2Xml::createXml($result, $rootArray);

    return $xml;
  }

  /*
      Преобразование массива в формат минтранса
    */
  public function prepareData()
  {
    $result = [];
    $header = [];

    // Перебор туров и преобразование туристов и команды
    foreach ($this->data as $tour) {
      if ($items = MintransShip::formatTour($tour)) {
        $result = array_merge($result, $items);
      }
    }

    if (!$result) {
      self::addLog("Нет данных для отправки", "warning");
      throw new Exception('Нет данных для отправки');
    }

    if (!$items[0]) {
      return false;
    }

    $header = array_keys($items[0]);

    if ($this->validateData($result)) {
      array_unshift($result, $header); // Добавляем заголовки в первую строку
      $this->data = $result;

      return $result;
    }

    return false;
  }

  /*
      Валидация данных
    */
  private function validateData($items)
  {
    $isValid = true;

    foreach ($items as $item) {
      $scenario = $item['rank'] ? MintransShip::SCENARIO_CREW : MintransShip::SCENARIO_TOURIST;

      $model = new MintransShip(['scenario' => $scenario]);
      $model->attributes = $item;

      if (!$model->validate()) {
        $isValid = false;
      }
    }

    return $isValid;
  }

  private function getDir()
  {
    return \Yii::getAlias('@integration-cdn') . "/mintrans";
  }

  private function clearDir()
  {
    $dir = $this->getDir();
    $files = FileHelper::findFiles($dir, ['only' => ['*.csv', '*.zip', '*.xml']]);

    foreach ($files as $file) {
      FileHelper::unlink($file);
    }

    return true;
  }

  public static function addLog($message = null, $function = "error")
  {
    if (!$message) {
      return null;
    }

    if (is_string($message)) {
      $message = [
        'description' => $message
      ];
    }

    \Yii::$function($message, "Mintrans");
  }
}
