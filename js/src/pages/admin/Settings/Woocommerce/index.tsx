import { memo } from 'react'
import { Heading, Switch, useEnv } from 'antd-toolkit'

const index = () => {
	const { SITE_URL } = useEnv()

	return (
		<div className="flex flex-col md:flex-row gap-8">
			<div className="w-full max-w-[400px]">
				<Heading className="mt-8">結帳優化</Heading>
				<Switch
					formItemProps={{
						name: ['powerhouse_settings', 'delay_email'],
						label: '使用非同步方式寄送 Email，加快結帳速度',
						help: (
							<>
								可以前往{' '}
								<a
									href={`${SITE_URL}/wp-admin/admin.php?page=wc-status&tab=action-scheduler&s=powerhouse_delay_email&action=-1&paged=1&action2=-1`}
								>
									Scheduled Actions
								</a>{' '}
								查看信件寄送的狀況
							</>
						),
						initialValue: 'yes',
					}}
				/>

				<Heading className="mt-16">My Account 帳號欄位優化</Heading>

				<Switch
					formItemProps={{
						name: ['powerhouse_settings', 'last_name_optional'],
						label: '使姓氏欄位為非必填',
						tooltip: '啟用後，不再強制要求用戶必須填寫姓氏',
						initialValue: 'yes',
					}}
				/>
			</div>
			<div className="flex-1 h-auto md:h-[calc(100%-5.375rem)] md:overflow-y-auto"></div>
		</div>
	)
}

export default memo(index)
