<?php

declare (strict_types = 1);

namespace J7\Powerhouse\Utils;

/**
 * ExportCSV 匯出 CSV
 * 使用方式
 * 1. 繼承此類
 * 2. 定義 $rows 資料源
 * 3. 定義 $filename, $columns, $render_columns
 * 4. 呼叫 export()
 * */
abstract class ExportCSV {

	/** @var string 檔案名稱 */
	protected string $filename;

	/** @var array<object> 資料 */
	protected array $rows;

	/** @var array<string> 欄位名稱，預設會從 $row 身上拿屬性 */
	protected array $columns = [];

	/** @var array<callable> 欄位值的 callback */
	protected array $render_columns = [];

	/**
	 * 匯出 CSV
	 *
	 *  @return void
	 *  @throws \Exception 如果無法開啟輸出檔案或無法寫入 CSV 標頭
	 * */
	public function export(): void {
		try {
			$filename = $this->filename . '_' . \wp_date('Y-m-d') . '.csv';
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=' . $filename);

			$output = fopen('php://output', 'w');
			if ($output === false) {
				throw new \Exception('無法開啟輸出檔案');
			}

			fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

			// CSV 標頭
			$fputcsv = fputcsv($output, $this->columns);
			if ($fputcsv === false) {
				throw new \Exception('無法寫入 CSV 標頭');
			}

			foreach ($this->rows as $row) {
				fputcsv(
				$output,
				self::get_field_value($row)
				);
			}

			fclose($output);
			exit;
		} catch (\Throwable $th) {
			throw new \Exception($th->getMessage());
		}
	}

	/**
	 * 取得欄位值
	 * 如果 render_columns 有填入 callback 則會使用 callback 的結果
	 * 否則使用 row 的值
	 *
	 * @param object $row 文章
	 * @return array 欄位值
	 */
	protected function get_field_value( $row ) {
		$values = [];
		foreach ($this->columns as $index => $column) {
			if (is_callable(@$this->render_columns[ $index ])) {
				$values[] = $this->render_columns[ $index ]($row);
				continue;
			}
			if (property_exists($row, $column)) {
				$values[] = $row->$column;
				continue;
			}

			$values[] = '';

		}
		return $values;
	}
}
