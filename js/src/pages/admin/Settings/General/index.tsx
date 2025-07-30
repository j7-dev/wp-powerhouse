import { memo } from 'react'
import { Button, Checkbox, Form, Input } from 'antd'
import { MinusCircleOutlined } from '@ant-design/icons'
import { PlusOutlined } from '@ant-design/icons'
import { Heading, Switch } from 'antd-toolkit'
import { stringToBool } from 'antd-toolkit/wp'

import { useUserOptions } from '@/hooks'
import useFormInstance from 'antd/es/form/hooks/useFormInstance'

const { Item, List } = Form

const index = () => {
	const { roles } = useUserOptions()
	const form = useFormInstance()
	const watchEnableCaptchaLogin = stringToBool(
		Form.useWatch(['powerhouse_settings', 'enable_captcha_login'], form),
	)

	const watchEnableEmailDomainCheckRegister = stringToBool(
		Form.useWatch(
			['powerhouse_settings', 'enable_email_domain_check_register'],
			form,
		),
	)

	return (
		<div className="flex flex-col md:flex-row gap-8">
			<div className="w-full max-w-[400px]">
				<Heading>Email 設定</Heading>

				<Switch
					formItemProps={{
						name: ['powerhouse_settings', 'enable_manual_send_email'],
						label: '允許用戶手動發信',
						tooltip:
							'啟用後，可以允許用戶手動發信，目前僅在 Power Course 有手動發信功能',
					}}
				/>

				<Heading className="mt-16">登入安全</Heading>

				<Switch
					formItemProps={{
						name: ['powerhouse_settings', 'enable_captcha_login'],
						label: '啟用登入驗證碼',
						tooltip: '啟用後，可以提高帳號安全性，防止機器人破解暴力破解帳密',
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
						label: '啟用註冊驗證碼',
						tooltip:
							'啟用後，可以防止機器人註冊，防止機器人註冊，但可能與其他社群登入外掛衝突',
					}}
				/>

				<Switch
					formItemProps={{
						name: ['powerhouse_settings', 'enable_email_domain_check_register'],
						label: '註冊前驗證用戶的 Email (👍推薦啟用)',
						tooltip:
							'啟用後，可以防止機器人使用假 Email 註冊，例如隨便填寫一個 test@test123.com 的假用戶註冊將被擋下',
					}}
				/>

				{watchEnableEmailDomainCheckRegister && (
					<>
						<p className="mb-1">驗證白名單</p>
						<p className="text-gray-400 text-xs">
							白名單中的網域被視為可以註冊，不會進行驗證，已知的合法網域不需要檢查，可以加快註冊流程
						</p>

						<List
							name={['powerhouse_settings', 'email_domain_check_white_list']}
						>
							{(fields, { add, remove }) => (
								<>
									{fields.map(({ key, name, ...restField }) => {
										return (
											<div className="flex items-center gap-2 mb-1">
												<Item
													noStyle
													{...restField}
													name={[name]}
													rules={[{ required: true, message: '請輸入網域' }]}
												>
													<Input placeholder="網域" size="small" />
												</Item>
												<MinusCircleOutlined onClick={() => remove(name)} />
											</div>
										)
									})}
									<Item noStyle>
										<Button
											size="small"
											type="dashed"
											onClick={() => add()}
											block
											icon={<PlusOutlined />}
										>
											新增
										</Button>
									</Item>
								</>
							)}
						</List>
					</>
				)}
			</div>
			<div className="flex-1 h-auto md:h-[calc(100%-5.375rem)] md:overflow-y-auto"></div>
		</div>
	)
}

export default memo(index)
