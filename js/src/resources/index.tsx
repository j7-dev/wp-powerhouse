import { SettingOutlined, BarcodeOutlined } from '@ant-design/icons'
import { ResourceProps } from '@refinedev/core'

export const resources: ResourceProps[] = [
	{
		name: 'settings',
		list: '/settings',
		meta: {
			label: '設定',
			icon: <SettingOutlined />,
		},
	},
	{
		name: 'license-code',
		list: '/license-code',
		meta: {
			label: '授權碼',
			icon: <BarcodeOutlined />,
		},
	},
]
