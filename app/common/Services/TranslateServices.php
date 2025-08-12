<?php

namespace app\common\Services;

use Volc\Base\V4Curl;
use think\response\Json;

/**
 * 火山翻译服务
 * Class TranslateServices
 * @package app\common\Services
 */
class TranslateServices extends V4Curl {

  /**
   * API配置
   */
  protected $apiList = [
    'LangDetect' => [
      'url' => '/',
      'method' => 'post',
      'config' => [
        'query' => [
          'Action' => 'LangDetect',
          'Version' => '2020-06-01',
        ],
      ],
    ],
    'TranslateText' => [
      'url' => '/',
      'method' => 'post',
      'config' => [
        'query' => [
          'Action' => 'TranslateText',
          'Version' => '2020-06-01',
        ],
      ],
    ],
  ];

  /**
   * 获取配置信息
   */
  protected function getConfig(string $region) {
    return [
      'host' => '//open.volcengineapi.com',
      'config' => [
        'timeout' => 5.0,
        'headers' => [
          'Accept' => 'application/json'
        ],
        'v4_credentials' => [
          'region' => 'cn-north-1',
          'service' => 'translate',
        ],
      ],
    ];
  }

  /**
   * 转换翻译内容
   */
  public function langDetect(array $textList): array {

    $req = array('TextList' => $textList);
    try {
      $resp = $this -> request('LangDetect', ['json' => $req]);
    } catch (\Throwable $e) {
      throw $e;
    }
    if ($resp -> getStatusCode() != 200) {
      return ['code' => 0, 'data' => ['code' => 0, 'message' => 'failed to detect language: status_code=%d, resp=%s', 'status' => $resp -> getStatusCode(), 'body' => $resp -> getBody()]];
    }
    return json_decode($resp -> getBody() -> getContents(), true)['DetectedLanguageList'];

  }

  /**
   * 翻译文字
   */
  public function translateText(string $sourceLanguage, string $targetLanguage, array $textList): array {

    $req = array('SourceLanguage' => $sourceLanguage, 'TargetLanguage' => $targetLanguage, 'TextList' => $textList);
    try {
      $resp = $this -> request('TranslateText', ['json' => $req]);
    } catch (\Throwable $e) {
      throw $e;
    }
    if ($resp -> getStatusCode() != 200) {
      return ['code' => 0, 'data' => ['code' => 0, 'message' => 'failed to translate: status_code=%d, resp=%s', 'status' => $resp -> getStatusCode(), 'body' => $resp -> getBody()]];
    }
    $result = json_decode($resp -> getBody() -> getContents(), true);
    if (isset($result['TranslationList'])) {
      return $result['TranslationList'];
    } else {
      return ['code' => 0, 'data' => ['code' => 0, 'message' => 'failed to translate: ' . $result['ResponseMetadata']['Error']['Message']]];
    }
  }

}