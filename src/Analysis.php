<?php

declare(strict_types=1);

namespace ibibicloud\douyin;

use ibibicloud\facade\HttpClient;
use ibibicloud\douyin\facade\FilterData;

class Analysis
{
	public function getConfig(): array
	{
		return config('douyin');
	}

	// 解析 Curl Bash 为 HttpClient 请求 headers
	public function parseCurlBash2Headers(): array
	{
		// 读取 douyin curlBash 文件
		$curlBash = file_get_contents(app()->getRootPath() . '/config/douyin.txt');
		// 替换 -b 参数为 -b cookie 标准Cookie头格式
	    $curlBash = preg_replace('/-b \'/', '-b \'Cookie: ', $curlBash);
	    
	    // 用于匹配头信息的正则表达式
	    $headerRegex = '/-(H|b) \'([^:\']+):\s*([^\']*)\'/';
	    
	    $headers = [];
	    preg_match_all($headerRegex, $curlBash, $matches, PREG_SET_ORDER);

	    foreach ( $matches as $key => $match ) {
	        $key = trim($match[2]);
	        $value = trim($match[3]);
	        // $headers[$key] = $value;
	        $headers[] = $key . ': ' . $value;
	    }
	    
	    return $headers;
	}

	// 获取我的抖音关注列表数据
	public function getMyfollowingData(int $offset = 0, bool $raw = true): array
	{
		$config = $this->getConfig();
		$bizParams = $config['bizParams'];
	    $params = [
	        'device_platform'	=> $bizParams['device_platform'],
	        'channel'			=> $bizParams['channel'],
	        'aid'				=> $bizParams['aid'],
	        'sec_user_id'		=> $config['mySecUserId'],
	        'source_type'		=> '4',
	        'offset'			=> $offset,   // 必须配合 source_type=4 使用
	        'count'				=> '20'
	    ];
	    $response = HttpClient::get($config['api']['following'], $params, $this->parseCurlBash2Headers());
	    $res = json_decode($response['body'], true);

	    return $raw ? $res : FilterData::myFollowingData($res);
	}

	// 获取我的抖音收藏数据-音乐
	public function getMyAudioCollectionData(string $cursor = '0', bool $raw = true): array
	{
		$config = $this->getConfig();
		$bizParams = $config['bizParams'];
		$params = [
			'device_platform'	=> $bizParams['device_platform'],
	        'channel'			=> $bizParams['channel'],
	        'aid'				=> $bizParams['aid'],
			'cursor'			=> $cursor,
			'count'				=> 20,
		];
		$response = HttpClient::get($config['api']['audio_collection'], $params, $this->parseCurlBash2Headers());
		$res = json_decode($response['body'], true);

		return $raw ? $res : FilterData::myAudioCollectionData($res);
	}

	// 获取UP主的相关信息
	public function getAuthorInfoData(string $sec_user_id, bool $raw = true): array
	{
		$config = $this->getConfig();
		$bizParams = $config['bizParams'];
	    $params = [
	        'device_platform'	=> $bizParams['device_platform'],
	        'channel'			=> $bizParams['channel'],
	        'aid'				=> $bizParams['aid'],
	        'sec_user_id'		=> $sec_user_id ,
	    ];

		$response = HttpClient::get($config['api']['userProfile'], $params, $this->parseCurlBash2Headers());
		$res = json_decode($response['body'], true);

	    return $raw ? $res : FilterData::authorInfoData($res);
	}

	// 获取作者的视频列表
	public function getAuthorVideoListData(string $sec_user_id, string $max_cursor = '0', int $count = 30, bool $raw = true): array
	{
		$config = $this->getConfig();
		$bizParams = $config['bizParams'];
		$params = [
	        'device_platform'	=> $bizParams['device_platform'],
	        'channel'			=> $bizParams['channel'],
	        'aid'				=> $bizParams['aid'],
	        'sec_user_id'		=> $sec_user_id,	// UP主的抖音ID
	        'max_cursor'		=> $max_cursor,
	        'count'				=> $count,
	        'need_time_list'	=> '1',
	        'from_user_page'	=> '1'
	    ];
	    $response = HttpClient::get($config['api']['videoList'], $params, $this->parseCurlBash2Headers());
	    $res = json_decode($response['body'], true);

	    return $raw ? $res : FilterData::authorVideoListData($res);
	}

	// 获取抖音创作中心音频数据
	public function getCreatorAudioData(string $type, int $cursor = 0, $count = 20): array
	{
		$config = $this->getConfig();
		$bizParams = $config['bizParams'];

		$typeData = [
			'推荐'	=> ['type' => 'recommend', 'category_id' => '1'],
			'热门榜'	=> ['type' => 'rank', 'category_id' => '7088298745502646280'],
			// '收藏'	=> ['type' => 'fav', 'category_id' => '1'],
			'飙升榜'	=> ['type' => 'rank', 'category_id' => '7088297994563059748'],
			'原创榜'	=> ['type' => 'rank', 'category_id' => '6854399861215747336'],
			'卡点'	=> ['type' => 'category', 'category_id' => '7395823327471782694'],
			'纯音乐'	=> ['type' => 'category', 'category_id' => '7397340776264420134'],
			'旅行'	=> ['type' => 'category', 'category_id' => '7397321654973549338'],
			'DJ'	=> ['type' => 'category', 'category_id' => '7395861511152864050'],
			'搞笑'	=> ['type' => 'category', 'category_id' => '7397653031963167526'],
			'流行'	=> ['type' => 'category', 'category_id' => '7397326893978405683'],
			'伤感'	=> ['type' => 'category', 'category_id' => '7397328346998213386'],
		];

		$queryData = [
			'type'			=> $typeData[$type]['type'],
			'category_id'	=> $typeData[$type]['category_id'],
			'cursor'		=> $cursor,
			'count'			=> $count,
		];

		$params = [
	        'device_platform'	=> $bizParams['device_platform'],
	        'channel'			=> $bizParams['channel'],
	        'aid'				=> $bizParams['aid'],
	    ];

		$response = HttpClient::get($config['api']['creator_audio'] . '?' . http_build_query($queryData), $params, $this->parseCurlBash2Headers());
		$res = json_decode($response['body'], true);

		return $res ?? [];
	}

}