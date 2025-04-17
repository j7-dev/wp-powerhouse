import { memo } from 'react'
import { Checkbox, Form } from 'antd'
import { Heading, Switch } from 'antd-toolkit'
import { useUserOptions } from '@/hooks'
import useFormInstance from 'antd/es/form/hooks/useFormInstance'

const { Item } = Form

const index = () => {
	const { roles } = useUserOptions()
	const form = useFormInstance()
	const watchEnableCaptchaLogin =
		Form.useWatch(['powerhouse_settings', 'enable_captcha_login'], form) ===
		'yes'

	return (
		<div className="flex flex-col md:flex-row gap-8">
			<div className="w-full max-w-[400px]">
				<Heading className="mt-16">登入安全</Heading>

				<Switch
					formItemProps={{
						name: ['powerhouse_settings', 'enable_captcha_login'],
						label: '啟用登入驗證碼 (推薦啟用)',
						tooltip: '啟用後，可以提高帳號安全性',
						initialValue: 'yes',
					}}
				/>

				{watchEnableCaptchaLogin && (
					<Item
						name={['powerhouse_settings', 'captcha_role_list']}
						label="那些角色登入需要驗證碼?"
					>
						<Checkbox.Group options={roles} />
					</Item>
				)}

				<Switch
					formItemProps={{
						name: ['powerhouse_settings', 'enable_captcha_register'],
						label: '啟用註冊驗證碼 (推薦啟用)',
						tooltip: '啟用後，可以防止機器人註冊',
						initialValue: 'yes',
					}}
				/>
			</div>
			<div className="flex-1 h-auto md:h-[calc(100%-5.375rem)] md:overflow-y-auto"></div>
		</div>
	)
}

export default memo(index)
