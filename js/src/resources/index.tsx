import { SettingOutlined } from '@ant-design/icons'
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
]
