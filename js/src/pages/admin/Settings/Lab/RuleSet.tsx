import React, { useEffect } from 'react'
import {
	Transfer,
	TransferProps,
	Form,
	Input,
	FormItemProps,
	Button,
	Popconfirm,
} from 'antd'
import { getSettingsName } from '@/pages/admin/Settings/utils'
import { TApiBoosterRule } from '@/pages/admin/Settings/Lab/types'
import usePlugins from '@/pages/admin/Settings/Lab/usePlugins'
import { Switch } from 'antd-toolkit'

const { Item } = Form

const getRulesName = (name: Array<string | number>): FormItemProps['name'] => {
	return getSettingsName(['api_booster_rules', ...name])
}

const RuleSet: React.FC<{ index: number }> = ({ index }) => {
	const form = Form.useFormInstance()
	const { data } = usePlugins()
	const plugins = data?.data || []

	const watchPlugins =
		Form.useWatch(
			['powerhouse_settings', 'api_booster_rules', index, 'plugins'],
			form,
		) || []

	const onChange: TransferProps['onChange'] = (
		nextTargetKeys,
		direction,
		moveKeys,
	) => {
		form.setFieldValue(getRulesName([index, 'plugins']), nextTargetKeys)
	}

	const handleDelete = () => {
		const key = form.getFieldValue(getRulesName([index, 'key']))
		const apiBoosterRules = (form.getFieldValue(
			getSettingsName(['api_booster_rules']),
		) || []) as TApiBoosterRule[]

		const newApiBoosterRules = apiBoosterRules.filter(
			(rule) => rule.key !== key,
		)

		form.setFieldValue(
			['powerhouse_settings', 'api_booster_rules'],
			newApiBoosterRules,
		)
	}

	return (
		<div
			className="w-full mb-4"
			style={{
				contain: 'layout paint style',
			}}
		>
			<Item name={getRulesName([index, 'key'])} hidden />
			<div className="grid grid-cols-[48px_1fr_580px] gap-4">
				<div className="flex flex-col gap-2 justify-between">
					<Switch
						formItemProps={{
							name: getRulesName([index, 'enabled']),
							label: '啟用',
						}}
					/>
					<Popconfirm
						title="確定要刪除嗎？"
						description="刪除後需要儲存才會真的刪掉"
						onConfirm={handleDelete}
						cancelText="取消"
						okText="確定"
					>
						<Button danger type="link">
							刪除
						</Button>
					</Popconfirm>
				</div>
				<div>
					<Item
						name={getRulesName([index, 'name'])}
						label="規則名稱"
						tooltip="僅用於識別，沒有其他功能"
					>
						<Input placeholder="例如：使用 Power API 時，不載入其他外掛" />
					</Item>
					<Item
						name={getRulesName([index, 'rules'])}
						label="填寫 url/api 規則"
						className="mb-0"
						tooltip="不需要填網址，每行一個規則，例如：/wp-json/powerhouse/*"
					>
						<Input.TextArea rows={6} />
					</Item>
				</div>
				<div className="">
					<Item name={getRulesName([index, 'plugins'])} hidden />
					<label className="text-sm mb-2 inline-block">
						選擇針對規則要載入的外掛
					</label>
					<Transfer
						listStyle={{
							width: 270,
							height: 229,
						}}
						className="w-full"
						dataSource={plugins}
						titles={['不載入', '載入']}
						targetKeys={watchPlugins}
						onChange={onChange}
						render={(item) => item.title}
					/>
				</div>
			</div>
		</div>
	)
}

export default RuleSet
