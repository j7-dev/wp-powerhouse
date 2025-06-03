import { memo } from 'react'
import { Heading } from 'antd-toolkit'
import { Button, Form, Tag } from 'antd'
import { PlusOutlined, TagsOutlined } from '@ant-design/icons'
import useOptions from '@/pages/admin/Settings/hooks/useOptions'
import { getSettingsName } from '@/pages/admin/Settings/utils'
import usePlugins from '@/pages/admin/Settings/Lab/usePlugins'
import { TApiBoosterRule } from '@/pages/admin/Settings/Lab/types'
import RuleSet from './RuleSet'
import { nanoid } from 'nanoid'

const { Item } = Form

const index = () => {
	const form = Form.useFormInstance()
	const { data } = usePlugins()
	const plugins = data?.data || []

	const apiBoosterRules = (form.getFieldValue(
		getSettingsName(['api_booster_rules']),
	) || []) as TApiBoosterRule[]

	const watchApiBoosterRules = (Form.useWatch(
		['powerhouse_settings', 'api_booster_rules'],
		form,
	) || []) as TApiBoosterRule[]

	const renderApiBoosterRules = watchApiBoosterRules.filter(
		(rule) => !!rule.key,
	)

	const handleAddRule = (recipe?: TApiBoosterRule) => () => {
		const activatedPlugins = plugins.filter((plugin) => plugin.is_active)

		const newRule = recipe || {
			enable: 'no',
			name: '',
			rules: '',
			plugins: activatedPlugins.map((plugin) => plugin.key),
		}

		form.setFieldValue(getSettingsName(['api_booster_rules']), [
			...apiBoosterRules,
			{
				...newRule,
				key: nanoid(6),
				plugins: activatedPlugins
					.filter(({ key }) => newRule?.plugins?.includes(key))
					?.map(({ key }) => key),
			},
		])
	}

	const { data: optionData } = useOptions({ form })
	const powerhouse_settings = optionData?.data?.data?.powerhouse_settings
	const recipes = (powerhouse_settings?.api_booster_rule_recipes ||
		[]) as TApiBoosterRule[]

	return (
		<>
			<Item name={getSettingsName(['api_booster_rules'])} hidden />
			<div className="flex flex-col xl:flex-row gap-8">
				<div className="w-full max-w-[400px]">
					<Heading className="mt-8">性能提升</Heading>
					<p>啟用後台 API 加速器，約提速 60% ~ 100%</p>
					<p className="text-gray-400">
						如果啟用後發生錯誤，請與管理員聯繫，並先暫時停用此功能
					</p>

					<p>
						<TagsOutlined /> 以下是一些預先建立好的範本
					</p>
					{recipes.map((recipe) => (
						<Tag
							className="cursor-pointer"
							key={recipe?.key}
							onClick={handleAddRule(recipe)}
						>
							{recipe?.name}
						</Tag>
					))}
				</div>
				<div className="flex-1 mt-8">
					{renderApiBoosterRules?.map((rule, index) => (
						<RuleSet key={rule.key} index={index} />
					))}

					<Button
						className="w-full mt-4"
						color="default"
						variant="dashed"
						icon={<PlusOutlined />}
						onClick={handleAddRule()}
					>
						新增規則
					</Button>
					{/* <Heading className="mt-8">說明</Heading>
				<iframe
					className="max-w-[400px] w-full aspect-video"
					src="https://www.youtube.com/embed/OnDK8sV0rQg?si=CHf80HE8hd2k20Yh"
					title="YouTube video player"
					frameBorder="0"
					allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
					referrerPolicy="strict-origin-when-cross-origin"
					allowFullScreen
				></iframe> */}
				</div>
			</div>
		</>
	)
}

export default memo(index)
