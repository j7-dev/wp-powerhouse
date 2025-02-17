import { memo } from 'react'
import { Form, Button, Tabs, TabsProps } from 'antd'
import useOptions from './hooks/useOptions'
import useSave from './hooks/useSave'
import General from './General'
import Lab from './Lab'
import Theme from './Theme'

const items: TabsProps['items'] = [
	{
		key: 'general',
		label: '一般設定',
		children: <General />,
	},
	{
		key: 'theme',
		label: '主題顏色',
		children: <Theme />,
	},
	{
		key: 'lab',
		label: (
			<div className="flex items-end">
				實驗性功能
				<div className="bg-orange-400 text-white rounded-xl px-2 py-0 inline-block ml-2 text-[0.625rem]">
					beta
				</div>
			</div>
		),
		children: <Lab />,
	},
]

const SettingsPage = () => {
	const [form] = Form.useForm()
	const { handleSave, mutation } = useSave({ form })
	const { isLoading: isSaveLoading } = mutation
	const { isLoading: isGetLoading } = useOptions({ form })

	return (
		<Form layout="vertical" form={form} onFinish={handleSave}>
			<Tabs
				tabBarExtraContent={{
					left: (
						<Button
							className="mr-8"
							type="primary"
							htmlType="submit"
							loading={isSaveLoading}
							disabled={isGetLoading}
						>
							儲存
						</Button>
					),
				}}
				defaultActiveKey="general"
				items={items}
			/>
		</Form>
	)
}

export const Settings = memo(SettingsPage)
