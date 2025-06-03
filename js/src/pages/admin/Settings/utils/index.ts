import { FormItemProps } from 'antd'

export const getSettingsName = (name: Array<string|number>) => {
return [
	'powerhouse_settings',
	...name
] as FormItemProps['name'];
}