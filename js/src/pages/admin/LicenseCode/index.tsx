import Card, { TLC } from './Card'
import { HttpError, useList } from '@refinedev/core'
import { Spin, Empty } from 'antd'

export const LicenseCode = () => {
	const { data, isLoading, isSuccess } = useList<TLC, HttpError>({
		resource: 'lc',
	})
	const lcs = data?.data

	if (lcs?.length === 0 && isSuccess) {
		return <Empty className="py-20" description="目前沒有授權碼" />
	}

	return (
		<Spin className="min-h-[25rem]" spinning={isLoading}>
			<div className="flex flex-wrap gap-6">
				{(lcs as TLC[])?.map((lc) => <Card key={lc.product_slug} lc={lc} />)}
			</div>
		</Spin>
	)
}
