import { useEffect } from 'react'
import { Form, ColorPicker, InputNumber } from 'antd'
import { COLOR_MAPPER, THEME_MAPPER, NUMBER_MAPPER } from './constants'
import { oklchToHex, hexToOklch } from './utils'
import { Heading, Switch } from 'antd-toolkit'

const { Item } = Form

const Custom = () => {
	const form = Form.useFormInstance()

	const watchTheme = Form.useWatch(['powerhouse_settings', 'theme'], form)

	useEffect(() => {
		if (!watchTheme) {
			return
		}
		if ('custom' === watchTheme) {
			return
		}
		const theme = THEME_MAPPER.find(
			({ theme: singleTheme }) => singleTheme === watchTheme,
		)
		if (!theme) return

		// const formattedTheme = formatTheme(theme)
		form.setFieldValue(['powerhouse_settings', 'theme_css'], theme)
	}, [watchTheme])

	return (
		<>
			<Heading className="mt-8">啟用 Power 外掛主題色</Heading>
			<Switch
				formItemProps={{
					name: ['powerhouse_settings', 'enable_theme'],
				}}
			/>
			<Heading className="mt-8">自訂主題</Heading>
			<div className="flex flex-wrap gap-2">
				{COLOR_MAPPER.map(({ label, key, defaultValue }) => (
					<Item
						noStyle
						key={key}
						name={['powerhouse_settings', 'theme_css', key]}
						getValueProps={(value) => {
							return {
								value: value ? oklchToHex(value) : '',
							}
						}}
						normalize={(value) => {
							const hex = value.toHex()
							return hexToOklch(hex)
						}}
					>
						<ColorPicker
							size="small"
							showText={() => label}
							onChange={(value) => {
								form.setFieldValue(['powerhouse_settings', 'theme'], 'custom')
							}}
						/>
					</Item>
				))}
			</div>

			<div className="mt-4">
				{NUMBER_MAPPER.map(({ label, key, defaultValue, unit }) => (
					<Item
						key={key}
						name={['powerhouse_settings', 'theme_css', key]}
						className="mb-0"
						getValueProps={(value) => ({ value: parseFloat(value) || 0 })}
						normalize={(value) => `${value}${unit}`}
					>
						<InputNumber
							className="w-full"
							size="small"
							step={'px' === unit ? 1 : 0.1}
							addonBefore={<div className="w-28 text-left">{label}</div>}
							addonAfter={<div className="w-6 text-center">{unit}</div>}
							onChange={(value) => {
								form.setFieldValue(['powerhouse_settings', 'theme'], 'custom')
							}}
						/>
					</Item>
				))}
			</div>

			<Item
				name={['powerhouse_settings', 'theme_css', 'color-scheme']}
				hidden
			/>
		</>
	)
}

export default Custom
