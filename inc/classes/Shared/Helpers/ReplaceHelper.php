<?php

declare(strict_types=1);

namespace J7\Powerhouse\Shared\Helpers;

use J7\Powerhouse\Shared\Enums\EObjectType;

/**
 * 字串替換
 * 例如
 * "{{user.display_name}}" 可以用 $user->display_name 替換
 * "{{arr[display_name]}}" 可以用 $arr['display_name'] 替換
 * @example
 * $replaced_template = (new ReplaceHelper($template))->replace($user)->replate($product)->replate($order)->get_replaced_template();
 */
final class ReplaceHelper {
    
    private array|object $obj;

	/** @var string[] $all_placeholders_with_tag 所有匹配到的 placeholder，例如 都是 {{user 開頭  }} 結尾的"完整"字串  */
	private array $all_placeholders_with_tag = [];
	/** @var string[] $all_placeholders 所有匹配到的 placeholder，例如 都是 {{user 開頭  }} 結尾的字串  */
	private array $all_placeholders = [];

	private EObjectType $object_type;
    
    private string $filtered_tempalte;


	/**
	 * Constructor
	 *
	 * @param string       $raw_template 要處理的字串模板
	 * @param string       $start_tag    開始標籤，預設為 '{{'
	 * @param string       $end_tag      結束標籤，預設為 '}}'
	 */
	public function __construct(
		private string          $raw_template,
		private readonly string $start_tag = '{{',
		private readonly string $end_tag = '}}',
	) {
        $this->filtered_tempalte = $this->raw_template;
        $this->init();
	}
    
    /**
     * 傳入物件取代
     * @param array|object $obj 要替換的物件或陣列，例如 \WP_User $user
     */
    public function replace( array|object $obj ):self {
        $this->obj = $obj;
        $this->init();
        $this->filtered_tempalte = \strtr( $this->filtered_tempalte, $this->get_strtr_replace_array());
        return $this;
    }

	public function get_replaced_template(): string {
        return $this->filtered_tempalte;
	}
    
   
    
    /**
     * 初始化
     * 每次做完一個物件的取代可以重新初始化
     */
    private function init(  ):void {
        $this->object_type = EObjectType::get_type($this->obj);
        
        // 用 preg_quote 把特殊字元轉義
        $pattern = '/' . \preg_quote($this->start_tag, '/') . $this->object_type->value . '\s*(.*?)\s*' . \preg_quote($this->end_tag, '/') . '/';
        \preg_match_all( $pattern, $this->raw_template, $matches);
        
        $this->all_placeholders_with_tag = $matches[0];
        $this->all_placeholders          = $matches[1];
    }

	/**
	 * 解析字串
	 * 解析 arr[user].orders[0].customer.address 字串
	 * key 代表是 array 的 key
	 * property 代表是物件的屬性
	 *
	 * @return array<int, array{value: string, type: 'property'|'key'}> 解析後的陣列
	 */
	private function parse_placeholder( string $placeholder ): array {
		$result = [];

		// 用正則表達式匹配所有的屬性和陣列索引
		\preg_match_all('/(\w+)(?:\[(\w+)\])?/', $placeholder, $matches, PREG_SET_ORDER);

		foreach ($matches as $index => $match) {
			$property = $match[1]; // 屬性名稱
			$arrayKey = isset($match[2]) ? $match[2] : null; // 陣列索引(如果有)

			// 後續的屬性是 property
			$result[] = [
				'value' => $property,
				'type'  => 'property',
			];

			// 如果有陣列索引,加入一個 key 類型的元素
			if ($arrayKey !== null) {
				$result[] = [
					'value' => $arrayKey,
					'type'  => 'key',
				];
			}
		}

		return $result;
	}

	/** @return string 取得該 placeholder 的字串 */
	private function get_placeholder_value( string $placeholder ): string {
		$parsed_array = $this->parse_placeholder( $placeholder);
		$value        = $this->obj;
		foreach ($parsed_array as $item) {
			if ($item['type'] === 'property') {
				$value = $value->{$item['value']};
			} else {
				$value = $value[ $item['value'] ];
			}
		}
		return (string) $value;
	}


	/**
	 * 取得給 strtr 用的 array
	 * 取得後可以直接丟進去 strtr 做字串取代
	 *
	 * @return array<string, string>
	 */
	private function get_strtr_replace_array(): array {
		$strtr_array = [];
		foreach ($this->all_placeholders_with_tag as $index => $placeholder_with_tag) {
			$strtr_array[ $placeholder_with_tag ] = $this->get_placeholder_value($this->all_placeholders[ $index ]);
		}
		return $strtr_array;
	}
}
