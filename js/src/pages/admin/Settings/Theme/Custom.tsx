import { useEffect, useState } from 'react'
import { Heading } from 'antd-toolkit'
import { Form, ColorPicker } from 'antd'
import { COLOR_MAPPER, THEME_MAPPER } from './constants'
import { oklchToHex, hexToOklch } from './utils'

const { Item } = Form

const Custom = () => {
	const [isCustom, setIsCustom] = useState(false)
	const form = Form.useFormInstance()
	const watchTheme = Form.useWatch(['powerhouse_settings', 'theme'], form)

	useEffect(() => {
		if (!watchTheme) {
			return
		}
		if ('custom' !== watchTheme) {
			setIsCustom(false)
			const theme = THEME_MAPPER.find(({ theme }) => theme === watchTheme)
			if (!theme) return
			// const formattedTheme = formatTheme(theme)
			form.setFieldValue(['powerhouse_settings', 'theme_css'], theme)
		} else {
			setIsCustom(true)
		}
	}, [watchTheme])

	useEffect(() => {
		if (isCustom) {
			form.setFieldValue(['powerhouse_settings', 'theme'], 'custom')
		}
	}, [isCustom])

	return (
		<>
			<Heading className="mt-8">自訂主題</Heading>
			<div className="flex flex-wrap gap-2">
				{COLOR_MAPPER.map(({ label, key, defaultValue }) => (
					<Item
						noStyle
						key={key}
						name={['powerhouse_settings', 'theme_css', key]}
						getValueProps={(value) => {
							if (key === '--p') {
								console.log({
									key,
									value,
									oklchToHex: oklchToHex(value),
								})
							}
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
							defaultValue={defaultValue}
							showText={() => label}
							onChange={(value) => {
								setIsCustom(true)
							}}
						/>
					</Item>
				))}
			</div>
		</>
	)
}

export default Custom
