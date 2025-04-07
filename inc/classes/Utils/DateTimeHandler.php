<?php

declare ( strict_types=1 );

namespace J7\Powerhouse\Utils;

if ( class_exists( 'J7\Powerhouse\Utils\DateTimeHandler' ) ) {
	return;
}

/**
 * Class DateTimeHandler
 * 時間相關
 */
abstract class DateTimeHandler {

	/**
	 * 取得比較日期時間
	 * 通常做圖表時使用
	 *
	 * @param \DateTime $date_time 日期時間
	 * @param string    $compare_type 比較類型 'day', 'month', 'year'，前N天，前N個月，前N年
	 * @param int       $compare_value 比較值，預設為1，前一天，前一個月，前一年
	 * @return \DateTime
	 * @throws \InvalidArgumentException 如果比較類型不支持
	 */
	public static function get_compared_date_time( \DateTime $date_time, string $compare_type = 'day', int $compare_value = 1 ): \DateTime {
		// 創建新的 DateTime 物件（遵循 immutable 風格）
		$compared_date = clone $date_time;

		switch ($compare_type) {
			case 'day':
				// 前一天
				$compared_date->modify("-{$compare_value} day");
				break;

			case 'month':
				// 取得當前日期的日、月、年
				$current_day = (int) $date_time->format('d');

				// 使用 DateTime 的 modify 來計算前 N 個月的年和月
				$temp_date = clone $date_time;
				$temp_date->modify("-{$compare_value} month");
				$prev_month = (int) $temp_date->format('m');
				$prev_year  = (int) $temp_date->format('Y');

				// 計算前 N 個月的最後一天
				$days_in_prev_month = cal_days_in_month(CAL_GREGORIAN, $prev_month, $prev_year);

				// 如果當前日期大於前 N 個月的天數，則使用前 N 個月的最後一天
				$prev_day = min($current_day, $days_in_prev_month);

				// 設定比較日期
				$compared_date->setDate($prev_year, $prev_month, $prev_day);
				break;

			case 'year':
				// 前一年同月同日
				$compared_date->modify("-{$compare_value} year");
				break;

			default:
				throw new \InvalidArgumentException("不支持的比較類型: {$compare_type}，支持的類型為: 'day', 'month', 'year'");
		}

		return $compared_date;
	}


	/**
	 * 解析日期時間
	 *
	 * @param string $date_string 日期時間字串 ex 2025-01-01T12:00:00
	 * @return \DateTime
	 */
	public static function parse_date_time( string $date_string ): \DateTime {
		return new \DateTime($date_string, new \DateTimeZone(\wp_timezone_string()));
	}
}
