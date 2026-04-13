<?php

namespace ibibicloud\douyin;

use ibibicloud\facade\FormatUnit;

// 抖音数据过滤
class FilterData
{
    // 我的关注列表数据
    public function following($json) {
        $json = json_decode($json['body'], true);
        
        // 从原始对象中提取必须要的字段
        $followings = $json['followings'] ?? [];        // 我的关注 数组
        $hasMore    = $json['has_more'] ?? false;       // 下拉还有吗？
        $offset     = $json['offset'] ?? 0;             // 偏移量
        $total      = $json['total'] ?? 0;              // 我的关注总数

        $filterFollowing = [];
        foreach ( $followings as $item ) {
            $filterFollowing[] = [
                'nickname'          => $item['nickname'] ?? '',                                 // UP主的昵称
                'avatar_300x300'    => $item['avatar_300x300']['url_list'][0] ?? '',            // UP主的头像 300x300
                'avatar_larger'     => $item['avatar_larger']['url_list'][0] ?? '',             // UP主的头像 large
                'sec_user_id'       => $item['sec_uid'] ?? '',                                  // UP主的抖音 长字符串ID
                'unique_id'         => $item['unique_id'] ?? '',                                // UP主的抖音号
                'uid'               => $item['uid'] ?? '',
                'language'          => $item['language'] ?? '',
                'aweme_count'       => $item['aweme_count'] ?? 0,                               // UP主的作品数量
                'create_time'       => date('Y-m-d', $item['create_time']),                     // UP主的账号注册时间
                'favoriting_count'  => $item['favoriting_count'] ?? 0,
                'follower_count'    => FormatUnit::number2CN($item['follower_count'] ?? 0),     // UP主的粉丝数
                'follower_count_o'  => $item['follower_count'] ?? 0,
                'following_count'   => FormatUnit::number2CN($item['following_count'] ?? 0),    // UP主的他关注别的UP主的数
                'following_count_o' => $item['following_count'] ?? 0,
                'total_favorited'   => FormatUnit::number2CN($item['total_favorited'] ?? 0),    // UP主的总获赞数
                'total_favorited_o' => $item['total_favorited'] ?? 0,
                'short_id'          => $item['short_id'] ?? '',
                'constellation'     => $item['constellation'] ?? '',                            // UP主的星座
                'signature'         => $item['signature'] ?? '',                                // UP主的个人描述简介
            ];
        }

        return [
            'following' => $filterFollowing,
            'has_more'  => $hasMore,
            'offset'    => $offset,
            'total'     => $total,
        ];
    }

    // UP主的相关信息
    public function authorInfoData($json) {
        $json = json_decode($json['body'], true);
        
        // 从原始对象中提取必须要的字段
        $user = $json['user'] ?? [];

        $filterUser = [
            'nickname'          => $user['nickname'] ?? '',                         // 昵称
            'avatar_168x168'    => $user['avatar_168x168']['url_list'][0] ?? '',
            'avatar_300x300'    => $user['avatar_300x300']['url_list'][0] ?? '',
            'avatar_larger'     => $user['avatar_larger']['url_list'][0] ?? '',
            'signature'         => $user['signature'] ?? '',                        // 个性签名
            'aweme_count'       => $user['aweme_count'] ?? 0,                       // 作品数
            'follower_count'    => $user['follower_count'] ?? 0,                    // 粉丝数
            'following_count'   => $user['following_count'] ?? 0,                   // 他的关注别UP主的数量
            'total_favorited'   => $user['total_favorited'] ?? 0,                   // 总获赞数
            'sec_user_id'       => $user['sec_uid'] ?? '',                          // sec_user_id
            'uid'               => $user['uid'] ?? '',                              // uid
            'unique_id'         => $user['unique_id'] ?? '',                        // uid
            'user_age'          => $user['user_age'] ?? 0,                          // 年龄
        ];

        return $filterUser;
    }

    // 作者的视频列表
    public function authorVideoList($json) {
        $json = json_decode($json['body'], true);

        // 从原始数组中提取必须要的字段
        $awemeList  = $json['aweme_list'] ?? [];        // UP主的作品列表
        $timeList   = $json['time_list'] ?? [];         // UP主的作品时间轴列表
        $hasMore    = $json['has_more'] ?? 0;           // 下拉还有吗？
        $minCursor  = $json['min_cursor'] ?? 0;         // 时间区间作品筛选 - 起始时间
        $maxCursor  = $json['max_cursor'] ?? 0;         // 时间区间作品筛选 - 结束时间

        $filterVideoList = [];
        foreach ( $awemeList as $item ) {
            $video  = [];
            $images = [];
            $isVideoOrImages = 'video';

            // 判断是视频还是图集
            if ( !empty($item['video']['bit_rate']) ) {
                $video = [
                    'cover'         => !empty($item['video']['cover']['url_list']) ? end($item['video']['cover']['url_list']) : '',
                    'ratio'         => $item['video']['ratio'] ?? '',
                    'duration'      => FormatUnit::duration($item['duration'] ?? 0),
                    'width'         => $item['video']['width'] ?? 0,
                    'height'        => $item['video']['height'] ?? 0,
                    'play_url'      => !empty($item['video']['play_addr']['url_list']) ? end($item['video']['play_addr']['url_list']) : '',
                ];

                // H264压缩
                if ( !empty($item['video']['play_addr_h264']) ) {
                    $video['play_addr_h264'] = [
                        'width'     => $item['video']['play_addr_h264']['width'] ?? 0,
                        'height'    => $item['video']['play_addr_h264']['height'] ?? 0,
                        'data_size' => FormatUnit::fileSize($item['video']['play_addr_h264']['data_size'] ?? 0), // 修复这里
                        'play_url'  => !empty($item['video']['play_addr_h264']['url_list']) ? end($item['video']['play_addr_h264']['url_list']) : '',
                    ];
                }

                // H265压缩
                if ( !empty($item['video']['play_addr_265']) ) {
                    $video['play_addr_265'] = [
                        'width'     => $item['video']['play_addr_265']['width'] ?? 0,
                        'height'    => $item['video']['play_addr_265']['height'] ?? 0,
                        'data_size' => FormatUnit::fileSize($item['video']['play_addr_265']['data_size'] ?? 0), // 修复这里
                        'play_url'  => !empty($item['video']['play_addr_265']['url_list']) ? end($item['video']['play_addr_265']['url_list']) : '',
                    ];
                }

                // 视频码率信息
                $bitRateList = [];
                if ( !empty($item['video']['bit_rate']) ) {
                    foreach ( $item['video']['bit_rate'] as $rate ) {
                        $bitRateList[] = [
                            'name'          => $rate['gear_name'] ?? '',
                            'bit_rate'      => $rate['bit_rate'] ?? 0,
                            'width'         => $rate['play_addr']['width'] ?? 0,
                            'height'        => $rate['play_addr']['height'] ?? 0,
                            'data_size'     => FormatUnit::fileSize($rate['play_addr']['data_size'] ?? 0),
                            'play_url'      => !empty($rate['play_addr']['url_list']) ? end($rate['play_addr']['url_list']) : '',
                            'is_h265'       => $rate['is_h265'] ?? false,
                            'FPS'           => $rate['FPS'] ?? 0,
                            'quality_type'  => $rate['quality_type'] ?? '',
                        ];
                    }
                }
                $video['bit_rate'] = $bitRateList;
            } else {
                $isVideoOrImages = 'images';
                if ( !empty($item['images']) ) {
                    $images = [
                        'cover' => end($item['images'][0]['url_list']),
                        'urls'  => array_map(function($image) {
                            return end($image['url_list']);
                        }, $item['images']),
                    ];
                }
            }

           // 构建过滤后的视频对象
            $filterVideoList[] = [
                'aweme_id'      => $item['aweme_id'] ?? '',
                'title'         => $item['item_title'] ?? '',
                'desc'          => $item['desc'] ?? '',
                'create_time'   => !empty($item['create_time']) ? date('Y-m-d H:i:s', $item['create_time']) : '', // 修复
                'share_url'     => $item['share_info']['share_url'] ?? '',
                'share_desc'    => $item['share_info']['share_link_desc'] ?? '',
                'caption'       => $item['caption'] ?? '',
                'is_top'        => !empty($item['is_top']),
                'is_ad'         => $item['is_ads'] ?? false,
                'region'        => $item['region'] ?? '',
                'group_id'      => $item['group_id'] ?? '',
                'comment_gid'   => $item['comment_gid'] ?? '',
                'authentication_token' => $item['authentication_token'] ?? '',
                'statistics' => [
                    'digg'      => $item['statistics']['digg_count'] ?? 0,
                    'collect'   => $item['statistics']['collect_count'] ?? 0,
                    'share'     => $item['statistics']['share_count'] ?? 0,
                    'admire'    => $item['statistics']['admire_count'] ?? 0,
                    'comment'   => $item['statistics']['comment_count'] ?? 0,
                ],
                'author' => [
                    'uid'       => $item['author']['uid'] ?? '',
                    'nickname'  => $item['author']['nickname'] ?? '',
                    'sec_uid'   => $item['author']['sec_uid'] ?? '',
                    'avatar'    => !empty($item['author']['avatar_thumb']['url_list']) ? end($item['author']['avatar_thumb']['url_list']) : '', // 修复
                ],
                'music' => [
                    'id'            => $item['music']['id'] ?? '',
                    'title'         => $item['music']['title'] ?? '',
                    'author'        => $item['music']['author'] ?? '',
                    'cover'         => !empty($item['music']['cover_thumb']['url_list']) ? end($item['music']['cover_thumb']['url_list']) : '', // 修复
                    'duration'      => FormatUnit::duration($item['music']['duration'] ?? 0, false),
                    'is_original'   => $item['music']['is_original'] ?? false,
                    'play_url'      => !empty($item['music']['play_url']['url_list']) ? end($item['music']['play_url']['url_list']) : '', // 修复
                ],
                'is_video_or_images'    => $isVideoOrImages,
                'video'                 => $video,
                'images'                => $images,
            ];
        }

        return [
            'aweme_list'    => $filterVideoList,
            'time_list'     => $timeList,
            'has_more'      => $hasMore,
            'min_cursor'    => $minCursor,
            'max_cursor'    => $maxCursor
        ];
    }

}